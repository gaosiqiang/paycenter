<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/8
 * Time: 5:50 PM
 */

namespace app\service;

use app\service\CommonService;

/**
 * 退款回调度类
 * Class RefundCallBackService
 * @package app\service
 */
class RefundCallBackService extends CommonService
{
    public $service = null;
    public $service_id = 0;

    /**
     * 设置服务
     * @param $service_id
     * @throws \Exception
     */
    public function setService($service_id)
    {
        if (!$service_id || !in_array($service_id, $this->config_params['pay_service_id_set'])) {
            throw new ServiceException('服务id不存在', 100010);
        }
        $service = $this->config_params['pay_refund_call_back_service_set'][$service_id];
        $this->service = new $service();
        $this->service_id = $service_id;
        return;
    }

    /**
     * 入口函数
     * @param $service_id
     */
    public function main($service_id)
    {
        $this->setService($service_id);
        //获取回调数据
        $call_back_data = $this->service->getCallBackData();
        //验证回调数据
        $check_ret = $this->service->checkCallBackData($call_back_data);
        //更新相关数据
        //TOOD
        //返回结果数据
        return [];
    }

}