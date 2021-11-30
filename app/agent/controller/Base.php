<?php
namespace app\agent\controller;

use think\facade\Request;

class Base
{
    protected static $agent;

    public function __construct()
    {
        $token = Request::header('x-token');
        if($token) {
            session_id($token);
        }
        session_start();
        if (empty($_SESSION['agent'])) {
            die(json_encode(['errno' => '403', 'message' => '你已掉线，请重新登录']));
        }
        self::$agent = json_decode($_SESSION['agent'], true);
    }
}
