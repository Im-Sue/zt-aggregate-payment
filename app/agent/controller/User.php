<?php

namespace app\agent\controller;

use think\facade\Db;

class User extends Base
{
    /**
     * 返回当前登录管理员的角色
     */
    public function info()
    {
        return successJson([
            'roles' => ['agent'],
            'introduction' => '',
            'avatar' => mediaUrl(self::$agent['avatar']),
            'logo' => mediaUrl('/static/img/logo-agent.png'),
            'logo_mini' => mediaUrl('/static/img/logo-agent-mini.png'),
            'name' => self::$agent['title'] ? self::$agent['title'] : self::$agent['phone'],
            'nopass' => isset(self::$agent['nopass']) ? self::$agent['nopass'] : 0
        ]);
    }

    /**
     * 修改密码
     */
    public function changePassword()
    {
        $passwordOld = input('password_old');
        $passwordNew = input('password_new');
        $agent = Db::name('agent')
            ->where('id', self::$agent['id'])
            ->find();
        if (!$agent) {
            return errorJson('修改失败，请重新登录');
        }
        // 验证密码
        if (md5($agent['password']) != md5($passwordOld)) {
            return errorJson('原密码不正确');
        }
        // 验证新密码
        if (strlen($passwordNew) < 6 || strlen($passwordNew) > 18) {
            return errorJson('新密码长度不符合规范');
        }

        $rs = Db::name('agent')
            ->where('id', self::$agent['id'])
            ->update([
                'password' => $passwordNew
            ]);
        if ($rs !== false) {
            return successJson('', '密码已修改，请重新登录');
        } else {
            return errorJson('修改失败，请重试');
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $_SESSION['agent'] = null;
        self::$agent = null;
        return successJson('', '已退出登录');
    }
}
