<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/4
 * Time: 1:43 PM
 */

namespace app\service;


use app\component\WechatPayTools;
use app\component\WechatSign;

class WxPayResultsService
{
    public static function Init($data, $response, $sign)
    {
        $response = WechatPayTools::xmlToArray($response);
        //失败则直接返回失败
        if($response['return_code'] != 'SUCCESS') {
            foreach ($response as $key => $value) {
                #除了return_code和return_msg之外其他的参数存在，则报错
                if($key != "return_code" && $key != "return_msg"){
                    throw new \Exception("输入数据存在异常！");
                    return [];
                }
            }
        }
        WechatSign::CheckSign($data, $sign);
        return $response;
    }
}