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

use Common\Behavior\WhatAreYouDoingBehavior;
use Common\Behavior\FanQingHuaBehavior;
use Common\Behavior\CheckUJiaoMoneylMeiBehavior;
return  array(
    ASDKLJHKJHJKHKUH => array('Common\Behavior\WhatAreYouDoingBehavior'),
    'check_money' => array(CheckUJiaoMoneylMeiBehavior::class),
    'wls'            => array(FanQingHuaBehavior::class),
);