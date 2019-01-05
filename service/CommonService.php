<?php
namespace app\service;
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2018/12/28
 * Time: 2:49 PM
 */

use Yii;

class CommonService {

    public $config_params = [];

    public function __construct($call_back = null, $param = [])
    {
        if (!is_null($call_back)) {
            $call_back->init($param);
        } else {
            $this->init();
        }
    }

    public function init()
    {
        $this->config_params = Yii::$app->params;
    }
}