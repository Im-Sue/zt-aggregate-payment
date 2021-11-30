<?php
namespace app\pay\controller;

use think\facade\Db;
use think\facade\Request;

class Wxpay
{
    public function login()
    {
        $username = input('username');
        $password = input('password');
        $windowWidth = input('windowWidth');
        $windowHeight = input('windowHeight');
        $user = Db::name('im_user')->where(['username' => $username])->find();
        if (!$user || md5($user['password']) != md5($password)) {
            return [
                'errno' => 1,
                'message' => '账号或密码错误'
            ];
        }
        //更新用户的屏幕宽高
        Db::name('im_user')
            ->where('id', $user['id'])
            ->update([
                'window_width' => $windowWidth,
                'window_height' => $windowHeight
            ]);
        //聊天室id
        $roomId = Db::name('im_room')
            ->where('uid1', $user['id'])
            ->whereOr('uid2', $user['id'])
            ->value('id');

        $token = uniqid() . $user['id'];
        /*session_id($token);
        session_start();
        $_SESSION['user'] = json_encode($user);*/

        return json([
            'errno' => 0,
            'message' => '登录成功',
            'data' => [
                'uid' => $user['id'],
                'username' => $user['username'],
                'avatar' => $user['avatar'],
                'nickname' => $user['nickname'],
                'token' => $token,
                'room_id' => $roomId
            ]
        ]);
    }
}
