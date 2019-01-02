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

    /**
     * 设置参数
     * @param $param
     */
    public function setParam($param)
    {
        return;
    }

    /**
     * 获取支付服务对象
     */
    public function getPayMentHandle()
    {
        return [];
    }

}