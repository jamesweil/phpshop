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

namespace Admin\Controller;

use Common\Controller\AuthController;
use Common\Model\BaseModel;
use Admin\Model\GoodsAttributeModel;
use Admin\Model\GoodsAttrModel;
use Common\Tool\Tool;
use Admin\Model\GoodsModel;

/**
 * ajax 获取商品属性
 */
class AjaxGetAttributeController extends AuthController
{
    /**
     * 商品属性显示列表
     */
    public function ajaxGetAttributeInput($id, $goodsId)
    {
        ($id = intval($id)) !== 0 || empty($goodsId) ? : $this->ajaxReturnData(null, 0, '操作失败');
        
        $goodsAttributeModel = BaseModel::getInstance(GoodsAttributeModel::class);
        
        //获取商品属性数据
        
        $goodsAttributeData = $goodsAttributeModel->getAttributeByTypeId($id);
        
        $goodsAttrModel     = BaseModel::getInstance(GoodsAttrModel::class);
        
        $goodsAttrModel->setProductId($goodsId);
        
        Tool::connect('parseString');
        
        //生成HTML数据
        $htmlString         = $goodsAttrModel->buildHtmlString($goodsAttributeData, $goodsAttributeModel);
        
        $this->ajaxReturnData($htmlString);
    }
    
    /**
     * 添加 属性 
     */
    public function addGoodsAttribute ()
    {
       Tool::checkPost($_POST, array(['is_numeric' => 'attr_type'],'goods_images', 'extend', 'item', 'class_name'), true, array('attr_type')) ? : $this->ajaxReturnData(null, 0, '操作失败');
       
       $this->promptPjax($_SESSION['insertId'], '添加失败');
       
       $_POST['goods_id'] = $_SESSION['insertId'];
       
       $goodsModel = BaseModel::getInstance(GoodsModel::class);
       
       $status = $goodsModel->saveAttrType($_POST);
       
       $this->promptPjax($status, $goodsModel->getError());
       
       $goodsAttributeModel = BaseModel::getInstance(GoodsAttrModel::class);
       
       $status = $goodsAttributeModel->addAttributeData($_POST);
       
       $this->updateClient($status, '添加');
    }
    
    /**
     * 修改商品属性 
     */
    public function editGoodsAttribute()
    {
        $mustExits = ['attr_type', 'goods_id', 'attr_id'];
        
        // 检测post数据
        Tool::checkPost($_POST, array(['is_numeric' => $mustExits],'goods_images', 'extend', 'item', 'class_name'), false, $mustExits) ? : $this->ajaxReturnData(null, 0, '操作失败');
        
        // 获取商品模型
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        //获取商品类型分类
        $attributeType = $goodsModel->getUserNameById((int)$_POST['goods_id'], GoodsModel::$attrType_d);
        
        // 修改商品属性
        $status = $goodsModel->saveAttrType($_POST);
        
        //检测是否成功
        $this->promptPjax($status, $goodsModel->getError());
        
        //获取商品属性模型
        $attrModel = BaseModel::getInstance(GoodsAttrModel::class);
        
        $attrModel->setVarriableType($attributeType !== $_POST['attr_type']);
         
        $status = $attrModel->editAttributeData($_POST);
         
        $this->updateClient($status, '添加');
    }
}