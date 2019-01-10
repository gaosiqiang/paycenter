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
    public function mian($service_id)
    {
        try {
            //设置服务
            $this->setService($service_id);
            //获取回调数据
            $call_back_data = $this->service->getCallBackData();
//            if (!$call_back_data) {
//                throw new ServiceException('回调数据不存在', 100010);
//            }
            //获取创建支付订单参数
            if (!isset($call_back_data['attach']) || $call_back_data['attach'] == '') {
                throw new ServiceException('回调数据attach参数为空', 100010);
            }
            $order_id = $call_back_data['attach'];
            //添加记录回调数据
            $event_data = [
                'pay_order_id' => $order_id,
                'event_type' => 20,
                'event_data' => json_encode($call_back_data),
                'create_time' => Tools::getTimeSecond(),
            ];
            PayEventDao::addEvent($event_data);
            $order_info = $this->getOrderById($order_id);
            if (!$order_info || !isset($order_info['params']) || $order_info['params'] == '') {
                throw new ServiceException('回调错误支付订单', 100010);
            }
            //回调设置服务
            $requst_data = json_decode($order_info['params'], 1);
            $ret = $this->service->main($call_back_data, $requst_data);
//            if ($ret['code'] != 0) {
//                throw new ServiceException('支付回调失败', 100010);
//            }
        } catch (ServiceException $e) {
            //错误的回调-数据记录log
            return ['code' => $e->getCode(), 'msg' => $e->getMessage(), 'res' => ''];
        }
        //修改支付订单状态
        if ($ret['code'] != 0) {
            $handle_status = 0;
        } else {
            $handle_status = 1;
        }
        $handle_pay_order_res = (new PayOrderService())->callBackOrder($order_id, $handle_status);
        if ($handle_pay_order_res) {
            //回调注册服务对象回调地址
            Tools::http_get($ret['data']['call_back_res']['url']);
//        if (!$call_back_res['res']) {
//            throw new ServiceException('处理回调失败', 100011);
//        }
        } else {
            return ['code' => 0, 'msg' => 'access', 'res' => ''];
        }
        return ['code' => 0, 'msg' => 'access', 'res' => Tools::arrayToXml(['return_code' => 'SUCCESS', 'return_msg' => 'OK'])];


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