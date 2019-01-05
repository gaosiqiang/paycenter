<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2018/12/29
 * Time: 6:21 PM
 */

namespace app\service;


use app\component\Tools;

class PayCallBackService
{
    public $data = [];
    /**
     * 处理回调
     * @param $channel_id
     */
    public function callBack($channel_id)
    {
        //获取回调数据
        $data = $this->getCallBackData($channel_id);
        //分析验证回调数据
        $ret = $this->HandleCallBack($data);
        //回调注册服务对象回调地址
        $call_back_res = Tools::http_get($ret['url']);
        if (!$call_back_res['res']) {
            return ['code' => 100010, 'msg' => 'call back errors'];
        }
        return ['code' => 0, 'msg' => 'access'];
    }

    /**
     * 接收回调数据
     * @param $channel_id
     * @return array
     */
    public function getCallBackData($channel_id)
    {
        
        return [];
    }

    /**
     * 处理分析验证回调数据
     * @param $data
     * @return array
     */
    public function andleCallBack($data)
    {
        return [];
    }


}