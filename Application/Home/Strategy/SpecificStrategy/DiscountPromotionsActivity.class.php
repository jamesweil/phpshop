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
use Home\Model\GoodsModel;
use Home\Model\GoodsCartModel;

/**
 * 打折促销活动 类
 */
class DiscountPromotionsActivity extends AbstractStrategy
{
    /**
     * 折扣
     * @var float
     */
    private  $discount = 100;
    
    /**
     * @return the $discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param number $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

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
        
        $goodsData = $this->goodsData;
       
        if (empty($goodsData)) {
            return array();
        }
        
        $goodsData[GoodsModel::$priceMember_d] = (float)sprintf('%.2f', ($goodsData[GoodsModel::$priceMember_d]* $goodsData['expression']/100));
        
        $goodsData['totalMoney'] = $goodsData[GoodsModel::$priceMember_d] * $goodsData['goods_num'];
        
        return $goodsData;
    }
    /**
     * {@inheritDoc}
     * @see \Home\Strategy\AbstractStrategy::getResultByManyArrays()
     */
    public function getResultByManyArrays()
    {
        // TODO Auto-generated method stub
        
        $goodsData = $this->goodsData;
         
        if (empty($goodsData)) {
            return array();
        }
        
        $money = 0;
        
        foreach ($goodsData as $key => & $value) {
            
            $money = (float)sprintf('%.2f', $value[GoodsCartModel::$priceNew_d] * $value['expression']/100);
            
            $value[GoodsCartModel::$priceNew_d] = $money;
            
            $value['totalMoney'] = $money * $value[GoodsCartModel::$goodsNum_d];
        }
        
        return $goodsData;
        
    }

}