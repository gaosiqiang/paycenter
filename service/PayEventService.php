<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/10
 * Time: 1:47 PM
 */

namespace app\service;

use app\dao\PayEventDao;
use app\service\CommonService;

class PayEventService extends CommonService
{
    /**
     * 获取支付订单id对应记录数据
     * @param $pay_order_id
     * @return array|false
     * @throws \yii\db\Exception
     */
    public function getEventByPayOrderId($pay_order_id, $event_type)
    {
        return PayEventDao::getEventByPayOrderId($pay_order_id, $event_type);
    }

    /**
     * 添加一条记录
     * @param $data
     * @return int|string
     * @throws \yii\db\Exception
     */
    public function addOneEvent($data)
    {
        return PayEventDao::addEvent($data);
    }

}