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
        if (!$ret) {
            return ['code' => 100012, 'msg' => '退款失败', 'data' => (object)[]];
        }
        return ['code' => 0, 'msg' => 'access', 'data' => (object)[]];
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
        $aop->appId = '2019010362727917';
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAxDwSWFrV8+375Vcoq2Jtk2wOFfjMQtybqX5cxI1bWMCI0lt6/eZ4t0aTkheCz67TBNScbYqxvGj/oaRub8hxua8JDrsNg/9RzRVA3fDGVXYbq6AS3QL4RQBEoKxZ+SWAA7d9498BNpuQFLCXnFkvrth2sI2w7moPd939fWhvh9oI/b3lKwIIkD/Sm+SZlcgX22l+lCnOsUOMTkB4fkb2HrirvwrhC65/pPPgmECkEVr6vFZQsDMDUWV3Arf3SD1UBrnqbzFOm/c8IyRobFnMkpJgOm11g1MKj4xIlTgAc7FZ43qt5ma/Y9DaBqzFW5f3wrvCH/+9melPx/BUV9+HBwIDAQABAoIBAGwPTGbdNn5J6fGDyoB+BistUKBmzkxvYUS3sewGj2vTMkTsXVEdrhH2ymKjkcQ92DQLxExKGM/Q3hwsCSiDL6T5rzouZSXv3iLZ9kuBQCZlkJ00285ayU5t9FyqLC2XqePiEm/+KJPinDfYetR9BFX7G3jjva+8NeJjGykI7onW3VvcTuyg9uxUcf33pGoCtMy++wky9MMz0T5mMLgZ74a22k4rk3l/uOpokc0ZeV1iLVo29ypKXEPj64y+O0NViy8xMFWSPuNwWaOGRvwP2jjuoVanhY285zEJdC0Lwhptn7va4bxjhGznU/ww5iDfxrZIonCdgJpEp9qMgcHkfPkCgYEA/hHpXtPFtmDkreDvurBFbNB9rzkt42MKEz+/OQStL5t5IFFr4UqsBV1KzWFJwZOQ7m74nMPjn6XuYBPoFiS40tiuZqyqqYcZWbh41noMpUIjGiLmb8xG2QTedgJg4P2zj76cP9qA+ITHfuNfgdfaBDWkyFuGkRPw0h/MagBrQf0CgYEAxbmwJLK6td5v4C6iUB6ZQWNEHMJb2Ijrco6yZgMnlfIKQfBJGv/055rr366m/lzvYFZF9ww6O3uf89m1z3411x0A6cRlAPzz3euVEqDs0FAjRXVj/ibEp8N+cz1QReKuWPhAxV+g2ZLyDe2BRYRA35FvhtXqtk2uazu71hf+SlMCgYEAzqBcji1mpytn7213Kfo/i/6HqOC0zC/4uqzmZIXEH2qu+LSfKutiuT4lHqDXriHIBdGkSUIatfTpx6OI5bFZyshEFeapKRRhbpFTXQKHlEMUbyYhCJ02pTqLfafziCdsSCQ5yMh4iSTbZfue6edVPIUwDW46I3T3LryDr1XF5TUCgYEAuy8WVTeq0KAbM5WSbdz541AxXDSXtL+yfkofE7oq0KZKFbB/1QK5fEVKxgDW8PlUWNRz+fRmcIeRns3dc+ic2eAITEZ0BGJ0EASFpRQZ/P/Q1GHU3vcet1+4pypFg1OdEHc9Al2Mrk2Rv6O3/PK+Y6iQd4quYBXcaIItSfp+7ycCgYATlm0BcPD9fWSB9zZGiBfA11/l/YY2EY71OUDObphTRm70UL5rVYN9pjqCE3US2PbaUeXu4trYUAQ/j0MP0i61iECbA55wJed3YYxgTLxcuPm7BkrpmYu6qxPWTrUqJw+HDRV/l/PQhbkVC1YNn1yp+dQrGKz+yDec9Se5NieNpA==';
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAihcTMZBlNzvS1j7l8O6xN+ukhZPrm/dGl6SXiaOK0fAbXU3/h3SNqjk0CqR7Vtxq5K5pCJgNdBo1O+KKTSNeBT3o3X3rZriVB7x09rBDqdaVT2ZyEg/lMmv+MZUIcwgPYv6dlIXZDRpQ2R7hZb90frAplEfZinhCr5AEJni2Jg9UeZL6sr6iYbPnzoWqNiy7lLtNMqDXH7BN+mF/cF/bYNQmgFg+9X+HjVf10QMHkHymi9P4pMZEJHli/X+TtJbdVtwevjGOZUJ8+aS9tRE7Q1yuezz9QXfsoOIvdgJAWN65IEKPo6KCNJAN6RWAjk6XnTMwoma40sbE0cDE7+yY3wIDAQAB';
        //$aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayTradeFastpayRefundQueryRequest();
        $biz_content = [
            //'trade_no' => '',//支付宝交易号
            'out_trade_no' => '1546594821',//创建交易传入的商户订单号
            'out_request_no' => 'a7bbabd609be43f385aae03d3df2e265',//本笔退款对应的退款请求号
        ];
        $request->setBizContent(json_encode($biz_content, JSON_UNESCAPED_UNICODE));

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
        $url = "https://api.mch.weixin.qq.com/pay/refundquery";
        $time_out = 30;
        $request_data['appid'] = 'wxbb93dfb3536660f6';
        $request_data['mch_id'] = '1446999202';
        $request_data['nonce_str'] = WechatPayTools::getNonceStr();
        $request_data['out_trade_no'] = 'aaaaaa';
        $request_params = $request_data;
        $request_params['sign'] = WechatPayTools::getSign($request_data);
        $request_params['sign_type'] = WechatPayTools::GetSignType();
        $response = WechatPayTools::postXmlCurl($request_params, $url, false, $time_out);
        //$response = Tools::xmlToArray($response);
        //验证签名
        $result = WechatPayTools::InitResults($request_params, $response, $request_params['sign']);

        if (!$result) {
            return [];
        }
        if(array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return $result;
        }
        return [];
    }

}