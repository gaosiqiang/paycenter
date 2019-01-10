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
        $res = PayOrderDao::updatOrderPayStatusById($order_id, $order_status, $handle_status);
        if (!$res) {
            return 0;
        }
        return 1;
    }

}