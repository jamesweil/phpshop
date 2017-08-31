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
namespace Home\Strategy\SpecificStrategy;

use Home\Strategy\AbstractStrategy;

/**
 * 减价优惠 类
 * @author 王强
 * @version 1.0.0
 */
class DiscountActivity extends AbstractStrategy
{
    public function __construct(array $goodsData)
    {
        $this->goodsData = $goodsData;
    }
    /**
     * {@inheritDoc}
     * @see \Home\Strategy\AbstractStrategy::acceptCash()
     */
    public function acceptCash()
    {
        // TODO Auto-generated method stub
        
    }
    /**
     * {@inheritDoc}
     * @see \Home\Strategy\AbstractStrategy::getResultByManyArrays()
     */
    public function getResultByManyArrays()
    {
        // TODO Auto-generated method stub
        
    }



}