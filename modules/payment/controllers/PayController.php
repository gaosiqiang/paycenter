<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:44 AM
 */

namespace app\modules\payment\controllers;

use app\service\AliPayService;
use app\service\RefundService;
use Yii;
use app\component\CommonController;
use app\service\PayHandleService;
use app\service\WechatPayService;
use app\service\AliRefundService;

class PayController extends CommonController
{
    /**
     * 退款
     */
    public function actionRefund()
    {
        $channel_id = Yii::$app->request->post('channel_id', 0);
        $scene_id = Yii::$app->request->post('scene_id', 0);
        $biz_order_id = Yii::$app->request->post('biz_order_id', '');
        $refund_params = Yii::$app->request->post('refund_params', '');
        $ret = (new RefundService())->main($channel_id, $scene_id, $biz_order_id, $refund_params);
        $this->code = $ret['code'];
        $this->msg = $ret['msg'];
        $this->data = $ret['data'];
        $this->echoJson();
    }

    /**
     * 获取支付参数
     * @throws \Exception
     */
    public function actionGet()
    {
        $channel_id = Yii::$app->request->post('channel_id', 0);
        $scene_id = Yii::$app->request->post('scene_id', 0);
        $pay_info = Yii::$app->request->post('pay_info', '');
        $biz_order_id = Yii::$app->request->post('biz_order_id', '');
        $ret = (new PayHandleService())->main($channel_id, $scene_id, $pay_info, $biz_order_id);
        $this->code = $ret['code'];
        $this->msg = $ret['msg'];
        $this->data = $ret['data'];
        $this->echoJson();
    }

    /**
     * 获取退款详情
     */
    public function actionGetrefundinfo()
    {
        $channel_id = Yii::$app->request->post('channel_id', 0);
        $biz_order_id = Yii::$app->request->post('biz_order_id', 0);
        $app_id = Yii::$app->request->post('app_id', '');
        $merchant_id = Yii::$app->request->post('merchant_id', '');
        $private_key = Yii::$app->request->post('private_key', '');
        $public_key = Yii::$app->request->post('public_key', '');
        $refund_id = Yii::$app->request->post('refund_id', '');
        $ret = (new RefundService())->getRefundInfo($channel_id, $biz_order_id, $app_id, $merchant_id, $private_key, $public_key, $refund_id);
        $this->code = $ret['code'];
        $this->msg = $ret['msg'];
        //$this->data = $ret['data'];
        $this->echoJson();
    }

}