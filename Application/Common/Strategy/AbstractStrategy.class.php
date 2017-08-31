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
namespace Common\Strategy;

/**
 * 策略模式
 */
abstract class AbstractStrategy
{
    protected $discount= 100;
    
    protected $receive = []; //运费结果集
    /**
     * 实现收钱方法
     */
    abstract public function acceptCash();
    
    /**
     * @param number $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = (float)$discount;
    }
}