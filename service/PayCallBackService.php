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
            //获取创建支付订单参数
            if (!isset($call_back_data['attach']) || $call_back_data['attach'] == '') {
                throw new ServiceException('回调数据attach参数为空', 100010);
            }
            $order_id = $call_back_data['attach'];
            $order_info = (new PayOrderService())->getOrderById($order_id);
            if (!$order_info) {
                throw new ServiceException('支付订单错误', 100011);
            }
            $pay_params = json_decode($order_info['params'], 1);
            //添加记录回调数据
            $event_data = [
                'pay_order_id' => $order_id,
                'event_type' => 20,
                'event_data' => json_encode($call_back_data),
                'create_time' => Tools::getTimeSecond(),
            ];
            (new PayEventService())->addONeEvent($event_data);
            $ret = $this->service->main($call_back_data, $pay_params['key']);
            //修改支付订单状态
            $handle_pay_order_res = (new PayOrderService())->callBackOrder($order_id, ($ret['code'] != 0) ? 0 : 1);
            if (!$handle_pay_order_res) {
                throw new ServiceException('修改支付订单状态失败', 100012);
            }
            Tools::http_post($pay_params['notify_url'], ['pay_order_id' => $order_id, 'biz_order_id' => $order_info['biz_order_id'], 'return_status' => 0, 'return_msg' => 'access']);
        } catch (ServiceException $e) {
            //错误的回调-数据记录log
            Tools::http_post($pay_params['notify_url'], ['pay_order_id' => $order_id, 'biz_order_id' => $order_info['biz_order_id'], 'return_status' => $e->getCode(), 'return_msg' => $e->getMessage()]);
//            return ['code' => $e->getCode(), 'msg' => $e->getMessage(), 'res' => Tools::arrayToXml(['return_code' => 'FAIL', 'return_msg' => $e->getMessage()])];
            return ['code' => $e->getCode(), 'msg' => $e->getMessage(), 'res' => $this->service->returnData(0, $e)];
        }
//        return ['code' => 0, 'msg' => 'access', 'res' => Tools::arrayToXml(['return_code' => 'SUCCESS', 'return_msg' => 'OK'])];
        return ['code' => 0, 'msg' => 'access', 'res' => $this->service->returnData(1)];
    }

}