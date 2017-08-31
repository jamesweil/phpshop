<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>\n
// +----------------------------------------------------------------------
namespace Common\Behavior;

use Common\Model\BaseModel;
use Common\Model\OrderWxpayModel;

class Decorate
{
    public function aplipaySerial(&$param)
    {
        
        if (empty($param)) {
            $param = [];
        }
        
        //更改订单微信表
        
        $status = BaseModel::getInstance(OrderWxpayModel::class)->nofityUpdate($param);
        if (empty($status)) {
             $param = [];
        }
    }
}