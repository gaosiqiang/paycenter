<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:47 AM
 */

namespace app\service;

use app\component\WechatSign;
use app\service\CommonService;
use app\component\WechatJsApiPayTool;
use app\component\WeChatHttpCurl;
use app\component\Tools;
use app\service\WechatAuth;

class WechatPayService
{

    public $service = null;

    public function setPayMode($channel, $mode_code)
    {

    }

    public function setParams($params)
    {

    }

    public function getJsApiData($code)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $config = ['mch_id' => '1900009851'];
        $data = [
            'openid' => (new WechatAuth())->GetOpenid($code, 'wx426b3015555a46be', '1900009851'),
            'appid' => 'wx426b3015555a46be',
            'body' => 'test',//商品描述
            'attach' => 'test',//附加数据
            'out_trade_no' => "sdkphp".date("YmdHis"),//商户内部订单号
            'total_fee' => '1',//订单金额只能是整数
            'time_start' => date("YmdHis"),//设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
            'time_expire' => date("YmdHis", time() + 600000),//订单失效时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
            'goods_tag' => 'xx',//商品标签
            'notify_url' => 'http://www.baidu.com',//微信支付回调地址
            'trade_type' => 'JSAPI',//微信支付方式，设置取值如下：JSAPI，NATIVE，APP，详细说明见参数规定
            'mch_id' => '1900009851',//商户id
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//支付ip
            'nonce_str' => WechatSign::getNonceStr(),//设置随机字符
        ];
        $data['sign'] = WechatSign::getSign($data);//签名
        $data['sign_type'] = WechatSign::GetSignType();//签名类型

        $time_out = 30;
        $ret = WeChatHttpCurl::postXmlCurl($config, $data, $url, false, $time_out);
        print_r($ret);die();
        if (!$ret) {
            return ['code' => 100010, 'msg' => 'error params', 'data' => (object)[]];
        }
        return ['code' => 0, 'msg' => 'access', 'data' => ['js_api_parameters' => $jsApiParameters, 'edit_address' => $editAddress]];
    }

}