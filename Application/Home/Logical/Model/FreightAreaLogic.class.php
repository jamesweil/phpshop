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
namespace Home\Logical\Model;


use Common\Model\BaseModel;
use Common\Tool\Tool;
use Home\Model\FreightAreaModel;

/**
 * 包邮地区逻辑处理层
 */
class FreightAreaLogic
{
    private $freightAreaObj;
    
    private $freightData;
    
    private $areaId;
    
    /**
     * @return the $freightData
     */
    public function getFreightData()
    {
        return $this->freightData;
    }

    /**
     * @return the $areaId
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * @param field_type $freightData
     */
    public function setFreightData($freightData)
    {
        $this->freightData = $freightData;
    }

    /**
     * @param field_type $areaId
     */
    public function setAreaId(array $areaId)
    {
        $this->areaId = implode(',', $areaId);
    }
    
    /**
     * 初始化参数
     * @param array $freightData
     */
    public function __construct(array $freightData) 
    {
        $this->freightData = $freightData;
        
        $this->freightAreaObj = BaseModel::getInstance(FreightAreaModel::class);
    }
    
    /**
     * 该模板是否包含 改地区 
     * @param array $areaId
     * @param array $express
     * @param BaseModel $freightModelMonery
     * @return array
     */
    public function isInclude ()
    {
        $express = $this->freightData;
        $areaId  = $this->areaId;
      
        if ( empty($areaId) || empty($express) ) {
            return array();
        }
        
        $id = Tool::characterJoin($express, 'freightCondition');
        
        if (empty($id)) {
            return $express;
        }
        
        
        $data = $this->freightAreaObj->where(FreightAreaModel::$freightId_d .' in ('.$id.') and '.FreightAreaModel::$mailArea_d .' in ('.$areaId.')')->select();
       
      
        if (empty($data)) {//不包含该地区【没有 倒着的】
            return array();
        }
        
        $i = 0;
        
        $lenght = count($data);
        
        $temp = [];
        
        foreach ($data as $key => $value) {
            $temp[] = (int)$value[FreightAreaModel::$freightId_d];
        }
        
        foreach ($express as $key => $value) {
        
            if (!in_array((int)$value['freightCondition'], $temp, true)) {
                unset($express[$key]);
            }
        }
        return $express;//包含该地区
    }
}