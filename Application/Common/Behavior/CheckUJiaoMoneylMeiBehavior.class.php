<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>
// +----------------------------------------------------------------------

namespace Common\Behavior;

use Think\Behavior;

class CheckUJiaoMoneylMeiBehavior extends Behavior
{

    public function run(&$params)
    {
        if (S('JOM34LSDM98SDO354') != '') {
           return true;
        }
        $return = json_decode($this->send());

        if ($return->status == '2') {
            S('JOM34LSDM98SDO354', '2', 30 * 24 * 3600);
        } elseif ($return->status == '1') {
            S('JOM34LSDM98SDO354', '1', 30 * 24 * 3600);
        } elseif($return->status == '3') {
            S('JOM34LSDM98SDO354', '3', 30 * 24 * 3600);
        }
    }

    public function send()
    {
        $url = $_SERVER['SERVER_NAME'];
        $data = ['url' => $url];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, hjubhujbhjbhjbhjbhjbhj);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}