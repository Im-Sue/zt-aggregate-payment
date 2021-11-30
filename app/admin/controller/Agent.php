<?php

namespace app\admin\controller;

use think\facade\Db;

class Agent extends Base
{
    /**
     * 获取代理商列表
     */
    public function getList()
    {
        $page = input('page', 1, 'intval');
        $pagesize = input('pagesize', 10, 'intval');
        $page = max(1, $page);
        $pagesize = max(1, $pagesize);
        $keyword = input('keyword', '', 'trim');

        // 查询代理商列表
        $where = [];
        if (!empty($keyword)) {
            $where[] = ['phone|title|remark', 'like', '%' . $keyword . '%'];
        }
        $list = Db::name('agent')
            ->where($where)
            ->field('id,phone,title,remark,FROM_UNIXTIME(add_time, \'%Y-%m-%d %H:%i:%s\') as add_time')
            ->page($page, $pagesize)
            ->select()
            ->toArray();
        $count = Db::name('agent')
            ->where($where)
            ->count();
        foreach ($list as &$v) {
            $v['shop_num'] = Db::name('shop')
                ->where('agent_id', $v['id'])
                ->count();
        }

        return successJson([
            'count' => $count,
            'list' => $list
        ]);
    }

    /**
     * 更新或新增代理商账号
     */
    public function saveInfo()
    {
        $id = input('id', 0, 'intval');
        $phone = input('phone', '', 'trim');
        $title = input('title', '', 'trim');
        $password = input('password', '', 'trim');
        $remark = input('remark', '', 'trim');

        // 判断账号是否存在
        $user = Db::name('agent')
            ->where([
                ['id', '<>', $id],
                ['phone', '=', $phone]
            ])
            ->find();
        if ($user) {
            return errorJson('保存失败：系统内已有此账号(' . $phone . ')');
        }

        // 组装要保存的数组
        $data = [
            'phone' => $phone,
            'title' => $title,
            'remark' => $remark,
            'add_time' => time()
        ];
        if ($password) {
            $data['password'] = $password;
        }

        // 更新或添加
        if ($id) {
            Db::name('agent')->where('id', $id)->update($data);
        } else {
            $data['add_time'] = time();
            Db::name('agent')->insert($data);
        }

        // 返回数据
        return successJson('', '保存成功');
    }

    /**
     * 获取单个代理商账号
     */
    public function getInfo()
    {
        $id = input('id', 0, 'intval');
        $info = Db::name('agent')
            ->where('id', $id)
            ->field('id,phone,title,remark')
            ->find();
        return successJson($info);
    }

    /**
     * 删除代理商账号
     */
    public function del()
    {
        $id = input('id', 0, 'intval');
        $shop = Db::name('shop')
            ->where('agent_id', $id)
            ->find();
        if ($shop) {
            return errorJson('此代理账号下还有商户，不能删除！');
        }
        $res = Db::name('agent')
            ->where('id', $id)
            ->delete();
        if ($res !== false) {
            return successJson('', '删除成功！');
        } else {
            return errorJson('删除失败，请重试！');
        }
    }

    public function getLoginUrl()
    {
        return successJson(agentLoginUrl());
    }

}
