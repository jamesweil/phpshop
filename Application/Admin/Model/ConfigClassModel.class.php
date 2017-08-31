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

use Think\Model;
use Common\Model\BaseModel;
use Common\Tool\Tool;

/**
 * 系统配置分类模型 
 */
class ConfigClassModel extends BaseModel
{
    /**
     * 类的实例
     * @var ConfigClassModel
     */
    private static $obj;

	public static $id_d;	//id

	public static $configClass_name_d;	//分类配置名称

	public static $pId_d;	//父级分类

	public static $isOpen_d;	//0=》启用配置， 1弃用配置

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

    /**
     * 获取类的实例
     * @return \Admin\Model\ConfigClassModel
     */
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
        $data['create_time'] = time();
        $data['update_time'] = time();
        return $data;
    }
    /**
     * 更新前操作 
     */
    protected function _before_update(&$data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$configClass_name_d, $data[static::$configClass_name_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$configClass_name_d].'】';
            return false;
        }
        $data['update_time'] = time();
        return $data;
    }
    
    /**
     * 重写添加操作 
     */
    public function add($data = '', Model $model, $options = array(),  $replace = false)
    {
        if (empty($data) || !is_array($data) || !($model instanceof Model))
        {
            return false;
        }
        $addData  = $this->create($data);
        $insertChildrenId = 0;
        if ( ($insertId  = parent::add($addData, $options, $replace)) != false && isset($data['p_id']))
        {
            $data['config_class_id'] = $insertId;
            $insertChildrenId = $model->add($data);
            return $insertChildrenId && $insertId;
        }
        
        return $insertId ;
    }
    
    /**
     * 重写更新操作
     */
    public function save($data = '',Model $model, $options = array() )
    {
        if (empty($data) || !is_array($data) || !($model instanceof Model))
        {
            return array();
        }
        $updateData = $this->create($data);
       
        $updateChildrenId = 0;
        if ( ($updateChildrenId  = parent::save($updateData, $options)) != false && !empty($data['children_id']))
        {   
            $data[static::$id_d] = $data['children_id'];
            $updateChildrenId = $model->save($data);
            return $updateChildrenId ;
        }
        return $updateChildrenId ;
    }
    /**
     * 获取全部分类 
     * @param array $options 条件
     * @return array
     */
    public function getAllClass(array $options)
    {
        if (empty($options))
        {
            return $options;
        }
        return parent::select($options);
    }
    /**
     * 获取全部子集分类【树形结构】
     * @param array $where 查询条件
     * @param array $field 查询的字段
     * @return string
     */
    public function getChildren(array $where = null, array $field = null)
    {
        // 根据地区编号  查询  该地区的所有信息
        $video_data   = parent::select(array(
            'where' => $where,
            'field' => $field,
        ));
        showData($video_data);
        if (empty($video_data))
        {
            return;
        }
        $pk    = $this->getPk();
        static $children = array();
      
        foreach ($video_data as $key => &$value)
        {
            if(!empty($value['id']))
            {
                $where['p_id'] = $value['id'];
                $child = $this->getChildren($where, $field);
                $children[$key] = $value;
                if (!empty($child))
                {
                    $children[$key]['children'] = $child;
                }
                unset($value, $child);
            }
        }
        return $children;
    }
    
  
    /**
     * 获取全部子集分类编号
     * @param array $where 查询条件
     * @param array $field 查询的字段
     * @return string
     */
    public function getChildrenId(array $where = null, array $field = null)
    {
        // 根据地区编号  查询  该地区的所有信息
        $video_data   = parent::select(array(
            'where' => $where,
            'field' => $field,
        ));
        
        if (empty($video_data)) {
            return array();
        }
        
        static $data ;
        $pk    = $this->getPk();
        foreach ($video_data as $key => &$value)
        {
            if(!empty($value[$pk]))
            {
                $data .= ','. $value[$pk];
                $where['p_id'] = $value[$pk];
                unset($where['id']);
                $child = $this->getChildrenId($where, $field);
                if (!empty($child))
                {
                    foreach ($child as $key_value => $value_key)
                    {
                        if (!empty($value_key[$pk]))
                        {
                            $data.=','.$value_key[$pk];
                        }
                    }
                }
                unset($value, $child);
            }
        }
        return !empty($data) ? substr($data , 1) : null;
    }
    
    /**
     * 获取一行数据
     * @param array $options 条件
     * @param Model $model   其他模型
     * @return array
     */
    public function getFind(array $options = array(), Model $model)
    {
        if (empty($options) || !is_array($options) || !($model instanceof Model))
        {
            return array();
        }
        
        $data = parent::find($options);
        if (!empty($data))
        {
            $children = $model->field('id as children_id,type_name,show_type, type')->where('config_class_id = "'.$data['id'].'"')->find();
            $data     = array_merge($data , (array)$children);
        }
        return $data;
    }
    
    /**
     * 是否还有下级分类 
     * @param array $optionss 
     * @return int
     */
    public function isHaveClass(array $optionss)
    {
        if (empty($optionss))
        {
            return array();
        }
        return $this->where($optionss)->count();
    }
    /**
     * 重写删除 
     */
    public function delete($options = array(), Model $model)
    {
        if (empty($options['where']) || empty($options['field']) || empty($options['where']['id']) || !is_array($options) || !($model instanceof Model))
        {
            return array();
        }
        
        //是否有下级
        $have = $this->getChildrenId($options['where'], $options['field']);
        $deld = 0;
        if (false !== strpos($have, ',') && !empty($have))
        {
            $sumId = $options['where']['id'].','.$have;
            $deld  = $model->delete(array(
                'where' => array('config_class_id' => array('in', $have))
            ));
            $options['where'] = array('id' => array('in',$sumId));
            
        }
        else if (!empty($have))
        {
            $deld = $model->delete(array(
                'where' => array('config_class_id' => $options['where']['id'])
            ));
        }
        return  parent::delete($options);
    }
    //是否存在
    public function isHaveName(array $options)
    {
        if (empty($options))
        {
            return array();
        }
        $data = $this->where($options)->find();
        return empty($data) ? false : true;
    }
    
    /**
     * 查找子集和自己 
     */
    public function getChildrenAndMe(array $options)
    {
        if (empty($options))
        {
            return array();
        }
        
        $data = $this->where($options)->select();
        
        return $data;
    }
    
    /**
     * 根据父类id 获取子集 
     */
    public function getClassDataById(array $data)
    {
        if (empty($data) || !is_array($data))
        {
            return $data;
        }
        
        foreach ($data as $key => $value)
        {
            $id = $key;
        }
        
        if (empty($id) || !is_numeric($id)) {
            return array();
        }
        
        return $this->where(static::$pId_d.'="%s"', $id)->getField(static::$id_d.','.static::$configClass_name_d);
    }
    
    /**
     * 配置分页 
     */
    public function getPageData ($page = 5)
    {
        $parentData = $this->getDataByPage([
            'field' => $this->getDbFields(),
            'where' => [static::$pId_d => 0]
        ], $page);
       
        if (empty($parentData['data'])) {
            return $parentData;
        }
        
        $idString = Tool::characterJoin($parentData['data'], static::$id_d);
       
        if (empty($idString)) {
            return $parentData;
        }
        
        $childrenData = $this->where(static::$pId_d.' in ('.$idString.')')->select();
        $parentData['data'] = array_merge($parentData['data'], $childrenData);
        
        return $parentData;
    }
}