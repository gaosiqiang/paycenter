<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/4
 * Time: 6:52 PM
 */
namespace app\modules\payment\controllers;

use app\service\PayCallBackService;
use Yii;
use app\component\CommonController;

class CallbackController extends CommonController
{

    /**
     * 支付回调
     */
    public function actionPay()
    {
        $service_id = Yii::$app->request->get('service_id', 0);//回调频道id
        $order_id = Yii::$app->request->get('order_id', 0);//支付订单id
        $ret = (new PayCallBackService())->mian($service_id, $order_id);
        $this->code = $ret['code'];
        $this->msg = $ret['msg'];
        //$this->data = $ret['data'];
        $this->echoJson();
    }

    /**
     * 退款回调
     */
    public function actionRefund()
    {
    }

}