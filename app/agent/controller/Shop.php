<?php

namespace app\agent\controller;

use think\facade\Db;

class Shop extends Base
{
    /**
     * 获取商户列表
     */
    public function getList()
    {
        $page = input('page', 1, 'intval');
        $pagesize = input('pagesize', 10, 'intval');
        $page = max(1, $page);
        $pagesize = max(1, $pagesize);
        $keyword = input('keyword', '', 'trim');

        // 查询商户列表
        $where = [
            ['agent_id', '=', self::$agent['id']]
        ];
        if (!empty($keyword)) {
            $where[] = ['title|address|link_name|link_phone|remark', 'like', '%' . $keyword . '%'];
        }
        $list = Db::name('shop')
            ->where($where)
            ->field('id,title,address,link_name,link_phone,remark,FROM_UNIXTIME(add_time, \'%Y-%m-%d %H:%i\') as add_time')
            ->page($page, $pagesize)
            ->select();
        $count = Db::name('shop')
            ->where($where)
            ->count();

        return successJson([
            'count' => $count,
            'list' => $list
        ]);
    }

    /**
     * 取单个商户账号资料
     */
    public function getInfo()
    {
        $id = input('id', 0, 'intval');

        $info = Db::name('shop')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['id', '=', $id]
            ])
            ->field('id,title,address,link_phone,link_name,remark')
            ->find();
        if (!$info) {
            return errorJson('没有找到此商户，请刷新页面重试');
        }

        return successJson($info);
    }

    /**
     * 更新或新增商户账号
     */
    public function saveInfo()
    {
        $id = input('id', 0, 'intval');
        $title = input('title', '', 'trim');
        $address = input('address', '', 'trim');
        $link_name = input('link_name', '', 'trim');
        $link_phone = input('link_phone', '', 'trim');
        $remark = input('remark', '', 'trim');

        // 判断账号是否存在
        $shop = Db::name('shop')
            ->where([
                ['id', '<>', $id],
                ['title', '=', $title]
            ])
            ->find();
        if ($shop) {
            return errorJson('已存在相同名称的商户，请更换');
        }

        $data = [
            'title' => $title,
            'address' => $address,
            'link_name' => $link_name,
            'link_phone' => $link_phone,
            'remark' => $remark
        ];
        // 更新或添加
        if ($id) {
            $res = Db::name('shop')
                ->where([
                    ['agent_id', '=', self::$agent['id']],
                    ['id', '=', $id]
                ])
                ->update($data);
        } else {
            $data['agent_id'] = self::$agent['id'];
            $data['add_time'] = time();
            $res = Db::name('shop')->insert($data);
        }

        if ($res !== false) {
            return successJson('', '保存成功');
        } else {
            return errorJson('保存失败，请重试！');
        }

    }

    /**
     * 删除商户账号
     */
    public function del()
    {
        $id = input('id', 0, 'intval');
        $res = Db::name('shop')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['id', '=', $id]
            ])
            ->delete();
        if ($res !== false) {
            return successJson('', '删除成功');
        } else {
            return errorJson('删除失败，请重试！');
        }
    }

    /**
     * 获取商户支付配置
     */
    public function getPayInfo()
    {
        $id = input('id', 0, 'intval');
        $info = Db::name('shop')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['id', '=', $id]
            ])
            ->field('id,alipay_config,wxpay_config')
            ->find();
        if (!$info) {
            return errorJson('没有找到此商户，请刷新页面重试');
        }

        $alipay_config = json_decode($info['alipay_config'], true);
        $wxpay_config = json_decode($info['wxpay_config'], true);
        $alipay_config = $alipay_config ? $alipay_config : [];
        $wxpay_config = $wxpay_config ? $wxpay_config : [];

        $data = array_merge($alipay_config, $wxpay_config);
        $data['id'] = $info['id'];

        return successJson($data);
    }

    /**
     * 获取服务商列表
     */
    public function getServerList()
    {
        $list = Db::name('server')
            ->field('id,type,title')
            ->select();
        $alipay = [];
        $wxpay = [];
        foreach ($list as $v) {
            if ($v['type'] == 'alipay') {
                $alipay[] = $v;
            } elseif ($v['type'] == 'wxpay') {
                $wxpay[] = $v;
            }
        }

        return successJson([
            'alipay' => $alipay,
            'wxpay' => $wxpay,
        ]);
    }

    /**
     * 更新商户支付配置
     */
    public function savePayInfo()
    {
        $id = input('id', 0, 'intval');
        // 支付宝配置
        $alipay_config = [];
        $alipay_status = input('alipay_status', 0, 'intval');
        $alipay_config['alipay_status'] = $alipay_status;
        if ($alipay_status == 1) {
            $alipay_type = input('alipay_type', '', 'trim');
            $alipay_config['alipay_type'] = $alipay_type;
            if ($alipay_type == 'server') {
                $alipay_config['alipay_server_id'] = input('alipay_server_id', 0, 'intval');
                $alipay_config['alipay_pid'] = input('alipay_pid', '', 'trim');
                $alipay_config['alipay_token'] = input('alipay_token', '', 'trim');
            } elseif ($alipay_type == 'shop') {
                $alipay_config['alipay_appid'] = input('alipay_appid', '', 'trim');
                $alipay_config['alipay_private_key'] = input('alipay_private_key', '', 'trim');
                $alipay_config['alipay_public_key'] = input('alipay_public_key', '', 'trim');
            }
            $alipay_config['alipay_rate'] = input('alipay_rate', 0, 'trim');
        }

        // 微信支付配置
        $wxpay_config = [];
        $wxpay_status = input('wxpay_status', 0, 'intval');
        $wxpay_config['wxpay_status'] = $wxpay_status;
        if ($wxpay_status == 1) {
            $wxpay_type = input('wxpay_type', '', 'trim');
            $wxpay_config['wxpay_type'] = $wxpay_type;
            if ($wxpay_type == 'server') {
                $wxpay_config['wxpay_server_id'] = input('wxpay_server_id', 0, 'intval');
                $wxpay_config['wxpay_mch_id'] = input('wxpay_mch_id', '', 'trim');
            } elseif ($wxpay_type == 'shop') {
                $wxpay_config['wxpay_appid'] = input('wxpay_appid', '', 'trim');
                $wxpay_config['wxpay_appsecret'] = input('wxpay_appsecret', '', 'trim');
                $wxpay_config['wxpay_mch_id'] = input('wxpay_mch_id', '', 'trim');
                $wxpay_config['wxpay_key'] = input('wxpay_key', '', 'trim');
                $wxpay_config['wxpay_apiclient_cert'] = input('wxpay_apiclient_cert', '', 'trim');
                $wxpay_config['wxpay_apiclient_key'] = input('wxpay_apiclient_key', '', 'trim');
            }
            $wxpay_config['wxpay_rate'] = input('wxpay_rate', 0, 'trim');
        }

        $res = Db::name('shop')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['id', '=', $id]
            ])
            ->update([
                'alipay_config' => json_encode($alipay_config),
                'wxpay_config' => json_encode($wxpay_config)
            ]);

        if ($res !== false) {
            return successJson('', '保存成功');
        } else {
            return errorJson('保存失败，请重试！');
        }
    }
}
