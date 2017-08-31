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
/**
 * 图片模型 
 */
namespace Admin\Model;
use Think\Model;
use Common\Tool\Tool;

class PictureModel 
{
    /**
     * 删除指定的图片 
     * @param array $options 图片路径数组
     * @return bool
     */
    public  function unlink(array $options)
    {
        if (empty($options) ||  !is_array($options))
        {
            return false;
        }
        
        return  Tool::partten($options);
    }
    
    /**
     * 构造 删除图片条件 
     * @param array $confition 要检测的数据
     * @param array $validata  要检测的建
     * @return array $validata
     */
    public function buildCondition(array $confition, array $validata)
    {
        if (empty($confition) || empty($validata))
        {
            return array();
        }
        static::pz($confition);
        foreach ($validata as $key => &$value)
        {
            if ( !array_key_exists($value, $confition) || empty($confition[$value]['name']) )
            {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 递归删除不是数组的值
     * @param array $data
     * @return array
     */
    public static function pz(array &$data)
    {
        foreach ($data as $key => &$value)
        {
            if (is_array($value))
            {
               static::pz($value); 
            }
            else if (empty($value))
            {
                unset($data[$key]);
            }
        }
        return $data;
    }
}