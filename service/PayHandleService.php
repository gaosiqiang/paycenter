<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:48 AM
 */

namespace app\service;

use app\component\ServiceException;
use app\component\Tools;
use app\service\CommonService;
use yii\base\ErrorException;
use app\dao\PayOrderDao;
use app\dao\PayEventDao;

class PayHandleService extends CommonService
{
    public $channel_id = '';
    public $service = null;
    public $service_id = '';
    public $scene = '';
    public $scene_id = '';

    /**
     * 设置场景服务
     * @param $channel
     * @param $mode_code
     */
    public function setPayService($service_id)
    {
        if (!$service_id || !in_array($service_id, $this->config_params['pay_service_id_set'])) {
            throw new ServiceException('params error', 100010);
        }
        $this->service_id = $service_id;
        $service = $this->config_params['pay_services_dict'][$service_id];
        $this->service = (new $service());
        return;
    }

    /**
     * 设置场景
     * @param $channel_id
     * @param $scene_id
     * @return array
     */
    public function setPayScene($scene_id)
    {
        if (!$scene_id || !in_array($scene_id, $this->config_params['pay_scene_id_set'])) {
            throw new ServiceException('params error', 100010);
        }
        $this->scene = $this->config_params['pay_scene_dict'][$scene_id];
        $this->scene_id = $scene_id;
        return;
    }

    /**
     * 主流程函数
     * @param $channel_id
     * @param $service_id
     * @param $scene_id
     * @param $brand_info
     * @return array
     */
    public function main($channel_id, $scene_id, $pay_params, $biz_order_id)
    {
        if (!$channel_id || !$scene_id) {
            return ['code' => 100010, 'msg' => 'params error'];
        }
        try {
            $this->setPayService($channel_id);
            $this->setPayScene($scene_id);
            $pay_params = json_decode($pay_params, 1);
            $order_id = $this->createOrderToDB($biz_order_id, $pay_params);
            if (!$order_id) {
                throw new ServiceException('创建支付订单失败', 100010);
            }
            $this->addEvent($order_id);
        } catch (ServiceException $e) {
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
        $ret = $this->service->handle($this->scene, $pay_params, $order_id);
        if ($ret['code'] != 0) {
            return ['code' => $ret['code'], 'msg' => '支付失败!:'.$ret['msg'], 'data' => ['action_pay_data' => $ret['data']]];
        }
        return ['code' => $ret['code'], 'msg' => $ret['msg'], 'data' => ['action_pay_data' => $ret['data']]];
    }

    /**
     * 保存用户支付请求数据 to mysql
     * @param $biz_order_id
     * @param $pay_params
     */
    public function createOrderToDB($biz_order_id, $pay_params)
    {
        $insert_data = [
            'channel_id' => $this->service_id,
            'scene_id' => $this->scene_id,
            'biz_order_id' => $biz_order_id,
            'order_status' => 0,
            'handle_status' => 0,
            'type' => 10,
            'params' => is_string($pay_params) ? $pay_params : json_encode($pay_params),
            'create_time' => Tools::getTimeSecond(),
            'update_time' => 0,
        ];
        return PayOrderDao::createOrder($insert_data);
    }

    /**
     * 增加记录
     * @param $order_id
     * @return int
     */
    public function addEvent($order_id)
    {
        $insert_data = [
            'pay_order_id' => $order_id,
            'event_type' => 10,
            'event_data' => '',
            'create_time' => Tools::getTimeSecond(),
        ];
        return PayEventDao::addEvent($insert_data);
    }

}