<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:44 AM
 */

namespace app\modules\payment\controllers;

use Yii;
use app\component\CommonController;
use app\service\PayHandleService;
use app\service\WechatPayService;

class PayController extends CommonController
{


    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        echo 111;die();
    }

    /**
     * 发起支付
     */
    public function actionCreate()
    {

    }

    /**
     * 退款
     */
    public function actionReturn()
    {
    }

    public function actionWechatpay()
    {
        $channel_id = Yii::$app->request->post('channel_id', 0);
        $ret = (new WechatPayService())->getJsApiData();
        var_dump($ret);die();
    }

}