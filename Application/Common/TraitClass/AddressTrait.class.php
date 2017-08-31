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

use Common\Tool\Tool;
use Common\Model\BaseModel;
use Common\Model\RegionModel;

/**
 * 地区列表 处理
 * @author 王强
 * @version 1.0.1
 */
trait AddressTrait
{
    /**
     * 获取地址列表
     */
    public function getAreaList ()
    {
    
        Tool::checkPost($_POST, array('is_numeric' => array('id')), true, array('id')) ?  : $this->ajaxReturnData(null, 0, '操作失败哦');
    
        $areaModel = BaseModel::getInstance(RegionModel::class);
    
        Tool::connect("PinYin");
    
        $data      = $areaModel->getContent($_POST['id']);
    
    
        $this->updateClient($data, '操作');
    }
    
    /**
     * 获取地区
     */
    public function getAreaListByName()
    {
        Tool::checkPost($_POST, array(), false, ['areaName']) ? : $this->ajaxReturnData(null, 0, '操作失败哦');
        
        $areaModel = BaseModel::getInstance(RegionModel::class);
        
        $data = $areaModel->getAreaByName($_POST['areaName']);
        
         $this->updateClient($data, '操作');
        
    }
}