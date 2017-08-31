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

namespace Home\Model;

use Common\Model\BaseModel;
use Common\Tool\Tool;

/**
 * 包邮条件表 
 */
class FreightConditionModel extends BaseModel
{
    private static $obj;

	public static $id_d;

	public static $freightId_d;

	public static $mailArea_num_d;

	public static $mailArea_wieght_d;

	public static $mailArea_volume_d;

	public static $mailArea_monery_d;

	public static $createTime_d;

	public static $updateTime_d;

    
    public static function getInitnation()
    {
        
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    /**
     * 保存 
     * @param array 提交的数据
     * @return bool
     */
    public function saveCondition(array $post) 
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        $this->startTrans();
        
        return $this->save($post);
        
    }
    /**
     * 添加条件 
     */
    public function addCondition (array $post)
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        $this->startTrans();
        
        $status = $this->add($post);
        
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        return $status;
    }
    
    protected function _before_insert(& $data, $options)
    {
        $data[static::$updateTime_d] = time();
        $data[static::$createTime_d]    = time();
        return $data;
    }
    
    protected function _before_update(& $data, $options)
    {
        $data[static::$updateTime_d] = time();
    
        return $data;
    }
    /**
     * 运送地区 是否包含在 免运费地区 
     */
    public function IsInFreeShipingArea(array $express, BaseModel $model)
    {
        if ( !$this->isEmpty($express) || !($model instanceof BaseModel)) {
            return array();
        }
        

        $id = $model::$freightId_d;
        
        $idString = Tool::characterJoin($express, $id);
        
        if (empty($idString)) {
            return array();
        }
        
        $data = $this->field(self::$createTime_d.','.self::$updateTime_d, true)->where(self::$freightId_d.' in ('.$idString.')')->select();
        
        if (empty($data)) {
            return array();
        }
        
        $flag = array();
        foreach ($express as $key => & $value) {
            
            $flag = current($data);
            if ($value[$id] === $flag[self::$freightId_d]) {
                
                $value[self::$mailArea_monery_d] = $flag[self::$mailArea_monery_d];
                
                $value[self::$mailArea_num_d] = $flag[self::$mailArea_num_d];
                
                $value[self::$mailArea_wieght_d] = $flag[self::$mailArea_wieght_d];
                
                $value[self::$mailArea_volume_d] = $flag[self::$mailArea_volume_d];
                $value['freightCondition']        = $flag[self::$id_d];
            }
        }
        return $express;
    }
}