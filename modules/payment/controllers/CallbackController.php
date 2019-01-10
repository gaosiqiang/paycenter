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
     * 微信支付回调
     */
    public function actionWechat()
    {
        $service_id = 200100;
        $ret = (new PayCallBackService())->mian($service_id);
        $this->code = $ret['code'];
        $this->msg = $ret['msg'];
        echo $ret['res'];
        exit();
    }

    /**
     * 退款回调
     */
    public function actionRefund()
    {
    }

}