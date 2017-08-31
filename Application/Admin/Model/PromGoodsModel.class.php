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

/**
 * 促销模型 
 * @author 王强
 * @version 1.0.1
 */
class PromGoodsModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//活动ID

	public static $name_d;	//促销活动名称

	public static $type_d;	//促销类型

	public static $expression_d;	//优惠体现

	public static $description_d;	//活动描述

	public static $startTime_d;	//活动开始时间

	public static $endTime_d;	//活动结束时间

	public static $status_d;	//活动状态 1 开启 0 关闭

	public static $group_d;	//适用范围

	public static $promImg_d;	//活动宣传图片

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

    /**
     * 获取类的实例
     * @return \Admin\Model\PromGoodsModel
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 添加前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected function _before_insert(&$data, $options)
    {
        $data[static::$createTime_d] = time();
         
        $data[static::$updateTime_d] = time();
    
        return $data;
    }
    
    /**
     * 更新前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_update()
     */
    protected function _before_update(&$data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$name_d, $data[static::$name_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$name_d].'】';
            return false;
        }
        $data[static::$updateTime_d] = time();
    
        return $data;
    }
    
    /**
     * 添加促销商品 
     * @param array $data post数据
     * @param string $fun 方法名
     * @return boolean 
     */
    public function addProGoods(array $data, $fun = 'add')
    {
        if (empty($data) || !is_array($data) || !method_exists($this, $fun)) {
            return false;
        }
       
        if ( $data[static::$startTime_d] > $data[static::$endTime_d]) {
            $this->error = '开始时间不能大于结束时间';
            $this->rollback();
            return false;
        }
        
        $this->startTrans();
      
        $data[static::$group_d] = implode(',', $data[static::$group_d]);
        $data[static::$startTime_d] = strtotime($data[static::$startTime_d]);
        $data[static::$endTime_d]   = strtotime($data[static::$endTime_d]);
        return $this->$fun($data);
    }
    
    /**
     * @desc 删除数据 
     * @param int $id
     * @return boolean
     */
    public function deletePro($id)
    {
        if (!is_numeric($id) || $id == 0) {
            return false;
        }
        
        $this->startTrans();
        $status = $this->delete($id);
        
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        return $status;
    }
    
    /**
     * 获取分类数据
     */
    public function getClassDataByPage ($where = array())
    {
        return $this->getDataByPage([
            'field' => [
                static::$promImg_d,
                static::$createTime_d,
                static::$updateTime_d,
                static::$description_d
            ],
            'where' => $where,
            'order' => static::$createTime_d.self::DESC.','.static::$updateTime_d.self::DESC
        ], 10, true);
    }
}