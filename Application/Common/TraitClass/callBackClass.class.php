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

namespace Common\TraitClass;

/**
 * 回调方法
 * @author 王强
 */
trait callBackClass
{
    public  function compare ($goodsA, $goodsB) 
    {
        return $goodsA['goods_id'] - $goodsB['goods_id'] <0;
    }
    
    public  function compareSmall ($goodsA, $goodsB)
    {
        return $goodsA['goods_id'] - $goodsB['goods_id'] >0;
    }
    
    
    public  function compareOrder ($goodsA, $goodsB)
    {
        return $goodsA['order_id'] - $goodsB['order_id'] <0;
    }
    
    public  function compareUserId ($goodsA, $goodsB)
    {
        return $goodsA['id'] - $goodsB['id'] <0;
    }
    
}