<?php

return [
    [
        'name' => 'mchid',
        'title' => '商户号',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => 'required',
        'msg' => '',
        'tip' => '在PAYJS会员中心查看',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'appkey',
        'title' => '通信密钥',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => 'required',
        'msg' => '',
        'tip' => '在PAYJS会员中心查看',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'pay_channel',
        'title' => '支付通道',
        'type' => 'select',
        'content' => [
            'all' => '支付宝和微信',
            'alipay' => '支付宝',
            'weixin' => '微信',
        ],
        'value' => 'all',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'notify_url',
        'title' => '回调地址',
        'type' => 'string',
        'content' => [],
        'value' => 'http://127.0.0.1/fastadmin/public/addons/payjs/notify',
        'rule' => 'required',
        'msg' => '',
        'tip' => '接收异步通知的回调地址（确保可以外网访问）',
        'ok' => '',
        'extend' => '',
    ],
];