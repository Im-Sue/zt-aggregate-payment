<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\Request;

class Login
{
    public function login()
    {
        $username = input('username');
        $password = input('password');
        $admin = Db::name('admin')->where(['phone' => $username])->find();
        if (!$admin || md5($admin['password']) != md5($password)) {
            return errorJson('账号或密码错误');
        }
        $token = uniqid() . $admin['id'];
        session_id($token);
        session_start();
        $_SESSION['admin'] = json_encode($admin);

        return successJson(['token' => $token], '登录成功');
    }
}
