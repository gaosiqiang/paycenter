<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:47 AM
 */

namespace app\service;

use app\service\CommonService;
use app\component\WechatPayTools;
use app\service\WechatAuth;

class WechatPayService extends CommonService
{
    /**
     * 获取支付接口相应数据
     * @throws \Exception
     */
    public function getPayApiData($request_data, $order_id)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $time_out = 30;
        /*
        $request_data = [
            'openid' => $openid,
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
            'mch_id' => $mch_id,//商户id
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//支付ip
            'nonce_str' => WechatSign::getNonceStr(),//设置随机字符
            'product_id' => '1',//商品id，NATIVE场景使用
        ];
        */
        $request_data['notify_url'] = 'http://pay.zhugexuetang.com/payment/callback/wechat';
        $request_data['attach'] = (string)$order_id;
        $sign = WechatPayTools::getSign($request_data);//签名
        $sign_type = WechatPayTools::GetSignType();//签名类型
        $request_params = array_merge($request_data, ['sign' => $sign, 'sign_type' => $sign_type]);
        $response = WechatPayTools::postXmlCurl($request_params, $url, false, $time_out);
        $result = WechatPayTools::InitResults(array_merge($request_data, ['sign' => $sign, 'sign_type' => $sign_type]), $response, $sign);
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
        $data = [];
        if ($result['return_code'] == 'SUCCESS' && $requst_data['trade_type'] === 'NATIVE') {
            $data['code'] = 0;
            $data['msg'] = $result['return_msg'];
            $result['code_url'] = 'http://qrcode.zyuwen.cn/img/qrcode?code=' . urlencode($result['code_url']);
            $data['data'] = $result;
        } else {
            $data['code'] = 100010;
            $data['msg'] = $result['return_msg'];
            $data['data'] = (object)[];
        }
        return $data;
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
    public function handle($call_back, $pay_params, $order_id)
    {
        $request_data = $this->getBaseParams();
        $this->setParams($call_back, $pay_params,$request_data);
        $result = $this->getPayApiData($request_data, $order_id);
        return $this->$call_back($request_data, $result);
    }

    /**
     * 设置参数
     */
    public function setParams($call_back, $pay_info, &$request_data)
    {
        $request_data['appid'] = (string)$pay_info['appid'];
        $request_data['mch_id'] = (string)$pay_info['mch_id'];
        $request_data['total_fee'] = (string)$pay_info['total_fee'];
        $request_data['body'] = (string)$pay_info['body'];
        //$request_data['attach'] = (string)$pay_info['attach'];
        $request_data['goods_tag'] = (string)$pay_info['goods_tag'];
        $request_data['notify_url'] = (string)$pay_info['notify_url'];
        switch ($call_back) {
            case 'handleNative':
                $request_data['trade_type'] = (string)'NATIVE';
                $request_data['product_id'] = (string)$pay_info['product_id'];
                break;
            case 'handleJsapi':
                $request_data['trade_type'] = (string)'JSAPI';
                $request_data['openid'] = (string)$pay_info['openid'];
                break;
            case 'handleApp':
                break;
        }
        return;
    }

    /**
     * 获取基础参数配置函数
     * @return array
     */
    public function getBaseParams()
    {
        return [
//            'openid' => '',
//            'appid' => 'wx426b3015555a46be',
//            'body' => 'test',//商品描述
//            'attach' => 'test',//附加数据
                'out_trade_no' => "sdkphp".date("YmdHis"),//商户内部订单号
//            'total_fee' => '1',//订单金额只能是整数
                'time_start' => date("YmdHis"),//设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
                'time_expire' => date("YmdHis", time() + 600000),//订单失效时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
//            'goods_tag' => 'xx',//商品标签
//            'notify_url' => 'http://www.baidu.com',//微信支付回调地址
//            'trade_type' => 'NATIVE',//微信支付方式，设置取值如下：JSAPI，NATIVE，APP，详细说明见参数规定
//            'mch_id' => '1900009851',//商户id
                'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//支付ip
                'nonce_str' => WechatPayTools::getNonceStr(),//设置随机字符
//            'product_id' => '1',//商品id，NATIVE场景使用
        ];
    }



}