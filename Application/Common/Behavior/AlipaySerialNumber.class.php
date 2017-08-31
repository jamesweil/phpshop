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

use Common\Model\BaseModel;
use Common\Model\AlipaySerialNumberModel;

class AlipaySerialNumber
{
    public function aplipaySerial(&$param)
    {
        $status = BaseModel::getInstance(AlipaySerialNumberModel::class)->addParse($param);
        
        $param = empty($status) ? [] : $param;
    }
}