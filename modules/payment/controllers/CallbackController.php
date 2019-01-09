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
        $service_id = Yii::$app->get('service_id', 0);//回调频道id
        $request_data = Yii::$app->get('request_data', '');
        $ret = (new PayCallBackService())->mian($service_id, $request_data);
        $this->code = $ret['code'];
        $this->msg = $ret['msg'];
        $this->data = $ret['data'];
        $this->echoJson();
    }

    /**
     * 退款回调
     */
    public function actionRefund()
    {
    }

}