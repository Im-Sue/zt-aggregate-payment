<?php

namespace app\admin\controller;

use think\facade\Db;

class Setting extends Base
{
    public function getSetting()
    {
        $name = input('name', 'speaker', 'trim');

        $setting = getSystemSetting($name);
        if(!$setting || count($setting) == 0) {
            if ($name == 'ad') {
                $setting = [
                    'ad_alipay_type' => '',
                    'ad_wxpay_type' => ''
                ];
            }
            if ($name == 'speaker') {
                $setting = [
                    'pinsheng_username' => '',
                    'pinsheng_password' => ''
                ];
            }
        }

        return successJson($setting);
    }

    public function setSetting()
    {
        $name = input('name', '', 'trim');
        $data = input('data', '', 'trim');
        if(!in_array($name, ['ad', 'speaker'])) {
            return errorJson('参数错误');
        }
        $res = setSystemSetting($name, $data);
        if ($res) {
            return successJson('', '保存成功');
        } else {
            return errorJson('保存失败，请重试！');
        }
    }
}
