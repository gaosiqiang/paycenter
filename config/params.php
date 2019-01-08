<?php

return [
    'adminEmail' => 'admin@example.com',
    /**
     * 支付频道集合
     */
    'pay_channel_id_set' => [
        100000, //支付宝
        200000, //微信
    ],
    /**
     * 支付服务集合
     */
    'pay_service_id_set' => [
        200100,
    ],
    /**
     * 支付场景集合
     */
    'pay_scene_id_set' => [
        200101,
        200102,
        200103,
    ],
    /**
     * 支付服务场景id地图
     */
    'pay_service_scene_id_map' => [
        200100 => [
            200101,
            200102,
            200103,
        ],
    ],
    /**
     * 支付频道服务字典
     */
    'pay_services_dict' => [
        100100 => '\app\service\AliPayService',
        200100 => '\app\service\WechatPayService',
    ],
    /**
     * 支付频道-服务-场景字典
     */
    'pay_scene_dict' => [
        200101 => 'handleNative', //扫码支付
        200102 => 'handleJsapi', //wap支付
        200103 => 'handleApp', //app支付
    ],
    'pay_call_back_serbice_dict' => [
        200100 => '\app\service\WechatCallBackService',
    ],
    /**
     * 退款服务集合
     */
    'pay_refund_service_set' => [
        200100,
    ],
    /**
     * 退款服务字典
     */
    'pay_refund_service_dict' => [
        200100 => '\app\service\WechatRefundService',
    ],
    //service list
    'qrcode_service_url' => 'http://qrcode.zyuwen.cn/img/qrcode?code=',

];
