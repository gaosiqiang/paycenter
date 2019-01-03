<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:48 AM
 */

namespace app\service;

use app\service\CommonService;

class PayHandleService extends CommonService
{

    /**
     *
     */
    public function PayBefore($channel_id)
    {
        if (!$channel_id || !in_array($channel_id, $this->config_params['pay_channel_ids'])) {
            return ['code' => 100010, 'msg' => 'error param', 'data' => (object)[]];
        }
        $serice = $this->config_params['pay_channel_services_map'][$channel_id];
        if (!$serice) {
            return ['code' => 100011, 'msg' => 'error param', 'data' => (object)[]];
        }
//        $ret = (new $serice())->

    }

    /**
     * 发起支付
     */
    public function createPay()
    {

    }

    /**
     * 发起退款
     */
    public function returnPay()
    {

    }

}