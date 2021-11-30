<?php

namespace app\admin\controller;

use think\facade\Db;

class Order extends Base
{
    /**
     * 获取订单列表
     */
    public function getList()
    {
        $page = input('page', 1, 'intval');
        $pagesize = input('pagesize', 10, 'intval');
        $page = max(1, $page);
        $pagesize = max(1, $pagesize);
        $pay_type = input('pay_type', '', 'trim');
        $shop_id = input('shop_id', 0, 'intval');
        $qrcode_id = input('qrcode_id', 0, 'intval');
        $date = input('date', '', 'trim');
        if (empty($date)) {
            $date = [date('Y-m-d'), date('Y-m-d')];
        }
        $start_time = strtotime($date[0]);
        $end_time = strtotime($date[1]) + 24 * 3600;

        $where = [];
        $where[] = ['status', '=', 1];
        $where[] = ['pay_time', 'between', [$start_time, $end_time]];
        // 按支付方式
        if ($pay_type) {
            $where[] = ['pay_type', '=', $pay_type];
        }
        // 按商户
        if ($shop_id) {
            $where[] = ['shop_id', '=', $shop_id];
        }
        // 按二维码
        if ($qrcode_id) {
            $where[] = ['qrcode_id', '=', $qrcode_id];
        }

        $list = Db::name('order')
            ->where($where)
            ->page($page, $pagesize)
            ->order('pay_time desc, id desc')
            ->select()
            ->each(function ($item, $index) {
                $item['shop_title'] = Db::name('shop')->where('id', $item['shop_id'])->value('title');
                $item['qrcode_title'] = Db::name('qrcode')->where('id', $item['qrcode_id'])->value('title');
                $item['total_fee'] = $item['total_fee'] / 100;
                $item['settlement_total_fee'] = $item['settlement_total_fee'] / 100;
                $item['fee'] = $item['fee'] / 100;
                $item['pay_time'] = date('Y-m-d H:i:s', $item['add_time']);
                unset($item['shop_id']);
                unset($item['qrcode_id']);
                unset($item['id']);
                return $item;
            });
        $count = Db::name('order')
            ->where($where)
            ->count();

        return successJson([
            'count' => $count,
            'list' => $list
        ]);
    }

    /**
     * 统计
     */
    public function getTongji()
    {
        $pay_type = input('pay_type', '', 'trim');
        $shop_id = input('shop_id', 0, 'intval');
        $qrcode_id = input('qrcode_id', 0, 'intval');
        $date = input('date', '', 'trim');
        if (empty($date)) {
            $date = [date('Y-m-d'), date('Y-m-d')];
        }
        $start_time = strtotime($date[0]);
        $end_time = strtotime($date[1]) + 24 * 3600;

        $where = [];
        $where[] = ['status', '=', 1];
        $where[] = ['pay_time', 'between', [$start_time, $end_time]];// 按支付方式
        // 按支付方式
        if ($pay_type) {
            $where[] = ['pay_type', '=', $pay_type];
        }
        // 按商户
        if ($shop_id) {
            $where[] = ['shop_id', '=', $shop_id];
        }
        // 按二维码
        if ($qrcode_id) {
            $where[] = ['qrcode_id', '=', $qrcode_id];
        }

        // 订单数量、订单金额
        $data = Db::name('order')
            ->where($where)
            ->field('count(id) as order_count,sum(total_fee) as order_amount')
            ->find();

        return successJson([
            'orderCount' => intval($data['order_count']),
            'orderAmount' => intval($data['order_amount']) / 100
        ]);
    }

    /**
     * 检索店铺列表
     */
    public function getShopList()
    {
        $keyword = input('keyword', '');
        $where = [];
        $where[] = ['title|link_name|link_phone', 'like', '%' . $keyword . '%'];
        $list = Db::name('shop')
            ->where($where)
            ->field('id, title')
            ->limit(20)
            ->select()
            ->toArray();

        return successJson($list);
    }

    /**
     * 检索二维码列表
     */
    public function getQrcodeList()
    {
        $keyword = input('keyword', '');

        $list = Db::name('qrcode')
            ->where('title', 'like', '%' . $keyword . '%')
            ->field('id, title')
            ->select()
            ->toArray();

        return successJson($list);
    }

    /**
     * 退款
     */
    public function refund()
    {
        $id = input('id', 0, 'intval');
        $pwd = input('pwd', '', 'trim');
        $refund_pwd = Db::name('admin')
            ->where('id', self::$admin['id'])
            ->value('refund_pwd');
        if (md5($pwd) != md5($refund_pwd)) {
            return errorJson('退款密码不正确');
        }
        $order = Db::name('order')
            ->where('id', $id)
            ->find();
        if ($order) {
            if($order['pay_type'] == 'alipay') {
                $payConfig = getShopPayConfig($order['shop_id'], 'alipay');
                return successJson('', '支付宝退款成功');
            } elseif ($order['pay_type'] == 'wxpay') {
                $payConfig = getShopPayConfig($order['shop_id'], 'wxpay');
                return successJson('', '微信退款成功');
            }
        }
    }
}
