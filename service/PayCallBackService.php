<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2018/12/29
 * Time: 6:21 PM
 */

namespace app\service;

use app\component\ServiceException;
use app\dao\PayEventDao;
use app\dao\PayOrderDao;
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
        if (!$service_id || !in_array($service_id, $this->config_params['pay_service_id_set'])) {
            throw new ServiceException('服务id不存在', 100010);
        }
        $service = $this->config_params['pay_call_back_serbice_dict'][$service_id];
        $this->service = new $service();
        $this->service_id = $service_id;
        return;
    }

    /**
     * 处理回调
     * @param $channel_id
     */
    public function mian()
    {
        $service_id = 200100;
        try {
            //设置服务
            $this->setService($service_id);
            //获取回调数据
//            $call_back_data = $this->service->getCallBackData();
//            if (!$call_back_data) {
//                throw new ServiceException('参数错误', 100010);
//            }
            $event_data = [
                'pay_order_id' => 1010101,
                'event_type' => 20,
                'event_data' => serialize($GLOBALS),
                'create_time' => Tools::getTimeSecond(),
            ];
            PayEventDao::addEvent($event_data);
            exit();
            //获取创建支付订单参数
            $attach = $call_back_data['attach'];
            $order_id = json_decode($attach, 1)['order_id'];
            $order_info = $this->getOrderById($order_id);
            if (!$order_info || !isset($order_info['params']) || $order_info['params'] == '') {
                throw new ServiceException('参数错误', 100010);
            }
            //回调设置服务
            $requst_data = json_decode($order_info['params'], 1);
            $ret = $this->service->main($requst_data);
            //记录回调数据
            $event_data = [
                'pay_order_id' => $order_id,
                'event_type' => 20,
                'event_data' => json_encode($ret['data']['call_back_data']),
                'create_time' => Tools::getTimeSecond(),
            ];
            PayEventDao::addEvent($event_data);
            if ($ret['code'] != 0) {
                throw new ServiceException('支付回调失败', 100010);
            }
            //回调注册服务对象回调地址
            $call_back_res = Tools::http_get($ret['data']['call_back_res']['url']);
            if (!$call_back_res['res']) {
                throw new ServiceException('处理回调失败', 100011);
            }
        } catch (ServiceException $e) {
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
        return ['code' => 0, 'msg' => 'access'];


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

}