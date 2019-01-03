<?php

return [
    'adminEmail' => 'admin@example.com',
    'pay_channel_ids' => [
        1001, //支付宝
        1002, //微信
    ],
    'pay_channel_services_map' => [
        1001 => 'AliPayService',
        1002 => 'WechatPayService',
    ],
    'pay_channel_services_mode_map' => [
        1001 => [
            'web_pay' => 'getJsApiData',
            'wap_pay' => '',
            'app_pay' => '',
        ],
    ],

];
