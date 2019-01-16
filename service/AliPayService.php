<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2018/12/29
 * Time: 5:25 PM
 */

namespace app\service;

use Yii;
use app\service\CommonService;
use app\library\alipay_sdk;

class AliPayService extends CommonService
{
    const SERVICE_ID = 100100;
    public $params = [];
    /**
     * 设置参数
     * @param $param
     */
    public function setParam($params)
    {
        $this->params = $params;
        return;
    }

    /**
     * 获取支付服务对象
     */
    public function handle($scene, $params, $oredr_id, $biz_order_id)
    {
        $this->setParam($params);
        $ret = $this->$scene($params, $oredr_id, $biz_order_id);
        return $ret;
    }

    /**
     * 网页支付
     * @param $params
     * @param $order_id
     * @param $biz_order_id
     * @return array
     */
    public function handleWeb($params, $order_id, $biz_order_id)
    {
        (new alipay_sdk\AopSdk())->init();
        $aop = new \AopClient;
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $params['app_id'];
        $aop->rsaPrivateKey =  $params['private_key'];
        //$aop->alipayrsaPublicKey = $params['public_key'];
        $aop->apiVersion ="1.0";
        $aop->postCharset = 'UTF-8';
        $aop->format= 'json';
        $aop->signType= 'RSA2';
        $request = new \AlipayTradePagePayRequest();
        $biz_content = [
            'body' => '',//详情
            'subject' => '',//描述
            'total_amount' => $params['price'],//支付金额0.00格式
            'out_trade_no' => $biz_order_id,//商户系统唯一订单号
        ];
        //$request->setNotifyUrl('');//同步回调地址
        $request->setReturnUrl('');//异步回调地址
        $request->setBizContent(json_encode($biz_content));
        $result = $aop->pageExecute($request,"post");
        if (!$result) {
            return ['code' => 100010, 'msg' => '支付错误', 'data' => []];
        }
        return ['code' => 0, 'msg' => 'access', 'data' => $result];
    }

    /**
     * 移动端网页支付
     * @param $params
     * @param $order_id
     * @param $biz_order_id
     * @return array
     */
    public function handleWap($params, $order_id, $biz_order_id)
    {
        (new alipay_sdk\AopSdk())->init();
        $aop = new \AopClient;
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $params['app_id'];
        $aop->rsaPrivateKey =  $params['private_key'];
        $aop->alipayrsaPublicKey = $params['public_key'];
        $aop->apiVersion ="1.0";
        $aop->postCharset = 'UTF-8';
        $aop->format= 'json';
        $aop->signType= 'RSA2';

    }

    /**
     * app支付
     * @param $params
     * @param $order_id
     * @param $biz_order_id
     * @return string
     */
    public function handleApp($params, $order_id, $biz_order_id)
    {
        (new alipay_sdk\AopSdk())->init();
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $params['app_id'];
        $aop->rsaPrivateKey = $params['private_Key'];//'请填写开发者私钥去头去尾去回车，一行字符串';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $params['public_Key'];//'请填写支付宝公钥，一行字符串';
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $biz_content = [
            'body' => 'test data',
            'subject' => 'pay test',
            'out_trade_no' => $biz_order_id,
            'timeout_express' => '30m',
            'total_amount' => '0.01', //支付金额
            'product_code' => 'QUICK_MSECURITY_PAY',
        ];
        $request->setNotifyUrl("");//商户外网可以访问的异步地址
        $request->setBizContent(json_encode($biz_content));
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        if (!$response) {
            return ['code' => 100010, 'msg' => '支付错误', 'data' => []];
        }
        //return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
        return ['code' => 0, 'msg' => 'access', 'data' => $response];
    }

}