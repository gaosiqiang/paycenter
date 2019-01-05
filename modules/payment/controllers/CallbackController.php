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
     * 接收回调
     */
    public function actionIndex()
    {
        $channel_id = Yii::$app->get('channel_id', 0);//回调频道id
        $ret = (new PayCallBackService())->callBack($channel_id);
        print_r($ret);
        die();
    }

}