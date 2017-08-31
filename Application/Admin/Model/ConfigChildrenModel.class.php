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
 * 系统配置模型
 * @author Administrator
 * @version 1.0.0
 */
class ConfigChildrenModel extends BaseModel
{

	public static $id_d;	//id

	public static $configClass_id_d;	//内容分类编号

	public static $showType_d;	//展示类型

	public static $typeName_d;	//对应的name值

	public static $updateTime_d;	//更新时间

	public static $createTime_d;	//创建时间

	public static $type_d;	//输入框对应的类型
    
	/**
	 * 类实例的承载着
	 * @var ConfigChildrenModel
	 */
    private static  $obj;
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 添加前操作
     */
    protected function _before_insert(&$data, $options)
    {
        
        $data[static::$createTime_d] = time();
        $data[static::$updateTime_d] = time();
        return $data;
    }
    /**
     * 更新前操作
     */
    protected function _before_update(&$data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$typeName_d, $data[static::$typeName_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$typeName_d].'】';
            return false;
        }
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    /**
     * 删除 配置
     * {@inheritDoc}
     * @see \Think\Model::delete()
     */
    public function delete($options = array())
    {
        if (empty($options['where'])  || !is_array($options) )
        {
            return false;
        }
   
        $id = parent::delete($options);
        
        return $id;
    }
    /**
     * 获取全部数据 
     * @param array $options 搜索条件
     * @return array
     */
    public function getAll(array $options = NULL)
    {
        return $this->field('create_time,update_time', true)->where($options)->select();
    }
    
    /**
     * 根据主键编号 获取 name属性 
     * @param array $data 主键编号数组
     * @return array
     */
    public function getDataById(array $data)
    {
        if (empty($data) || !is_array($data)) {
            return array();
        }
        
        $ids = implode(',', array_keys($data));
        
        $parseData = $this->where(static::$configClass_id_d .' in('.addslashes($ids).')')->getField(static::$configClass_id_d.','.static::$typeName_d);
        return $parseData;
    }
}