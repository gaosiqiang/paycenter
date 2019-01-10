<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/5
 * Time: 3:41 PM
 */

namespace app\service;

use app\component\Tools;
use app\component\WechatPayTools;
use app\service\CommonService;

class WechatCallBackService extends CommonService
{

    /**
     * 服务入口函数
     */
    public function main($data, $params)
    {
        //获取回调数据
//        $data = $this->getCallBackData();
//        if (!$data) {
//            return ['code' => 100010, 'msg' => 'error', 'data' => ['call_back_data' => $data]];
//        }
        //分析验证回调数据
        $ret = $this->checkCallBackData($data, $params);
        if (!$ret) {
            return ['code' => 100010, 'msg' => 'error', 'data' => ['call_back_data' => $data]];
        }
        return ['code' => 0, 'msg' => 'access', 'data' => ['call_back_res' => $ret, 'call_back_data' => $data]];
    }

    /**
     * 获取回调数据
     * @return array
     */
    public function getCallBackData()
    {
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = $GLOBALS['HTTP_RAW_POST_DATA'];
        } else {
            $data = file_get_contents('php://input');
        }
        $data = Tools::xmlToArray($data);
        if (!$data) {
            return [];
        }
        return $data;
    }

    /**
     * 验证回调数据的
     * @param $data 回调数据
     * @param $sign_config 生成签名数据
     * @return int
     */
    public function checkCallBackData($data)
    {
        //验证签名是否一致
        if ($data['sign'] != WechatPayTools::MakeSign($data, false)) {
            return 0;
        }
        //验证$data["transaction_id"]微信的订单号
        if (!$this->Queryorder($data, $data["transaction_id"])) {
            return 0;
        }
        return 1;
    }

    //查询订单
    public function Queryorder($data)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $request_data['appid'] = $data['appid'];
        $request_data['mch_id'] = $data['mch_id'];
        $request_data['transaction_id'] = $data['transaction_id'];
        $request_data['out_trade_no'] = $data['out_trade_no'];
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