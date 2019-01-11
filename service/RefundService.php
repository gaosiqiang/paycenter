<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/7
 * Time: 10:18 AM
 */

namespace app\service;

use app\component\ServiceException;
use app\component\Tools;
use app\service\CommonService;

class RefundService extends CommonService
{

    public $service_id = 0;
    public $service = null;
    /**
     * 设置服务
     * @param $service_id
     * @throws ServiceException
     */
    public function setService($service_id)
    {
        if (!$service_id || !in_array($service_id, $this->config_params['pay_refund_service_set'])) {
            throw new ServiceException('参数错误', 100010);
        }
        $this->service_id = $service_id;
        $service = $this->config_params['pay_refund_service_dict'][$service_id];
        $this->service = new $service();
        return;
    }

    /**
     * 主函数
     * @param $service_id
     * @param $params
     * @return mixed
     * @throws ServiceException
     */
    public function main($service_id, $scene_id, $biz_order_id, $refund_params)
    {
        //设置服务
        $this->setService($service_id);
        //添加记录和创建订单
        $order_id = (new PayOrderService())->createOneOrder([
            'channel_id' => $this->service_id,
            'scene_id' => $scene_id,
            'biz_order_id' => $biz_order_id,
            'order_status' => 0,
            'handle_status' => 0,
            'type' => 20,
            'params' => is_string($refund_params) ? $refund_params : json_encode($refund_params),
            'create_time' => Tools::getTimeSecond(),
            'update_time' => 0,
        ]);
        (new PayEventService())->addOneEvent([
            'pay_order_id' => $order_id,
            'event_type' => 30,
            'event_data' => is_string($refund_params) ? $refund_params : json_encode($refund_params),
            'create_time' => Tools::getTimeSecond(),
        ]);
        //服务处理
        $refund_params = json_decode($refund_params, 1);
        $res = $this->service->handle($refund_params);
        if (!$res) {
            return ['code' => 100010, 'msg' => 'params error', 'data' => (object)[]];
        }
        return ['code' => 0, 'msg' => 'access', 'data' => ['refund_ret' => $res]];
    }

}