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

namespace Home\Model;

use Think\Model;

class SystemConfigModel extends Model
{
    private static $obj;
    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    public function getAllConfig(array $option = null)
    {
        $data = $this->field('create_time,update_time', true)->where($option)->select();
        
        if (!empty($data))
        {
            foreach ($data as $key => &$value)
            {
                if (!empty($value['config_value']))
                {
                    $unData = unserialize($value['config_value']);
                    unset($data[$key]['config_value']);
                    $value = array_merge($value, $unData);
                }
            }
        }
        return $data;
    }
}