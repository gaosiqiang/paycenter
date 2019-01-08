<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/7
 * Time: 10:18 AM
 */

namespace app\service;

use app\component\ServiceException;
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
    public function main($service_id, $params)
    {
        //设置服务
        $this->setService($service_id);
        //服务处理
        $res = $this->service->haandle($params);
        if (!$res) {
            return ['code' => 100010, 'msg' => 'params error', 'data' => (object)[]];
        }
        return ['code' => 0, 'msg' => 'access', 'data' => ['refund_ret' => $res]];
    }

}