<?php

namespace app\pay\controller;

use think\facade\Db;
use AopSDK\request\AlipayTradeCreateRequest;
use AopSDK\request\AlipaySystemOauthTokenRequest;
use Wxpay\WxPayConfig;
use Wxpay\JsApiPay;
use Wxpay\lib\WxPayUnifiedOrder;
use Wxpay\lib\WxPayApi;
use Jssdk\jssdk;

class Qrcode
{
    /**
     * 二维码页面-路由
     */
    public function index()
    {
        $id = input('id', 0, 'intval');
        // echo $id;exit;
        $qrcode = Db::name('qrcode')
            ->where('id', $id)
            ->find();
        if (!$qrcode) {
            return $this->error('参数错误');
        }
        $shop = Db::name('shop')
            ->where('id', $qrcode['shop_id'])
            ->find();
        if (!$shop) {
            return $this->error('参数错误');
        }
        $now = time();
        if ($shop['end_time'] && $shop['end_time'] < $now) {
            return $this->error('商户账号已过期');
        }
        if (!$shop['status']) {
            return $this->error('收款码已被停用');
        }

        // 支付宝扫码
        if (preg_match('/alipayclient\/([^\s]+)/i', $_SERVER['HTTP_USER_AGENT'], $regs)) {
            header("location:" . mobileUrl('/pay/qrcode/alipay', ['id' => $id]));
            exit();
        }
        // 微信扫码
        if (preg_match('/MicroMessenger\/([^\s]+)/i', $_SERVER['HTTP_USER_AGENT'], $regs)) {
            header("location:" . mobileUrl('/pay/qrcode/wxpay', ['id' => $id]));
            exit();
        }

        return $this->error('请使用微信或支付宝扫码付款');
    }

    /**
     * 支付宝h5
     */
    public function alipay()
    {
        $id = input('id', 0, 'intval');
        $auth_code = input('auth_code', '');

        $qrcode = Db::name('qrcode')
            ->where('id', $id)
            ->find();
        $shop = Db::name('shop')
            ->where('id', $qrcode['shop_id'])
            ->find();
        $payConfig = getShopPayConfig($shop, 'alipay');
        if (!$payConfig['status']) {
            return $this->error('未开启支付宝付款');
        }
        if(empty($_SESSION['alipay_user_id']) && empty($auth_code)) {
            $query = $this->toUrlParams([
                'app_id' => $payConfig['appid'],
                'scope' => 'auth_base',
                'redirect_uri' => urlencode(mobileUrl('/pay/qrcode/alipay', ['id' => $id]))
            ]);
            header("location:https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?" . $query);
            exit();
        }
        if(!empty($_SESSION['alipay_user_id'])) {
            $user_id = $_SESSION['alipay_user_id'];
        } else {
            // 获取支付宝user_id
            $aop = get_alipay_aop($payConfig);
            $request = new AlipaySystemOauthTokenRequest();
            $request->setGrantType("authorization_code");
            $request->setCode($auth_code);
            $result = $aop->execute($request);
            if (isset($result->error_response)) {
                return $this->error('请重新扫码打开');
            }
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";

            if (!isset($result->$responseNode->user_id)) {
                // return $this->error($result->$responseNode->sub_msg);
                return $this->error('请重新扫码打开');
            }

            $user_id = $result->$responseNode->user_id;
            $_SESSION['alipay_user_id'] = $user_id;
        }

        return view('alipay', [
            'qrcode_id' => $id,
            'user_id' => $user_id,
            'title' => $qrcode['title']
        ]);
    }

    /**
     * 创建订单
     */
    public function createOrder()
    {
        $qrcode_id = input('qrcode_id', 0, 'intval');
        $total_fee = input('total_fee', 0);
        $user_id = input('user_id', '', 'trim');
        $remark = input('remark', '', 'trim');
        $pay_type = input('pay_type', 'alipay', 'trim');
        if ($total_fee <= 0) {
            return json([
                'errno' => 1,
                'msg' => '请输入金额'
            ]);
        }

        $qrcode = Db::name('qrcode')
            ->where('id', $qrcode_id)
            ->find();
        $shop = Db::name('shop')
            ->where('id', $qrcode['shop_id'])
            ->find();

        if ($pay_type == 'alipay') {
            $out_trade_no = 'A' . date('YmdHis') . rand(1000, 9999);
        } else {
            $out_trade_no = 'W' . date('YmdHis') . rand(1000, 9999);
        }
        $orderid = Db::name('order')->insertGetId([
            'agent_id' => $shop['agent_id'],
            'shop_id' => $qrcode['shop_id'],
            'qrcode_id' => $qrcode_id,
            'user_id' => $user_id,
            'out_trade_no' => $out_trade_no,
            'transaction_id' => '',
            'total_fee' => $total_fee * 100,
            'order_type' => 'pay',
            'pay_type' => $pay_type,
            'remark' => $remark,
            'status' => 0, // 0-待付款；1-成功；2-失败
            'add_time' => time()
        ]);
        if ($pay_type == 'alipay') {
            $payConfig = getShopPayConfig($shop, 'alipay');
            Db::name('order')
                ->where('id', $orderid)
                ->update(['rate' => $payConfig['rate'] * 100]);

            $notifyUrl = getNotifyUrl('alipay');

            $aop = get_alipay_aop($payConfig);
            $request = new AlipayTradeCreateRequest();
            $request->setBizContent(json_encode([
                'subject' => '商家收款',
                'out_trade_no' => $out_trade_no,
                'total_amount' => $total_fee,
                'buyer_id' => $user_id
            ]));
            $request->setNotifyUrl($notifyUrl);
            if ($payConfig['type'] == 'server') {
                $result = $aop->execute($request, '', $payConfig['app_auth_token']);
            } elseif ($payConfig['type'] == 'shop') {
                $result = $aop->execute($request);
            }
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if (!empty($resultCode) && $resultCode == 10000) {
                Db::name('order')
                    ->where('id', $orderid)
                    ->update([
                        'transaction_id' => $result->$responseNode->trade_no
                    ]);

                return json([
                    'errno' => 0,
                    'data' => $result->$responseNode->trade_no
                ]);
            } else {
                return json([
                    'errno' => 1,
                    'msg' => $result->$responseNode->sub_msg
                ]);
            }
        }

        if ($pay_type == 'wxpay') {
            $openid = input('openid', '', 'trim');
            $payConfig = getShopPayConfig($shop, 'wxpay');
            Db::name('order')
                ->where('id', $orderid)
                ->update(['rate' => $payConfig['rate'] * 100]);
            $notifyUrl = getNotifyUrl('wxpay');

            $input = new WxPayUnifiedOrder();
            $input->SetBody("商家收款");
            $input->SetAttach("商家收款");
            $input->SetOut_trade_no($out_trade_no);
            $input->SetTotal_fee($total_fee * 100);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetNotify_url($notifyUrl);
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openid);
            if ($payConfig['type'] == 'server') {
                $input->SetSubMchID($payConfig['sub_mch_id']);
            }
            // $input->SetSubOpenid($openid);
            $WxPayApi = new WxPayApi();
            $config = new WxPayConfig();
            $config->SetAppId($payConfig['appid']);
            $config->SetAppSecret($payConfig['appsecret']);
            $config->SetMerchantId($payConfig['mch_id']);
            if ($payConfig['type'] == 'server') {
                $config->SetSubMchID($payConfig['sub_mch_id']);
            }
            $config->SetKey($payConfig['key']);
            $config->SetSignType("HMAC-SHA256");
            $config->SetNotifyUrl($notifyUrl);

            $unifiedOrder = $WxPayApi->unifiedOrder($config, $input);

            // 生成调起jsapi-pay的js参数
            $JsApiPay = new JsApiPay();
            $jsApiParameters = $JsApiPay->GetJsApiParameters($config, $unifiedOrder);

            return json([
                'errno' => 0,
                'jsApiParameters' => json_decode($jsApiParameters, true)
            ]);
        }
    }

    /**
     * 微信支付H5页面
     */
    public function wxpay()
    {
        // 根据code得到openid
        $id = input('id', 0, 'intval');
        $code = input('code', '', 'trim');

        $qrcode = Db::name('qrcode')
            ->where('id', $id)
            ->find();
        $shop = Db::name('shop')
            ->where('id', $qrcode['shop_id'])
            ->find();
        $payConfig = getShopPayConfig($shop, 'wxpay');
        if (!$payConfig['status']) {
            return $this->error('未开启微信付款');
        }
        if(empty($_SESSION['wxpay_openid']) && empty($code)) {
            $query = $this->ToUrlParams([
                'appid' => $payConfig['appid'],
                'response_type' => 'code',
                'scope' => 'snsapi_base',
                'redirect_uri' => urlencode(mobileUrl('/pay/qrcode/wxpay', ['id' => $id]))
            ]);
            header("location:https://open.weixin.qq.com/connect/oauth2/authorize?" . $query);
            exit;
        }

        if(!empty($_SESSION['wxpay_openid'])) {
            $openid = $_SESSION['wxpay_openid'];
        } else {
            $config = new WxPayConfig();
            $config->SetAppId($payConfig['appid']);
            $config->SetAppSecret($payConfig['appsecret']);
            $config->SetMerchantId($payConfig['mch_id']);
            $config->SetKey($payConfig['key']);

            $JsApiPay = new JsApiPay();
            $openid = $JsApiPay->getOpenidFromMp($config, $code);
            if (empty($openid)) {
                return $this->error('请重新扫码打开');
            }
            $_SESSION['wxpay_openid'] = $openid;
        }


        //获得jsapi的参数，signPackage，赋值到html页面
        $jssdk = new jssdk($payConfig['appid'], $payConfig['appsecret']);
        $signPackage = $jssdk->getSignPackage();

        return view('wxpay', [
            'signPackage' => $signPackage,
            'qrcode_id' => $id,
            'openid' => $openid,
            'title' => $qrcode['title']
        ]);
    }

    public function paySuccess()
    {
        $type = input('type', 'alipay', 'trim');
        $ad = [];
        $adConfig = getSystemSetting('ad');
        if ($type == 'alipay') {
            if (!empty($adConfig['ad_alipay_type'])) {
                if ($adConfig['ad_alipay_type'] == 'image') {
                    $ad['title'] = isset($adConfig['ad_alipay_image_title']) ? $adConfig['ad_alipay_image_title'] : '';
                    $ad['image'] = isset($adConfig['ad_alipay_image']) ? $adConfig['ad_alipay_image'] : '';
                    $ad['link'] = isset($adConfig['ad_alipay_image_link']) ? $adConfig['ad_alipay_image_link'] : '';
                } elseif ($adConfig['ad_alipay_type'] == 'link' && !empty($adConfig['ad_alipay_link'])) {
                    header('location: ' . $adConfig['ad_alipay_link']);
                    exit;
                }
            }
        } else if ($type == 'wxpay') {
            if (!empty($adConfig['ad_wxpay_type'])) {
                if ($adConfig['ad_wxpay_type'] == 'image') {
                    $ad['title'] = isset($adConfig['ad_wxpay_image_title']) ? $adConfig['ad_wxpay_image_title'] : '';
                    $ad['image'] = isset($adConfig['ad_wxpay_image']) ? $adConfig['ad_wxpay_image'] : '';
                    $ad['link'] = isset($adConfig['ad_wxpay_image_link']) ? $adConfig['ad_wxpay_image_link'] : '';
                } elseif ($adConfig['ad_wxpay_type'] == 'link' && !empty($adConfig['ad_wxpay_link'])) {
                    header('location: ' . $adConfig['ad_wxpay_link']);
                    exit;
                }
            }
        }
        return view('paySuccess', [
            'pay_type' => $type,
            'ad' => $ad
        ]);
    }

    private function toUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * @param $msg
     * 在页面上输出错误信息
     */
    private function error($message)
    {
        return view('error', ['message' => $message]);
    }

}
