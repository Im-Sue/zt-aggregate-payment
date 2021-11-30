<?php

namespace app\pay\controller;

use think\facade\Config;
use AopSDK\AopClient;
use AopSDK\request\AlipayTradePayRequest;

class Alipay
{
    public function index()
    {

        return view();
    }
    /**
     * 当面付收款
     */
    public function codepay()
    {
        $subject = input('subject', '服务费', 'trim');
        $code = input('code', '', 'trim');
        $money = input('money', 0, 'floatval');

        // 商户资料
        $mch_id = '2088502994920532';
        $mch_token = '202011BB6778d8987fde445ba96b9d91b54c5C53';

        $aop = new AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = Config::get('alipay.appId');
        $aop->rsaPrivateKey = Config::get('alipay.privateKey');
        $aop->alipayrsaPublicKey = Config::get('alipay.publicKey');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';

        $item = [
            'out_trade_no' => uniqid(),
            'auth_code' => $code,
            'subject' => $subject,
            'total_amount' => $money,
            'scene' => 'bar_code',
            'query_options' => ['fund_bill_list', 'voucher_detail_list', 'discount_goods_detail'],
            'seller_id' => $mch_id
        ];
        $request = new AlipayTradePayRequest();
        $request->setBizContent(json_encode($item));
        $result = $aop->execute($request, "", $mch_token);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {          //交易成功
            $result = json_decode(json_encode($result), true)['alipay_trade_pay_response'];

            $result['status'] = 1;
            print_r($result);
        } else if (!empty($resultCode) && $resultCode == 10003) {   //需要用户输入支付密码
            echo '需要输入密码';
        } else {//交易失败
            echo $result->$responseNode->sub_msg;
        }
    }

    public function auth_success() {
        return view('auth_success');
    }
}
