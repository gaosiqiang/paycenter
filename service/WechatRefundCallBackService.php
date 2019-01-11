<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/10
 * Time: 5:57 PM
 */

namespace app\service;

use app\component\Tools;
use app\service\CommonService;

class WechatRefundCallBackService extends CommonService
{
    /**
     * 回调数据
     * @var array
     */
    public $call_back_data = [];

    /**
     * 获取回调数据
     * @return array
     */
    public function getCallBackData()
    {
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = $GLOBALS['HTTP_RAW_POST_DATA'];
        } else {
            $data = file_get_contents('php://input');
        }
        $data = Tools::xmlToArray($data);
        if (!$data) {
            return [];
        }
        return $data;
    }

    /**
     * 验证回调数据
     * @return array
     */
    public function checkCallBackData($call_back_data)
    {
        if (!isset($call_back_data['return_code']) || $call_back_data['return_code'] != 'SUCCESS') {
            return 0;
        }
        //验证签名是否一致
        if ($call_back_data['sign'] != WechatPayTools::MakeSign($call_back_data, false)) {
            return 0;
        }
        return 1;
    }

}