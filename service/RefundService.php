<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/7
 * Time: 10:18 AM
 */

namespace app\service;

use app\component\ServiceException;
use app\component\Tools;
use app\component\WechatPayTools;
use app\library\alipay_sdk\AopSdk;
use app\service\CommonService;

class RefundService extends CommonService
{

    public $refund_service_set = [
        100300,
        200300,
    ];
    public $refund_service_dict = [
        100300 => 'getAliRefundInfo',
        200300 => 'getWechatRefundInfo',
    ];

    public $service_id = 0;
    public $service = null;

    /**
     * 设置服务
     * @param $service_id
     * @throws ServiceException
     */
    public function setService($service_id)
    {
        if (!$service_id || !in_array($service_id, $this->config_params['pay_refund_service_set'])) {
            throw new ServiceException('参数错误', 100010);
        }
        $this->service_id = $service_id;
        $service = $this->config_params['pay_refund_service_dict'][$service_id];
        $this->service = new $service();
        return;
    }

    /**
     * 主函数
     * @param $service_id
     * @param $params
     * @return mixed
     * @throws ServiceException
     */
    public function main($service_id, $scene_id, $biz_order_id, $refund_params)
    {
        //设置服务
        $this->setService($service_id);
        //添加记录和创建订单
        $order_id = (new PayOrderService())->createOneOrder([
            'channel_id' => $this->service_id,
            'scene_id' => $scene_id,
            'biz_order_id' => $biz_order_id,
            'order_status' => 0,
            'handle_status' => 0,
            'type' => 20,
            'params' => is_string($refund_params) ? $refund_params : json_encode($refund_params),
            'create_time' => Tools::getTimeSecond(),
            'update_time' => 0,
        ]);
        (new PayEventService())->addOneEvent([
            'pay_order_id' => $order_id,
            'event_type' => 30,
            'event_data' => is_string($refund_params) ? $refund_params : json_encode($refund_params),
            'create_time' => Tools::getTimeSecond(),
        ]);
        //服务处理
        $refund_params = json_decode($refund_params, 1);
        $res = $this->service->handle($refund_params);
        if (!$res) {
            return ['code' => 100010, 'msg' => 'params error', 'data' => (object)[]];
        }
        return ['code' => 0, 'msg' => 'access', 'data' => ['refund_ret' => $res]];
    }

    /**
     * 获取退款详情
     * @param $service_id
     * @param $order_id
     * @param $params
     * @return array
     */
    public function getRefundInfo($service_id, $order_id, $params)
    {
        if (!$service_id || !$order_id || !$params) {
            return ['code' => 100010, 'msg' => 'params error', 'data' => (object)[]];
        }
        if (!in_array($service_id, $this->refund_service_set)) {
            return ['code' => 100011, 'msg' => 'params error', 'data' => (object)[]];
        }
        $function_id = $this->refund_service_dict[$service_id];
        $ret = $this->$function_id($order_id, json_decode($params, 1));
        return $ret;
    }

    /**
     * 支付宝获取退款详情
     * @param $order_id
     * @param $params
     * @return array
     */
    public function getAliRefundInfo($order_id, $params)
    {
        (new AopSdk())->init();
        $aop = new \AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $params['app_id'];
        $aop->rsaPrivateKey = $params['private_key'];//私钥
        $aop->alipayrsaPublicKey= $params['public_key'];//公钥，一行字符串
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        //var_dump($aop);die();
        $request = new \AlipayTradeFastpayRefundQueryRequest();
        $biz_content = [
            'trade_no' => '',//支付宝交易号
            'out_trade_no' => $order_id,//创建交易传入的商户订单号
            'out_request_no' => $params['out_request_no'],//本笔退款对应的退款请求号
            'org_pid' => '', //该参数指定需要查询的交易所属收单机构的pid
        ];

        $request->setBizContent(json_encode($biz_content));
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return $result;
        } else {
            return [];
        }
    }

    /**
     * 微信获取退款详情
     * @param $order_id
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getWechatRefundInfo($order_id, $params)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $request_data['appid'] = $params['appid'];
        $request_data['mch_id'] = $params['mch_id'];
        //$request_data['transaction_id'] = $params['transaction_id'];
        $request_data['out_trade_no'] = $order_id;
        $request_data['nonce_str'] = WechatPayTools::getNonceStr();
        $sign = WechatPayTools::getSign($request_data);
        $sign_type = WechatPayTools::GetSignType();//签名类型
        $response = WechatPayTools::postXmlCurl(array_merge($request_data, ['sign' => $sign, 'sign_type' => $sign_type]), $url,false, 30);
        //$response = Tools::xmlToArray($response);
        //验证签名
        $result = WechatPayTools::InitResults(array_merge($request_data, ['sign' => $sign, 'sign_type' => $sign_type]), $response, $sign);
        if (!$result) {
            return [];
        }
        if(array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return $result;
        }
        return [];
    }

}