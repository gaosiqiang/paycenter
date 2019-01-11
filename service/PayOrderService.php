<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/10
 * Time: 11:35 AM
 */

namespace app\service;

use app\service\CommonService;
use app\dao\PayOrderDao;

class PayOrderService extends CommonService
{

    /**
     * 更新支付订单回调处理结果
     * @param $order_id
     * @param $handle_res
     * @return int
     * @throws \yii\db\Exception
     */
    public function callBackOrder($order_id, $handle_res)
    {
        if ($handle_res === 0) {
            //处理失败
            $handle_status = 20;
        } elseif ($handle_res === 1) {
            //处理成功
            $handle_status = 10;
        }
        $order_status = 10;
        //获取当前支付订单是否已经支付完成&支付状态
        $order_info = $this->getOrderById($order_id);
        //如果需要修改的状态与目前数据状态一致则不更新数据库
        if ($order_info && $order_info['order_status'] == $order_status && $order_info['handle_status'] == $handle_status) {
            return 1;
        }
        $res = PayOrderDao::updatOrderPayStatusById($order_id, $order_status, $handle_status);
        if (!$res) {
            return 0;
        }
        return 1;
    }

    /**
     * 获取订单数据
     * @param $order_id
     * @return array|false
     * @throws \yii\db\Exception
     */
    public function getOrderById($order_id)
    {
        return PayOrderDao::getOrderById($order_id);
    }

    /**
     * 创建订单
     * @param $insert_data
     * @return int|string
     * @throws \yii\db\Exception
     */
    public function createOneOrder($insert_data)
    {
        return PayOrderDao::createOrder($insert_data);
    }

}