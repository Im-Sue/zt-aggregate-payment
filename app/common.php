<?php
// 应用公共文件
use think\facade\Db;

/**
 * @return mixed|string
 * 获取客户端ip
 */
function get_client_ip()
{
    $ip = '';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) {
            unset($arr[$pos]);
        }
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

/**
 * @param array $data
 * @param string $message
 * @return string
 * 返回成功json
 */
function successJson($data = [], $message = '')
{
    echo json_encode([
        'errno' => 0,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * @param string $message
 * @return string
 * 返回失败json
 */
function errorJson($message = '')
{
    echo json_encode([
        'errno' => 1,
        'message' => $message
    ]);
    exit;
}

/**
 * @param $pass
 * @param $salt
 * @return string
 * 密码加密
 */
function encryptPass($pass, $salt)
{
    return md5(' ' . md5($pass) . $salt);
}

/**
 * @param array $shop
 * @return \AopSDK\AopClient|array
 * new Alipay AopClient
 */
function get_alipay_aop($config)
{
    $aop = new \AopSDK\AopClient();
    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    $aop->appId = $config['appid'];
    $aop->rsaPrivateKey = $config['private_key'];
    $aop->alipayrsaPublicKey = $config['public_key'];
    $aop->apiVersion = '1.0';
    $aop->signType = 'RSA2';
    $aop->postCharset = 'UTF-8';
    $aop->format = 'json';

    return $aop;
}

function getShopPayConfig($shop, $type)
{
    if (is_numeric($shop)) {
        $shop = Db::name('shop')
            ->where('id', $shop)
            ->find();
    }

    if ($type == 'alipay') {
        if (empty($shop['alipay_config'])) {
            return ['status' => 0];
        }
        $config = @json_decode($shop['alipay_config'], true);
        if ($config['alipay_status'] == 0) {
            return ['status' => 0];
        }
        if ($config['alipay_type'] == 'server') {
            $server = Db::name('server')
                ->where('id', $config['alipay_server_id'])
                ->value('config');
            $server = @json_decode($server, true);
            return [
                'status' => 1,
                'type' => 'server',
                'appid' => $server['appid'],
                'private_key' => $server['private_key'],
                'public_key' => $server['public_key'],
                'pid' => $config['alipay_pid'],
                'app_auth_token' => $config['alipay_token'],
                'rate' => $config['alipay_rate']
            ];
        } elseif ($config['alipay_type'] == 'shop') {
            return [
                'status' => 1,
                'type' => 'shop',
                'appid' => $config['alipay_appid'],
                'private_key' => $config['alipay_private_key'],
                'public_key' => $config['alipay_public_key'],
                'rate' => $config['alipay_rate']
            ];
        }
    } elseif ($type == 'wxpay') {
        if (empty($shop['wxpay_config'])) {
            return ['status' => 0];
        }
        $config = @json_decode($shop['wxpay_config'], true);
        if ($config['wxpay_status'] == 0) {
            return ['status' => 0];
        }
        if ($config['wxpay_type'] == 'server') {
            $server = Db::name('server')
                ->where('id', $config['wxpay_server_id'])
                ->value('config');
            $server = @json_decode($server, true);
            return [
                'status' => 1,
                'type' => 'server',
                'appid' => $server['appid'],
                'appsecret' => $server['appsecret'],
                'mch_id' => $server['mch_id'],
                'key' => $server['key'],
                'apiclient_cert' => $server['apiclient_cert'],
                'apiclient_key' => $server['apiclient_key'],
                'sub_mch_id' => $config['wxpay_mch_id'],
                'rate' => $config['wxpay_rate']
            ];
        } elseif ($config['wxpay_type'] == 'shop') {
            return [
                'status' => 1,
                'type' => 'shop',
                'appid' => $config['wxpay_appid'],
                'appsecret' => $config['wxpay_appsecret'],
                'mch_id' => $config['wxpay_mch_id'],
                'key' => $config['wxpay_key'],
                'apiclient_cert' => $config['wxpay_apiclient_cert'],
                'apiclient_key' => $config['wxpay_apiclient_key'],
                'rate' => $config['wxpay_rate']
            ];
        }
    }

    return ['status' => 0];
}

/**
 * @param $brand
 * @return array|mixed
 * 获取系统配置
 */
function getSystemSetting($name)
{
    $setting = Db::name('setting')
        ->where('id', 1)
        ->value($name);
    if (!$setting) {
        return [];
    }

    return json_decode($setting, true);
}

/**
 * @param $brand
 * @return array|mixed
 * 保存系统配置
 */
function setSystemSetting($name, $value)
{
    $setting = Db::name('setting')
        ->where('id', 1)
        ->find();
    if(!$setting) {
        Db::name('setting')
            ->insert(['id' => 1]);
    }
    $res = Db::name('setting')
        ->where('id', 1)
        ->update([
            $name => $value
        ]);

    return $res !== false;
}

if (!function_exists('mobileUrl')) {
    function mobileUrl($path, $query = array(), $full = true)
    {
        $path = ltrim($path, '/');
        list($module, $controller, $action) = explode('/', $path);
        $url = '/' . $module . '.php/' . $controller . '/' . $action;
        if (!empty($query)) {
            foreach ($query as $k => $v) {
                $url .= "/{$k}/{$v}";
            }
        }

        if ($full) {
            $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $url;
        }

        return $url;
    }
}

if (!function_exists('agentLoginUrl')) {
    function agentLoginUrl()
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/agent';
    }
}

if (!function_exists('mediaUrl')) {
    function mediaUrl($url = '', $full = false)
    {
        if($url) {
            if(strpos($url, '://') !== false) {
                return $url;
            }
            $url = '/' . ltrim($url, '/');

            if ($full) {
                $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $url;
            }
        }

        return $url;
    }
}

if (!function_exists('getNotifyUrl')) {
    function getNotifyUrl($name)
    {
       $notifyArr = [
           'alipay' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/pay.php/notify/alipay',
           'wxpay' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/pay.php/notify/wxpay'
       ];
       return $notifyArr[$name];
    }
}
