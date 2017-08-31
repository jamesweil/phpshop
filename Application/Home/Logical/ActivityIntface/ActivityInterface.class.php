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
namespace Home\Logical\ActivityIntface;

/**
 * 
 * @author Administrator
 */
interface  ActivityInterface
{
    /**
     * 获取活动数据
     */
    public function getResult();
    
    /**
     * 多个数组 中获取数据
     */
     public function getResultByManyArrays ();
     
     /**
      * 获取HTML文件名
      */
     public function getHtmlName ();
}