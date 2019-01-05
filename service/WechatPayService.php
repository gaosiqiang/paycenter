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
use app\component\WechatPayTools;
use app\service\WechatAuth;

class WechatPayService
{
    /**
     * 获取支付接口相应数据
     * @throws \Exception
     */
    public function getPayApiData($request_data)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $appid = 'wx426b3015555a46be';
        $mch_id = '1900009851';
        $openid = '';
        $time_out = 30;
        /*
        $request_data = [
            'openid' => $openid,
            'appid' => $appid,
            'body' => 'test',//商品描述
            'attach' => 'test',//附加数据
            'out_trade_no' => "sdkphp".date("YmdHis"),//商户内部订单号
            'total_fee' => '1',//订单金额只能是整数
            'time_start' => date("YmdHis"),//设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
            'time_expire' => date("YmdHis", time() + 600000),//订单失效时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
            'goods_tag' => 'xx',//商品标签
            'notify_url' => 'http://www.baidu.com',//微信支付回调地址
            'trade_type' => 'JSAPI',//微信支付方式，设置取值如下：JSAPI，NATIVE，APP，详细说明见参数规定
            'mch_id' => $mch_id,//商户id
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//支付ip
            'nonce_str' => WechatSign::getNonceStr(),//设置随机字符
            'product_id' => '1',//商品id，NATIVE场景使用
        ];
        */
        $sign = WechatSign::getSign($request_data);//签名
        $sign_type = WechatSign::GetSignType();//签名类型
        $response = WeChatHttpCurl::postXmlCurl(['mch_id' => $mch_id], array_merge($request_data, ['sign' => $sign, 'sign_type' => $sign_type]), $url, false, $time_out);
        $result = WxPayResultsService::Init(array_merge($request_data, ['sign' => $sign, 'sign_type' => $sign_type]), $response, $sign);
        return $result;
    }

    /**
     * 处理扫码支付
     * @param $requst_data
     * @param $result
     * @return mixed
     */
    public function handleNative($requst_data, $result)
    {
        if ($requst_data['trade_type'] === 'NATIVE') {
            $result['code_url'] = 'http://qrcode.zyuwen.cn/img/qrcode?code=' . urlencode($result['code_url']);
        }
        return $result;
    }

    /**
     * 处理jsapi支付
     * @param $requst_data
     * @param $result
     * @return array
     */
    public function handleJsapi($requst_data, $result)
    {
        $access_token = '';
        if ($requst_data['trade_type'] === 'JSAPI') {
            $jsApiParameters = WechatPayTools::GetJsApiParameters($result, $requst_data);
            //获取共享收货地址js函数参数
            $editAddress = WechatPayTools::GetEditAddressParameters(['appid' => $requst_data['appid'], 'access_token' => $access_token]);
        }
        return ['jsApiParameters' => $jsApiParameters, 'editAddress' => $editAddress];
    }

    /**
     * 处理app支付参数
     * @param $requst_data
     * @param $result
     * @return array
     */
    public function handleApp($requst_data, $result)
    {
        if ($requst_data['trade_type'] === 'APP') {
            return $result;
        }
        return [];
    }

    /**
     * 处理支付数据
     */
    public function handle($call_back, $pay_info)
    {
        $request_data = [
            'appid' => 'wx426b3015555a46be',
            'body' => 'test',//商品描述
            'attach' => 'test',//附加数据
            'out_trade_no' => "sdkphp".date("YmdHis"),//商户内部订单号
            'total_fee' => '1',//订单金额只能是整数
            'time_start' => date("YmdHis"),//设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
            'time_expire' => date("YmdHis", time() + 600000),//订单失效时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
            'goods_tag' => 'xx',//商品标签
            'notify_url' => 'http://www.baidu.com',//微信支付回调地址
            'trade_type' => 'NATIVE',//微信支付方式，设置取值如下：JSAPI，NATIVE，APP，详细说明见参数规定
            'mch_id' => '1900009851',//商户id
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//支付ip
            'nonce_str' => WechatSign::getNonceStr(),//设置随机字符
            'product_id' => '1',//商品id，NATIVE场景使用
        ];
        $result = $this->getPayApiData($request_data);
        return $this->$call_back($request_data, $result);
    }

}