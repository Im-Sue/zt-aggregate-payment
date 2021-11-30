<?php

namespace app\agent\controller;

use think\facade\Db;

class Index extends Base
{
    public function getTongji()
    {
        $today = date('Y-m-d');
        $start_time = strtotime($today);
        $end_time = strtotime($today . ' 23:59:59');

        // 查商户 - 总数
        $shopTotal = Db::name('shop')
            ->where('agent_id', self::$agent['id'])
            ->count();
        // 查商户 - 新增
        $shopTotalNew = Db::name('shop')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['add_time', 'between', [$start_time, $end_time]]
            ])
            ->count();

        // 订单数量、订单金额 - 总数
        $data = Db::name('order')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['status', '=', 1]
            ])
            ->field('count(id) as order_count,sum(total_fee) as order_amount')
            ->find();
        $orderTotal = $data['order_count'];
        $orderAmount = $data['order_amount'] / 100;

        // 订单数量、订单金额 - 新增
        $data = Db::name('order')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['pay_time', 'between', [$start_time, $end_time]]
            ])
            ->field('count(id) as order_count,sum(total_fee) as order_amount')
            ->find();
        $orderTotalNew = $data['order_count'];
        $orderAmountNew = $data['order_amount'] / 100;

        // 二维码数量 - 总数
        $qrcodeTotal = Db::name('qrcode')
            ->where('agent_id', self::$agent['id'])
            ->count();
        // 二维码数量 - 新增
        $qrcodeTotalNew = Db::name('qrcode')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['add_time', 'between', [$start_time, $end_time]]
            ])
            ->count();

        return successJson([
            'shopTotal' => $shopTotal ? $shopTotal : 0,
            'shopTotalNew' => $shopTotalNew ? $shopTotalNew : 0,
            'orderTotal' => $orderTotal ? $orderTotal : 0,
            'orderTotalNew' => $orderTotalNew ? $orderTotalNew : 0,
            'orderAmount' => $orderAmount ? $orderAmount : 0,
            'orderAmountNew' => $orderAmountNew ? $orderAmountNew : 0,
            'qrcodeTotal' => $qrcodeTotal ? $qrcodeTotal : 0,
            'qrcodeTotalNew' => $qrcodeTotalNew ? $qrcodeTotalNew : 0,
        ]);
    }

    public function getChartData()
    {
        $today = date('Y-m-d');
        $where = [
            ['agent_id', '=', self::$agent['id']],
            ['status', '=', 1]
        ];

        $timeArr = [];
        $countArr = [];
        $amountArr = [];
        for ($i = 15; $i >= 0; $i--) {
            $start_time = strtotime($today . "-{$i} day");
            $end_time = $start_time + 24 * 3600 - 1;

            $where2 = $where;
            $where2[] = ['pay_time', 'between', [$start_time, $end_time]];
            $data = Db::name('order')
                ->where($where2)
                ->field('count(id) as order_count,sum(total_fee) as order_amount')
                ->find();

            $timeArr[] = date('m-d', $start_time);
            $countArr[] = intval($data['order_count']);
            $amountArr[] = $data['order_amount'] / 100;
        }

        return successJson([
            'times' => $timeArr,
            'count' => $countArr,
            'amount' => $amountArr
        ]);
    }
}
