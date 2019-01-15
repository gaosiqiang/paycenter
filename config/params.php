<?php

return [
    'adminEmail' => 'admin@example.com',
    'site_name' => 'paycenter',
    'site_url' => 'http://pay.zhugexuetang.com',
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
        100100, //支付宝支付服务
        200100, //微信支付服务
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
        100100 => [
            100101,
            100102,
            100103,
        ],
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
        100101 => 'handleWeb',//网页支付
        100102 => 'handleWap',//wap支付
        100103 => 'handleApp',//app支付
        200101 => 'handleNative', //扫码支付
        200102 => 'handleJsapi', //wap支付
        200103 => 'handleApp', //app支付
    ],
    'pay_call_back_serbice_dict' => [
        100100 => '\app\service\AliPayCallBackService',
        200100 => '\app\service\WechatPayCallBackService',
    ],
    /**
     * 退款服务集合
     */
    'pay_refund_service_set' => [
        100200, //支付宝退款服务
        200200, //微信退款服务
    ],
    /**
     * 退款服务字典
     */
    'pay_refund_service_dict' => [
        100200 => '\app\service\AliRefundService',
        200200 => '\app\service\WechatRefundService',
    ],
    /**
     * 退款回调服务集合
     */
    'pay_refund_call_back_service_set' => [
        200200,
    ],
    /**
     * 退款回调服务字典
     */
    'pay_refund_call_back_service_dict' => [
        200200 => '\app\service\WechatRefundCallBackService',
    ],
    /**
     * 回调地址
     */
    'call_back_url' => [
        100100 => 'payment/callback/alipay',//支付宝支付回调
        200100 => 'payment/callback/wechat',//微信支付回调
        200200 => 'payment/callback/wechatrefund',//微信退款回调
    ],
    /**
     * service list
     */
    'qrcode_service_url' => 'http://qrcode.zyuwen.cn/img/qrcode?code=',

];
