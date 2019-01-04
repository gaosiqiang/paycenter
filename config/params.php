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
     * 支付频道服务场景id地图
     */
    'pay_channel_service_scene_id_map' => [
        200000 => [
            200100 => [
                200101,
                200102,
                200103,
            ],
        ],
    ],
    /**
     * 支付频道服务字典
     */
    'pay_channel_services_dict' => [
        100100 => 'AliPayService',
        200100 => 'WechatPayService',
    ],
    /**
     * 支付频道-服务-场景字典
     */
    'pay_channel_scene_dict' => [
        200101 => 'web_pay',
        200102 => 'wap_pay',
        200103 => 'app_pay',
    ],

];
