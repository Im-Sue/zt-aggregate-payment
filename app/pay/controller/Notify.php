<?php

namespace app\pay\controller;

use Wxpay\WxPayConfig;
use Wxpay\lib\WxPayNotifyResults;
use think\facade\Db;

class Notify
{
    /**
     * 支付宝订单状态查询
     */
    public function alipay()
    {
        // file_put_contents('./payResultAlipay.txt', file_get_contents("php://input") . "\n\n", 8);
        $out_trade_no = input('out_trade_no', '');
        $trade_status = input('trade_status', '');
        $sign_type = input('sign_type', '');
        $gmt_payment = input('gmt_payment', '');
        $receipt_amount = input('receipt_amount', '');
        $fund_bill_list = input('fund_bill_list', '');
        $fund_bill_list = json_decode(htmlspecialchars_decode($fund_bill_list), true);
        // $total_amount = input('total_amount', '');
        // $trade_no = input('trade_no', '');
        // $sign = input('sign', '');
        // $buyer_user_id = input('buyer_user_id', '');
        // $buyer_logon_id = input('buyer_logon_id', '');

        //验签
        $order = Db::name('order')
            ->where('out_trade_no', $out_trade_no)
            ->find();
        if (empty($order)) {
            die('fail');
        }
        $payConfig = getShopPayConfig($order['shop_id'], 'alipay');
        $aop = get_alipay_aop($payConfig);
        $checkSign = $aop->rsaCheckV1($_POST, null, $sign_type);

        if ($checkSign && $trade_status == 'TRADE_SUCCESS') {
            //记录优惠信息
            $coupon_count = 0;
            $coupon_fee_nocash = 0;
            $coupon_fee_cash = 0;
            if ($fund_bill_list) {
                foreach ($fund_bill_list as $v) {
                    if ($v['fundChannel'] == 'MDISCOUNT') {
                        //商户优惠券
                        $coupon_fee_nocash += $v['amount'];
                        $coupon_count++;
                    } else if ($v['fundChannel'] == 'COUPON') {
                        //支付宝红包
                        $coupon_fee_cash += $v['amount'];
                        $coupon_count++;
                    } else if ($v['fundChannel'] == 'DISCOUNT') {
                        //折扣券
                        $coupon_fee_cash += $v['amount'];
                        $coupon_count++;
                    } else if ($v['fundChannel'] == 'MCOUPON') {
                        //商户红包
                        $coupon_fee_nocash += $v['amount'];
                        $coupon_count++;
                    }
                }
            }
            // 计算手续费
            $fee = self::calcOrderFee($order['rate'], $receipt_amount);

            Db::name('order')
                ->where('out_trade_no', $out_trade_no)
                ->update([
                    'status' => 1,
                    'pay_time' => strtotime($gmt_payment),
                    'coupon_count' => $coupon_count,
                    'coupon_fee_nocash' => $coupon_fee_nocash * 100,
                    'coupon_fee_cash' => $coupon_fee_cash * 100,
                    'settlement_total_fee' => $receipt_amount * 100,
                    'fee' => $fee * 100
                ]);

            //推送到收款音箱播放
            $order['money'] = $receipt_amount;
            self::speaker($order);

            echo 'success';
        } else {
            echo 'fail';
        }
    }

    public function wxpay()
    {
        $xml = file_get_contents("php://input");
        // file_put_contents('./payResultWxpay.txt', "$xml\n\n", 8);
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        $result_code = $data['result_code'];
        $out_trade_no = $data['out_trade_no'];      // 商户订单号
        $transaction_id = $data['transaction_id'];  // 微信流水单号
        $time_end = $data['time_end'];              // 支付时间
        // $total_fee = $data['total_fee'];         // 交易金额

        // 验签
        $order = Db::name('order')
            ->where('out_trade_no', $out_trade_no)
            ->find();
        if (!$order) {
            die('fail');
        }
        $payConfig = getShopPayConfig($order['shop_id'], 'wxpay');
        $config = new WxPayConfig();
        $config->SetKey($payConfig['key']);
        $Notify = new WxPayNotifyResults();
        $checkSign = $Notify->Init($config, $xml);

        if (!$checkSign) {
            die('fail');
        }

        if ($result_code == 'SUCCESS') {
            // 优惠信息
            $settlement_total_fee = $data['total_fee'];
            $coupon_count = 0;
            $coupon_fee_nocash = 0;
            $coupon_fee_cash = 0;
            if (!empty($data['coupon_count'])) {
                if ($data['coupon_type_0'] == 'NO_CASH') {
                    $coupon_fee_nocash = $data['coupon_fee_0'];
                }
                if ($data['coupon_type_0'] == 'CASH') {
                    $coupon_fee_cash = $data['coupon_fee_0'];
                }
                if ($data['coupon_type_1'] == 'NO_CASH') {
                    $coupon_fee_nocash = $data['coupon_fee_1'];
                }
                if ($data['coupon_type_1'] == 'CASH') {
                    $coupon_fee_cash = $data['coupon_fee_1'];
                }
                if (empty($coupon_fee_cash) && empty($coupon_fee_nocash)) {
                    $coupon_fee_cash = $data['coupon_fee_0'];
                }
                $coupon_count = $data['coupon_count'];
                if (isset($data['settlement_total_fee'])) {
                    $settlement_total_fee = $data['settlement_total_fee'];
                }

            }
            //营销详情
            if (!empty($data['promotion_detail'])) {
                $promotion_detail = json_decode($data['promotion_detail'], true);
                $coupon_count = count($promotion_detail['promotion_detail']);
                foreach ($promotion_detail['promotion_detail'] as $k => $v) {
                    if ($v['type'] == 'DISCOUNT') {
                        $coupon_fee_nocash += ($v['amount'] * 100);
                    } else {
                        $coupon_fee_cash += ($v['amount'] * 100);
                    }
                }
                $settlement_total_fee = $data['total_fee'] * 100 - $coupon_fee_nocash;
            }
            // 计算手续费
            $fee = self::calcOrderFee($order['rate'], $settlement_total_fee / 100);

            Db::name('order')
                ->where('out_trade_no', $out_trade_no)
                ->update([
                    'status' => 1,
                    'transaction_id' => $transaction_id,
                    'coupon_count' => $coupon_count,
                    'coupon_fee_nocash' => $coupon_fee_nocash,
                    'coupon_fee_cash' => $coupon_fee_cash,
                    'settlement_total_fee' => $settlement_total_fee,
                    'fee' => $fee * 100,
                    'pay_time' => strtotime($time_end)
                ]);

            //推送到收款音箱播放
            $order['money'] = $settlement_total_fee / 100;
            self::speaker($order);

            echo 'success';
        } else {
            echo 'fail';
        }
    }

    /**
     * 计算手续费
     */
    private static function calcOrderFee($rate = 0, $money = 0)
    {
        if (empty($rate) || empty($money)) {
            return 0;
        }
        return round($rate * $money / 100, 2);
    }

    /**
     * 语音播报
     */
    private static function speaker($order)
    {
        $qrcode = Db::name('qrcode')
            ->where('id', $order['qrcode_id'])
            ->field('speaker_status,speaker_brand,speaker_config')
            ->find();
        if (!$qrcode || empty($qrcode['speaker_status']) || empty($qrcode['speaker_brand'])) {
            return false;
        }
        if ($qrcode['speaker_brand'] == 'pinsheng') {
            $qrConfig = json_decode($qrcode['speaker_config'], true);
            if (empty($qrConfig)) {
                return false;
            }
            $setting = getSystemSetting('speaker');
            if (!empty($setting['pinsheng_username']) && !empty($setting['pinsheng_password']) && !empty($qrConfig['devid'])) {
                $speaker = new \Speaker\pinsheng\sdk($setting['pinsheng_username'], $setting['pinsheng_password']);
                $speaker->play($qrConfig['devid'], $order['pay_type'], $order['money'], $order['out_trade_no']);
            }
        }

    }
}
