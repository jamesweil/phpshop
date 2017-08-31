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
namespace Home\Strategy;

/**
 * 策略模式
 */
abstract class AbstractStrategy
{
    protected $goodsData = [];
    
    /**
     * @return the $goodsData
     */
    public function getGoodsData()
    {
        return $this->goodsData;
    }

    /**
     * @param multitype: $goodsData
     */
    public function setGoodsData($goodsData)
    {
        $this->goodsData = $goodsData;
    }

    /**
     * 实现收钱方法
     */
    abstract public function acceptCash();
    
    /**
     * 多商品 实现收钱方法
     */
    abstract public function getResultByManyArrays();
}