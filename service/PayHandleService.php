<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:48 AM
 */

namespace app\service;

use app\component\ServiceException;
use app\service\CommonService;
use yii\base\ErrorException;

class PayHandleService extends CommonService
{
    public $channel_id = '';
    public $service = null;
    public $service_id = '';
    public $scene = '';

    /**
     * 设置
     * @param $channel
     */
    public function setPayChannel($channel_id)
    {
        if (!$channel_id || !in_array($channel_id, $this->config_params['pay_channel_id_set'])) {
            //抛出异常
            throw new ServiceException('params error', 100010);
        }
        $this->channel_id = $channel_id;
        return;
    }
    /**
     * 设置场景服务
     * @param $channel
     * @param $mode_code
     */
    public function setPayService($service_id)
    {
        if (!$service_id || !in_array($service_id, $this->config_params['pay_service_id_set']) || !isset($this->config_params['pay_channel_services_map'][$this->channel_id][$service_id])) {
            throw new ServiceException('params error', 100010);
        }
        $this->service_id = $service_id;
        $service = $this->config_params['pay_channel_services_dict'][$service_id];
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
        $this->scene = $this->config_params['pay_channel_scene_dict'][$scene_id];
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
    public function main($channel_id, $service_id, $scene_id, $brand_info)
    {
        if (!$channel_id || !$scene_id || !$brand_info) {
            return ['code' => 100010, 'msg' => 'params error'];
        }
        try {
            $this->setPayChannel($channel_id);
            $this->setPayService($service_id);
            $this->setPayScene($scene_id);
        } catch (ServiceException $e) {
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
        //TODO 业务
        $this->service->handle($this->scene, $brand_info);
    }

}