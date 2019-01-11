<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/8
 * Time: 1:49 PM
 */

namespace app\service;

use app\component\ServiceException;
use app\component\WechatPayTools;

class WechatRefundService
{
    public $scene = '';
    public function handle($params)
    {
        if (isset($params['transaction_id']) && $params['transaction_id']) {
            //微信订单号退款
            $this->scene = 'BytransactionId';
            $request_data['transaction_id'] = $params["transaction_id"];
        } elseif (isset($params['out_trade_no']) && $params['out_trade_no']) {
            //商户订单号退款
            $this->scene = 'BytoutTradeNo';
            $requset_data['out_trade_no'] = $params['out_trade_no'];
        } else {
            throw new ServiceException('参数错误，没有对应场景 by wechat', 100010);
        }
        $request_data['total_fee'] = $params["total_fee"];
        $request_data['refund_fee'] = $params["refund_fee"];
        $request_data['out_refund_no'] = "sdkphp".date("YmdHis");
        $request_data['op_user_id'] = $params['mch_id'];
        $request_data['appid'] = $params['appid'];//公众账号ID
        $request_data['mch_id'] = $params['mch_id'];//商户号
        $request_data['notify_url'] = 'http://pay.zhugexuetang.com/payment/callback/wechatrefund';//回调地址
        $scene = $this->scene;
        $ret = $this->$scene($request_data);
        return $ret;
    }

    /**
     * 微信订单号退款
     * @param $params
     * @return array
     */
    protected function BytransactionId($params)
    {
        return $this->refund($params);
    }

    /**
     * 商户订单号退款
     * @param $params
     * @return array
     */
    protected function BytoutTradeNo($params)
    {
        return $this->refund($params);
    }

    /**
     *
     * 申请退款，WxPayRefund中out_trade_no、transaction_id至少填一个且
     * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param WxPayConfigInterface $config  配置对象
     * @param WxPayRefund $inputObj
     * @param int $timeOut
     * @throws WxPayException
     * @return 成功时返回，其他抛异常
     */
    public function refund($params)
    {
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        //检测必填参数
        $request_data = $params;
        $request_data['nonce_str'] = WechatPayTools::getNonceStr();//设置随机字符//随机字符串
        $sign = WechatPayTools::getSign($request_data);//签名
        $time_out = 30;
        $sslCertPath = '';
        $sslKeyPath = '';
        $response = WechatPayTools::postXmlCurl(array_merge($request_data, ['sign' => $sign]), $url, true, $time_out, $sslCertPath = '', $sslKeyPath = '');
        $result = WechatPayTools::InitResults(array_merge($request_data, ['sign' => $sign]), $response, $sign);
        return $result;
    }

}