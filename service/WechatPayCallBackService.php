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

class WechatPayCallBackService extends CommonService
{

    /**
     * 服务入口函数
     */
    public function main($data, $key)
    {
        //获取回调数据
        //分析验证回调数据
        $ret = $this->checkCallBackData($data, $key);
        if (!$ret) {
            return ['code' => 100010, 'msg' => 'error', 'data' => ['call_back_res' => (object)[], 'call_back_data' => $data]];
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
    public function checkCallBackData($data, $key)
    {
        //验证签名是否一致
        if ($data['sign'] != WechatPayTools::MakeSign($data, false, $key)) {
            return 0;
        }
        //验证$data["transaction_id"]微信的订单号
        if (!$this->Queryorder($data, $key)) {
            return 0;
        }
        return 1;
    }

    /**
     * 查询支付订单状态
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function Queryorder($data, $key)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $request_data['appid'] = $data['appid'];
        $request_data['mch_id'] = $data['mch_id'];
        $request_data['transaction_id'] = $data['transaction_id'];
        $request_data['out_trade_no'] = $data['out_trade_no'];
        $request_data['nonce_str'] = WechatPayTools::getNonceStr();
        $sign_type = WechatPayTools::GetSignType();//签名类型
        $request_data['sign_type'] = $sign_type;
        $sign = WechatPayTools::getSign($request_data, $key);
        $response = WechatPayTools::postXmlCurl(array_merge($request_data, ['sign' => $sign, 'sign_type' => $sign_type]), $url,false, 30);
        //$response = Tools::xmlToArray($response);
        //验证签名
        $result = WechatPayTools::InitResults(array_merge($request_data, ['sign' => $sign, 'sign_type' => $sign_type]), $response, $sign, $key);
        if (!$result) {
            return [];
        }
        if(array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return $result;
        }
        return [];
    }

    /**
     * 处理返回数据格式
     * @param $return_status
     * @param $e
     * @return string
     */
    public function returnData($return_status, $e = null, $ext_data = [])
    {
        if (!$return_status) {
            return Tools::arrayToXml(['return_code' => 'FAIL', 'return_msg' => $e->getMessage()]);

        }
        return Tools::arrayToXml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']);
    }

}