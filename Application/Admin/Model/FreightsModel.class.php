<?php

// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>\n
// +----------------------------------------------------------------------

namespace Admin\Model;

use Common\Model\BaseModel;
use Common\Model\IsExitsModel;
use Common\Tool\Tool;

/**
 * 运费模板 
 */
class FreightsModel extends BaseModel implements IsExitsModel
{
    
    private static  $obj;

	public static $id_d;

	public static $expressTitle_d;

	public static $sendTime_d;

	public static $isFree_shipping_d;

	public static $valuationMethod_d;

	public static $isSelect_condition_d;

	public static $stockId_d;


	public static $updateTime_d;

	public static $createTime_d;

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    protected function _before_insert(& $data, $options)
    {
        $data[static::$createTime_d] = time();
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    protected function _before_update(& $data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$expressTitle_d, $data[static::$expressTitle_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$expressTitle_d].'】';
            return false;
        }
        $data[static::$updateTime_d] = time();
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    /**
     * 运费模板是否存在 
     */
    public function IsExits($post)
    {
        if (empty($post)) {
           throw new \Exception('数据异常');
        }
        
        $data = $this->getAttribute(array(
            'field' => array(static::$id_d),
            'where' => array(static::$expressTitle_d => array('like', $post))
        ));
        
        return empty($data) ? false : true;
        
    }
    
    /**
     * 获取模板 
     */
    public function getTemplate()
    {
        $data = $this->getField(static::$id_d.','.static::$expressTitle_d);
        
        if (empty($data)) {
            return array();
        }
        
        foreach ($data as $key => & $value) {
            $value = Tool::getFirstEnglish($value).' '.$value;
        }
        
        return $data;
    }
}