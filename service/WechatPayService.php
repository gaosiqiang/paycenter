<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/2
 * Time: 10:47 AM
 */

namespace app\service;

use app\service\CommonService;
use app\component\WechatJsApiPayTool;

require_once LIB_PATH . "/wechat_sdk/lib/WxPay.Api.php";
require_once LIB_PATH . "/wechat_sdk/lib/WxPay.Data.php";

class WechatPayService
{

    public $service = null;

    public function setPayMode($channel, $mode_code)
    {

    }

    public function setParams($params)
    {

    }

    public function getJsApiData()
    {
        $tools = new WechatJsApiPayTool();
        $openId = '';
        $input = new \app\library\wechat_sdk\lib\WxPayUnifiedOrder();
        $input->SetBody("test");
        $input->SetAttach("test");
        $input->SetOut_trade_no("sdkphp".date("YmdHis"));
        $input->SetTotal_fee("1");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://paysdk.weixin.qq.com/notify.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $config = new WxPayConfig();
        $order = \app\library\wechat_sdk\lib\WxPayApi::unifiedOrder($config, $input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        //获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddressParameters();
        if (!$jsApiParameters || !$editAddress) {
            return ['code' => 100010, 'msg' => 'error params', 'data' => (object)[]];
        }
        return ['code' => 0, 'msg' => 'access', 'data' => ['js_api_parameters' => $jsApiParameters, 'edit_address' => $editAddress]];
    }

}