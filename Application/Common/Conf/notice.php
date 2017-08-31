<?php
return [
    'modifyPay' => [
        'pay_account' => '支付账号不能为空且不能有特殊字符',
        'mchid'       => '商户账号只能是数字，且不为空',
        'pay_key'     => '随机字符串只能是数字及其字母的组合且长度是32位，且不为空',
        'public_pem'  => '公钥不能为空',
        'private_pem'  => '私钥不能为空'
    ]
];