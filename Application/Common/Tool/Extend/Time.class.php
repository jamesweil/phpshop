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

namespace Common\Tool\Extend;
use Common\Tool\Tool;
use Common\Tool\Intface\TimeInterFace;

/**
 * 时间处理工具 
 */
class Time extends Tool implements TimeInterFace
{
    /**
     * 转换时间格式
     */
    public function parseTime(array $data,$key ='create_time')
    {
        if (empty($data))
        {
            return $data;
        }
        
        foreach ($data as $setkey => &$value)
        {
            if (!empty($value[$key]))
            {
                $value[$key] = date('Y-m-d H:i:s', $value[$key]);
            }
        }
        return $data;
    }
    
    /**
     * 获取前几天时间数组
     * @param int $number 前几天
     * @return array
     */
    public function getTime($number)
    {
       
        if (!is_int($number)) {
            return null;
        }
        $height = $number;
        $dateStr = null;
        $dateArray = array();
        for ($i = $height, $i >= 0; $i--;) {//因为是从零开始的
            $dateStr = date("Y-m-d", strtotime("-".$i." day"));
            $dateArray[] = $dateStr;
        }
        return $dateArray;
    }
}