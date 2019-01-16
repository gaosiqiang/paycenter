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
        $aop->appId = '2015121100962151';
//        $aop->rsaPrivateKey = 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBANJOh5oVNIeJcHh/S3alPNqTsnAJTdF2zFcpHlnHrfW00nWRyaOjfRcKveCRts1moB20IeAcYGxhq5wagiikLqZJMjbONVqa+u+e1+6NRx/po2AK8X2V/efEKKSk6nU8DUAZqBU3d3JGR4Zw+gQOkvWippaVrNfOTBYHY2rlkkofAgMBAAECgYAOrYhtSpmV9BOzdT7xEutCXhiQFTYnLmFom+gQYA1WHr6pkfk+wIRUfV1vNPxLLaRzLkVG/PQF3HM7u+XlrD/NHt4xTEK8xz7oqu7y8By5ArOYn4rIXHDT3vptNSvl9zVwi/hUTThVWNaR8RBWeRcwxyX/stTZq1n7kt0JFkem4QJBAOuqzcQc3JJ3t/9erq1RC1YH+nMVkJvqBgpkIMsOXt8o5gOzL2wObHvLilPXDY9NBmIanb6pyBQMbAfCXnhfjrcCQQDkc5aSi4Fkbi2OtFwGCfE8mRV+mDWONzyEeA04YnXIfyysIImyE7HicYfxk9JC9Tw+76kYIOusKgIoVw+ZXzfZAkAE02SPRYAGx8jOw+OTzPsMcfFg9eoWJz6ka9R4E/1BWJcNMFgiQFFcX5ifiuHOM2eUDrN4OgXM00xLBGHm2R4VAkEAzk84rtU/oCQEDnkBFg8KhdA14iKxUuK9S2BjiAUbG1sGS9gCogg5QCeJPnhhjUiNBMVIrtqkGtHBKw8crkSYWQJBAN5REJYxbIY2+oiKHm3lcy4NIhZTGbGRtuHRGJIZaTdzbERWLW/fTpOhKhoSRja3LPrEqnu1Irk1z3G7KIM3aSA=';
        $aop->rsaPrivateKey = 'MIICXQIBAAKBgQDSToeaFTSHiXB4f0t2pTzak7JwCU3RdsxXKR5Zx631tNJ1kcmjo30XCr3gkbbNZqAdtCHgHGBsYaucGoIopC6mSTI2zjVamvrvntfujUcf6aNgCvF9lf3nxCikpOp1PA1AGagVN3dyRkeGcPoEDpL1oqaWlazXzkwWB2Nq5ZJKHwIDAQABAoGADq2IbUqZlfQTs3U+8RLrQl4YkBU2Jy5haJvoEGANVh6+qZH5PsCEVH1dbzT8Sy2kcy5FRvz0BdxzO7vl5aw/zR7eMUxCvMc+6Kru8vAcuQKzmJ+KyFxw0976bTUr5fc1cIv4VE04VVjWkfEQVnkXMMcl/7LU2atZ+5LdCRZHpuECQQDrqs3EHNySd7f/Xq6tUQtWB/pzFZCb6gYKZCDLDl7fKOYDsy9sDmx7y4pT1w2PTQZiGp2+qcgUDGwHwl54X463AkEA5HOWkouBZG4tjrRcBgnxPJkVfpg1jjc8hHgNOGJ1yH8srCCJshOx4nGH8ZPSQvU8Pu+pGCDrrCoCKFcPmV832QJABNNkj0WABsfIzsPjk8z7DHHxYPXqFic+pGvUeBP9QViXDTBYIkBRXF+Yn4rhzjNnlA6zeDoFzNNMSwRh5tkeFQJBAM5POK7VP6AkBA55ARYPCoXQNeIisVLivUtgY4gFGxtbBkvYAqIIOUAniT54YY1IjQTFSK7apBrRwSsPHK5EmFkCQQDeURCWMWyGNvqIih5t5XMuDSIWUxmxkbbh0RiSGWk3c2xEVi1v306ToSoaEkY2tyz6xKp7tSK5Nc9xuyiDN2kg';
        $aop->alipayrsaPublicKey = 'zrrydg5u81lqjqia2dtii3nomsdhxood';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayTradeFastpayRefundQueryRequest();
        $biz_content = [
            'trade_no' => '',//支付宝交易号
            'out_trade_no' => $order_id,//创建交易传入的商户订单号
            'out_request_no' => $params['out_request_no'],//本笔退款对应的退款请求号
        ];
        //$request->setBizContent(json_encode($biz_content, JSON_UNESCAPED_UNICODE));
        $out_request_no = $params['out_request_no'];
        $request->setBizContent("{" .
            "\"trade_no\":\"\"," .
            "\"out_trade_no\":\"$order_id\"," .
            "\"out_request_no\":\"$out_request_no\"," .
            "\"org_pid\":\"\"" .
            "  }");

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