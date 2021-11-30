<?php

namespace app\admin\controller;

use think\facade\Db;

class Server extends Base
{
    /**
     * 获取列表
     */
    public function getList()
    {
        $page = input('page', 1, 'intval');
        $pagesize = input('pagesize', 10, 'intval');
        $page = max(1, $page);
        $pagesize = max(1, $pagesize);
        $keyword = input('keyword', '', 'trim');

        $where = [];
        if (!empty($keyword)) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }
        $list = Db::name('server')
            ->where($where)
            ->field('id,title,type,config,remark')
            ->page($page, $pagesize)
            ->select()->each(function ($item) {
                if($item['config']) {
                    $config = @json_decode($item['config'], true);
                    if($config) {
                        $item['appid'] = $config['appid'];
                    }
                }
                unset($item['config']);
                return $item;
            });
        $count = Db::name('server')
            ->where($where)
            ->count();

        return successJson([
            'count' => $count,
            'list' => $list
        ]);
    }

    /**
     * 更新或新增服务商
     */
    public function saveInfo()
    {
        $id = input('id', 0, 'intval');
        $type = input('type', '', 'trim');
        $title = input('title', '', 'trim');
        $remark = input('remark', '', 'trim');
        $config = [];
        if ($type == 'alipay') {
            $appid = input('appid', '', 'trim');
            $pid = input('pid', '', 'trim');
            $private_key = input('private_key', '', 'trim');
            $public_key = input('public_key', '', 'trim');
            $config = [
                'appid' => $appid,
                'pid' => $pid,
                'private_key' => $private_key,
                'public_key' => $public_key,
            ];
        } elseif ($type == 'wxpay') {
            $appid = input('appid', '', 'trim');
            $appsecret = input('appsecret', '', 'trim');
            $mch_id = input('mch_id', '', 'trim');
            $key = input('key', '', 'trim');
            $apiclient_cert = input('apiclient_cert', '', 'trim');
            $apiclient_key = input('apiclient_key', '', 'trim');
            $config = [
                'appid' => $appid,
                'appsecret' => $appsecret,
                'mch_id' => $mch_id,
                'key' => $key,
                'apiclient_cert' => $apiclient_cert,
                'apiclient_key' => $apiclient_key,
            ];
        }

        $data = [
            'title' => $title,
            'type' => $type,
            'config' => json_encode($config),
            'remark' => $remark
        ];
        // 更新或添加
        if ($id) {
            $res = Db::name('server')->where('id', $id)->update($data);
        } else {
            $res = Db::name('server')->insert($data);
        }

        if ($res !== false) {
            return successJson('', '保存成功');
        } else {
            return errorJson('保存失败，请重试！');
        }

    }



    /**
     * 取单个服务商资料
     */
    public function getInfo()
    {
        $id = input('id', 0, 'intval');

        $info = Db::name('server')
            ->where('id', $id)
            ->field('id,title,type,config,remark')
            ->find();
        if(!$info) {
            return errorJson('没有找到相关信息，请刷新页面重试');
        }
        $config = json_decode($info['config'], true);

        unset($info['config']);
        return successJson(array_merge($info, $config));
    }

    /**
     * 删除服务商
     */
    public function del()
    {
        $id = input('id', 0, 'intval');
        $res = Db::name('server')
            ->where('id', $id)
            ->delete();
        if ($res !== false) {
            return successJson('', '删除成功');
        } else {
            return errorJson('删除失败，请重试！');
        }
    }
}
