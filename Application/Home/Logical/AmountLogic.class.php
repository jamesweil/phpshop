<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <13052079525>
// +----------------------------------------------------------------------
namespace Home\Logical;

use Home\Model\GoodsModel;
use Common\Model\BaseModel;
use Home\Model\SpecGoodsPriceModel;
use Common\Model\OrderGoodsModel;

/**
 * 库存逻辑处理
 * 
 * @author 王强
 * @version 1.0
 */
class AmountLogic
{
    /**
     * 商品数组
     * 
     * @var array
     */
    private $goodsData = array();
    
    private $error = '';
    
    private $objModel; //模型对象
    
    /**
     *
     * @return the $goodsData
     */
    public function getGoodsData()
    {
        return $this->goodsData;
    }

    /**
     *
     * @param multitype: $goodsData            
     */
    public function setGoodsData($goodsData)
    {
        $this->goodsData = $goodsData;
    }

    public function __construct($goodsData, BaseModel $model)
    {
        $this->goodsData = $goodsData;
        
        $this->objModel = $model;
    }

    public function amountParse()
    {
        
        $status = $this->delStock();
        
        if (empty($status)) {
            return $status;
        }
        return $status;
        
    }

    /**
     * 检查库存是否可减
     */
    public function checkAmountDel()
    {
        $data = $this->goodsData;
        
        if (empty($data)) {
            return false;
        }
        
        $title = '';
        
        $flag = 0;
        
        foreach ($data as $key => $value) {
            if ($value['stock'] - $value['goods_num'] < 0) {
                
                $title .= ','.$value[GoodsModel::$title_d];
                
                $flag = 5;
            }
        }
        if ($flag !== 0) {
            $this->error = $title.' 库存不足';
            
            $this->objModel->rollback();
            return false;
        }
        return true;
    }
    
    /**
     * @return the $error
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * 处理库存
     * @param BaseModel $goodsModel
     * @return boolean|unknown
     */
    protected final function delStock()
    {
        $orderId = $this->goodsData;
       
        if (empty($orderId)) {
            return false;
        }
        
        //获取订单商品
        $orderGoodsModel = $this->objModel;
        
        $orderGoodsData  = $orderGoodsModel->getGoodsDataByOrderId($orderId);
        
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        
        $goodsModel->setKeyArray([
            GoodsModel::$stock_d
        ]);
    
       
        // 减少库存
        $status = $goodsModel->delStock($orderGoodsData);
    
        if ($status === false) {
            return false;
        }
    
        // 规格表减少库存
        $specModel = BaseModel::getInstance(SpecGoodsPriceModel::class);
    
        $specModel::setStock(); // 设置库存
        $specModel->setKeyArray([
            SpecGoodsPriceModel::$stock_d
        ]);
    
        $specModel->setFieldUpdate(SpecGoodsPriceModel::$goodsId_d);
    
        $status = $specModel->delStock($orderGoodsData);
    
        if ($status === false) {
            return false;
        }
        $specModel->commit();
        return $status;
    }
}
