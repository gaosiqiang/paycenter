<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:42 AM
 */

namespace app\modules\payment\controllers;

use app\service\WechatAuth;
use Yii;
use app\component\CommonController;

class WechatauthController extends CommonController
{
    /**
     * 微信授权的获取openid
     * @param $appid
     * @param $mch_id
     * @param string $code
     * @return 用户的openid
     */
    public function actionOpenid()
    {
        $code = Yii::$app->request->get('code', '');
        $appid = Yii::$app->request->get('appid', '');
        $mch_id = Yii::$app->request->get('mch_id', '');
        $openid = (new WechatAuth())->GetOpenid($code, $appid, $mch_id);
        return $openid;
        die();
    }

}