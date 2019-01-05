<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/5
 * Time: 3:41 PM
 */

namespace app\service;

use app\component\WeChatHttpCurl;
use app\component\WechatPayTools;
use app\component\WechatSign;
use app\service\CommonService;

class WechatCallBackService extends CommonService
{

    /**
     * 获取回调数据
     * @return array
     */
    public function getCallBackData()
    {
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            # 如果没有数据，直接返回失败
            return [];
        }
        $data = WechatPayTools::xmlToArray($GLOBALS['HTTP_RAW_POST_DATA']);
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
    public function checkCallBackData($data, $sign_config)
    {
        //验证签名是否一致
        if ($data['sign'] != WechatSign::MakeSign($sign_config, false)) {
            return 0;
        }
        $request_data = [];
        //验证$data["transaction_id"]微信的订单号
        if (!$this->Queryorder($request_data, $data["transaction_id"])) {
            return 0;
        }
        return 1;
    }

    //查询订单
    public function Queryorder($request_data, $transaction_id)
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $config['mch_id'] = $request_data['mch_id'];
        $response = WeChatHttpCurl::postXmlCurl($config, array_merge($request_data, ['transaction_id' => $transaction_id]), $url, false, 30);
        $response = WechatPayTools::xmlToArray($response);
        //验证签名
        $result = WxPayResultsService::Init($request_data, $response, $response['sign'], WechatSign::getSign($request_data));
        if (!$result) {
            return 0;
        }
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return 1;
        }
        return 0;
    }

}