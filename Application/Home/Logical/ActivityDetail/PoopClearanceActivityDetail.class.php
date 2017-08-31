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
namespace Home\Logical\ActivityDetail;

use Common\Model\BaseModel;
use Home\Model\PoopClearanceModel;
use Common\Model\PromotionTypeModel;
use Home\Model\GoodsCartModel;
use Home\Logical\ActivityIntface\ActivityInterface;

/**
 * 尾货清仓
 * @author 王强
 */
class PoopClearanceActivityDetail implements ActivityInterface
{
    
    private $goods = [];
    
    const  GOODS_HTML = 'poopClearByGoodsInformation';
    
    public function __construct(array $goods) {
        $this->goods = $goods;
    }
    
    /**
     * {@inheritDoc}
     * @see \Home\Logical\ActivityIntface\PoopClearanceActivityInterface::getResult()
     */
    public function getResult()
    {
        if (empty($this->goods)) {
            return array();
        }
        
        // TODO Auto-generated method stub
        $model = BaseModel::getInstance(PoopClearanceModel::class);
        
        $poopClearGoods = $model->getPoopClearData($this->goods['id']);
        
        // 获取活动类型
        $promotionType = BaseModel::getInstance(PromotionTypeModel::class);
        
        $typeData = $promotionType->getPromotionType($poopClearGoods[PoopClearanceModel::$typeId_d]);
        
        unset($poopClearGoods[PoopClearanceModel::$id_d], $poopClearGoods[PoopClearanceModel::$sort_d], $typeData[PromotionTypeModel::$id_d]);
        
        $this->goods = array_merge($poopClearGoods, $typeData, $this->goods);
       
        return empty($typeData) ? -1 : (int)$typeData[PromotionTypeModel::$status_d];
    }
    /**
     * @return the $goods
     */
    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * @param Ambigous <multitype:, unknown> $goods
     */
    public function setGoods($goods)
    {
        $this->goods = $goods;
    }
    /**
     * {@inheritDoc}
     * @see \Home\Logical\ActivityIntface\ActivityInterface::getResultByManyArrays()
     */
    public function getResultByManyArrays()
    {
        // TODO Auto-generated method stub
        
        $goods = $this->goods;
        
        if (empty($goods)) {
            return array();
        }
        
        // TODO Auto-generated method stub
        $model = BaseModel::getInstance(PoopClearanceModel::class);
        
        $poopClearGoods = $model->getPoopClearByGoods($goods, GoodsCartModel::$goodsId_d);
        
        // 获取活动类型
        $promotionType = BaseModel::getInstance(PromotionTypeModel::class);
        
        $typeData = $promotionType->getTypeData($poopClearGoods, PoopClearanceModel::$typeId_d);
        
        return $typeData;
    }
    /**
     * {@inheritDoc}
     * @see \Home\Logical\ActivityIntface\ActivityInterface::getHtmlName()
     */
    public function getHtmlName()
    {
        // TODO Auto-generated method stub
        return self::GOODS_HTML;
    }

}