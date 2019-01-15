<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/4
 * Time: 6:52 PM
 */
namespace app\modules\payment\controllers;

use app\service\PayCallBackService;
use app\service\RefundCallBackService;
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
     * 微信退款回调
     */
    public function actionWechatrefund()
    {
        $service_id = 200200;
        $ret = (new RefundCallBackService())->main($service_id);
        $this->code = $ret['code'];
        $this->msg = $ret['msg'];
        echo $ret['res'];
        exit();
    }

    /**
     * 阿里支付回调
     */
    public function actionAlipay()
    {
        $service_id = 100100;
        $ret = (new PayCallBackService())->mian($service_id);
        $this->code = $ret['code'];
        $this->msg = $ret['msg'];
        echo $ret['res'];
        exit();
    }

}