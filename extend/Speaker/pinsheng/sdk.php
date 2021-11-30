<?php

namespace Speaker\pinsheng;
class sdk
{
    // 接口地址
    private $getTokenUrl = 'https://serv.51pbox.com/api/getToken';
    private $sendMsgUrl = 'https://serv.51pbox.com/api/message/sendMsg';
    private $getStatusUrl = 'https://serv.51pbox.com/api/message/getStatus/';
    // 品生账号
    private $username = '';
    private $password = '';

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    private function getToken()
    {
        $now = time();
        $token = '';
        $tokenFile = __DIR__ . '/token.txt';
        // 先从缓存文件里取
        if (file_exists($tokenFile)) {
            $con = file_get_contents($tokenFile);
            $con = json_decode($con, true);
            if ($con['expire_time'] > $now + 1800) {
                $token = $con['token'];
            }
        }
        if ($token) {
            return $token;
        }
        // 去服务器取
        $res = $this->http_request($this->getTokenUrl, [
            'username' => $this->username,
            'password' => $this->password
        ]);
        if (!$res) {
            return '';
        }
        $res = json_decode($res, true);
        if ($res['code'] == 200) {
            $token = $res['data']['token'];
        }
        if (!$token) {
            return '';
        }
        // 存到缓存文件
        file_put_contents($tokenFile, json_encode(['token' => $token, 'expire_time' => $now + 7200]));
        return $token;
    }

    /**
     * @param $pay_type
     * @param $money
     * @param $out_trade_no
     * @return array|bool
     * 播放声音
     */
    public function play($devid, $pay_type, $money, $out_trade_no = '')
    {
        $type = 1;
        switch ($pay_type) {
            case 'alipay':
                $pay_type = '支付宝';
                $type = 2;
                break;
            case 'wxpay':
                $pay_type = '微信';
                $type = 3;
                break;
        }

        if (empty($out_trade_no)) {
            $out_trade_no = uniqid();
        }

        $data = [
            'devid' => $devid,
            'time' => time(),
            'reqid' => md5(uniqid() . rand(10000, 9999)),
            'message' => $pay_type . '到账' . $money . '元',
            'type' => $type,
            'payment' => $money * 100,
            'orderid' => $out_trade_no,
            'timeout' => 15000
        ];

        $token = $this->getToken();
        if (!$token) {
            return false;
        } else {
            $res = $this->http_request($this->sendMsgUrl, $data, $token);
            if (!$res) {
                return false;
            }
            $res = json_decode($res, true);
            if ($res['code'] == 200) {
                return true;
            } else {
                return [
                    'errno' => 1,
                    'message' => (isset($res['data']) && isset($res['data']['message'])) ? $res['data']['message'] : $res['message']
                ];
            }
        }
    }

    public function getStatus($devid)
    {
        $token = $this->getToken();
        if (!$token) {
            return 'offline';
        }
        $res = $this->http_request($this->getStatusUrl, '', $token);
        if (!$res) {
            return 'offline';
        }
        $res = json_decode($res, true);
        if ($res['code'] == 200) {
            return $res['data'];
        }

        return 'offline';
    }

    private function http_request($url, $data = [], $token = '')
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $header = [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data)
        ];
        if ($token) {
            $header[] = 'token:' . $token;
        }
        //初始化 curl
        $ch = curl_init();
        //设置目标服务器
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $res = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($errno) {
            return '';
        }
        return $res;
    }
}