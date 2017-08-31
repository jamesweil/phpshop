<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王强 <13052079525>
// +----------------------------------------------------------------------
namespace Common\Logic;


abstract class AbstractGetDataModel
{
    protected  $data = array();
    
    protected $modelObj;
    
    /**
     * @return the $modelObj
     */
    public function getModelObj()
    {
        return $this->modelObj;
    }

    /**
     * @param field_type $modelObj
     */
    public function setModelObj($modelObj)
    {
        $this->modelObj = $modelObj;
    }

    /**
     * @return the $goodsData
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * @param multitype: $goodsData
     */
    public function setData($goodsData)
    {
        $this->data = $goodsData;
    }
    
    /**
     * 获取结果
     */
    abstract public function getResult ();
}