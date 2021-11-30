<?php

namespace app\admin\controller;

use think\facade\Db;

class User extends Base
{
    /**
     * 返回当前登录管理员的角色
     */
    public function info()
    {
        return successJson([
            'roles' => [self::$admin['role']],
            'introduction' => '',
            'avatar' => mediaUrl(self::$admin['avatar']),
            'logo' => mediaUrl('/static/img/logo.png'),
            'logo_mini' => mediaUrl('/static/img/logo-mini.png'),
            'name' => self::$admin['realname'] ? self::$admin['realname'] : self::$admin['phone'],
            'nopass' => isset(self::$admin['nopass']) ? self::$admin['nopass'] : 0
        ]);
    }

    /**
     * 修改密码
     */
    public function changePassword()
    {
        $passwordOld = input('password_old');
        $passwordNew = input('password_new');
        $admin = Db::name('admin')
            ->where('id', self::$admin['id'])
            ->find();
        if (!$admin) {
            return errorJson('修改失败，请重新登录');
        }
        // 验证密码
        if (md5($admin['password']) != md5($passwordOld)) {
            return errorJson('原密码不正确');
        }
        // 验证新密码
        if (strlen($passwordNew) < 6 || strlen($passwordNew) > 18) {
            return errorJson('新密码长度不符合规范');
        }

        $rs = Db::name('admin')
            ->where('id', self::$admin['id'])
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
        $_SESSION['admin'] = null;
        self::$admin = null;
        return successJson('', '已退出登录');
    }
}
