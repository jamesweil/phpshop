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
 * 优惠促销相关
 * @author 王强
 */
trait ParsePromotionTrait
{
    private $expression = 0;
    /**
     * 打折促销
     */
    protected function getPromotionType0 ($price)
    {
    
        $price =  sprintf("%.2f", ($price * $this->expression)/100);
    
        return $price;
    }
    
    /**
     * 减价优惠
     */
    protected function getPromotionType1 ($price)
    {
        $price = $price - $this->expression;
    
        return $price;
    }
    
    /**
     * 固定金额出售
     */
    protected function getPromotionType2 ($price)
    {
        $price =  $this->expression;
    
        return $price;
    
    }
    
    /**
     * 买就送代金券
     */
    protected function getPromotionType ($goodsData)
    {
    
    
    }
}