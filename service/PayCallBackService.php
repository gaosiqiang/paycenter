<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2018/12/29
 * Time: 6:21 PM
 */

namespace app\service;

use app\component\ServiceException;
use app\service\CommonService;
use app\component\Tools;

class PayCallBackService extends CommonService
{
    public $data = [];
    public $service = null;
    public $service_id = 0;

    /**
     * 设置服务
     * @param $service_id
     * @throws \Exception
     */
    public function setService($service_id)
    {
        $this->service_id = $service_id;
        if (!$service_id || !in_array($service_id, $this->config_params['pay_service_id_set'])) {
            throw new ServiceException('服务id不存在', 100010);
        }
        $service = $this->config_params['pay_call_back_serbice_dict'][$service_id];
        $this->service = new $service();
        return;
    }

    /**
     * 处理回调
     * @param $channel_id
     */
    public function mian($service_id, $requst_data)
    {
        try {
            //设置服务
            $this->setService($service_id);
            //回调设置服务
            $requst_data = json_decode($requst_data, 1);
            $ret = $this->service->main($requst_data);
            if (!$ret) {
                throw new ServiceException('支付回调失败', 100010);
            }
            //回调注册服务对象回调地址
            $call_back_res = Tools::http_get($ret['url']);
            if (!$call_back_res['res']) {
                throw new ServiceException('处理回调失败', 100011);
            }
        } catch (ServiceException $e) {
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
        return ['code' => 0, 'msg' => 'access'];


    }


}