<?php
namespace app\agent\controller;

use think\facade\Db;

class Login
{
    public function login()
    {
        $username = input('username');
        $password = input('password');
        $agent = Db::name('agent')->where(['phone' => $username])->find();
        if (!$agent || md5($agent['password']) != md5($password)) {
            return errorJson('账号或密码错误');
        }
        $token = uniqid() . $agent['id'];
        session_id($token);
        session_start();
        $_SESSION['agent'] = json_encode($agent);

        return successJson(['token' => $token], '登录成功');
    }
}
