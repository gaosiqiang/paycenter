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
     * 退款
     */
    public function actionReturn()
    {
    }

    /**
     * 获取支付参数
     * @throws \Exception
     */
    public function actionGet()
    {
        $channel_id = Yii::$app->request->post('channel_id', 0);
        $scene_id = Yii::$app->request->post('scene_id', 0);
        $brand_info = Yii::$app->request->post('brand_info', '');
        $ret = (new WechatPayService())->main($channel_id, $scene_id, $brand_info);
        var_dump($ret);die();
    }

}