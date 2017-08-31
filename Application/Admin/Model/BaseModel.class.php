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
use Common\Tool\Tool;
use Think\Exception;
use Think\Page;

/**
 * 早期基类模型
 * @deprecated 已放弃
 * @author Administrator
 * @version 1.0
 */
class BaseModel extends Model
{
   private static $obj;
   
   
    /**
     * 取得子类的实例 
     */
    public static function getInstance($className, $metheds='getInitnation')
    {
//         $array = array();
        
//         $model = S('model')[$className];
      
        if (empty(static::$obj[$className]))
        {
            static::$obj[$className] = $className::$metheds();
            
//             S('model', $array, 100);
        }
        return static::$obj[$className];
    }
    
    /**
     * 为类中的数据库字段赋值 
     */
    protected  function setDbFileds(Model $model, $suffix = '_d')
    {
        if (!($model instanceof Model))
        {
            throw new Exception('模型不匹配');
        }
        
        try{
            // 反射类中的数据库属性
            $reflection         =  new \ReflectionObject($model);
           
            $staticProperties   =  $reflection->getStaticProperties();
          
            if (!empty($staticProperties))
            {
                Tool::connect('ArrayChildren');
                //截取数据库字段
                $dbFileds  = Tool::getSplitUnset($staticProperties);
                $dbData    = $model->getDbFields();
                $this->error($dbData, $model);
                
                $flag = count($dbData);
                $i    = 0;     
                foreach ($dbFileds as $key => &$value)
                {
                    $model::$$key = $dbData[$i];
                    $i++;
                    if ($i > $flag-1)
                    {
                        break;
                    }
                }
             }
        } catch (\Think\Exception $e) {
            throw new \ErrorException('该模型不匹配基类模型');
        }
    }
    
    private function error($data, Model $model)
    {
        if (empty($data))
        {
            throw new \Exception('该模型【'.get_class($model).'】对应的数据表无字段');
        }
    }
    
    /**
     * 去除不查询的字段 
     * @param array $fields 要去除查询的字段
     * @return array;
     */
    public function deleteFields( array $fields)
    {
        $fieldsDb = $this->getDbFields();
        if (empty($fields) || empty($fields))
        {
            return array();
        }
        foreach ($fieldsDb as $key => $name)
        {
            if (in_array($name, $fields))
            {
                unset($fieldsDb[$key]);
            }
        }
        return $fieldsDb;
    }
    
    /**
     * 获取商品属性数据
     * @param array  $options 查询条件
     * @param bool   $isNoSelect 是否过滤字段
     * @param string 调用的方法
     * @return array;
     */
    public function getAttribute($options, $isNoSelect = false, $default = 'select')
    {
        if (empty($options))
        {
            return array();
        }
        if ($isNoSelect)
        {
            $options['field'] = $this->deleteFields($options['field']);
        }
        return $this->$default($options);
    }
    
    /**
     * 分页读取数据
     */
    public function getDataByPage( array $options, $pageNumer = 10, $isNoSelect = false)
    {
        if (empty($options) || !is_int($pageNumer))
        {
            return array();
        }
         
        $count = $this->count();
        $page = new Page($count, $pageNumer);
         
        $options['limit'] = $page->firstRow.','.$page->listRows;
         
        $data = $this->getAttribute($options, $isNoSelect);
         
        $array['data'] = $data;
         
        $array['page'] = $page->show();
         
        return $array;
    }
    
    /**
     * add
     */
    public function add($data='',$options=array(),$replace=false)
    {
        if (empty($data))
        {
            return false;
        }
        $data = $this->create($data);
         
        return parent::add($data, $options, $replace);
    }
    
    /**
     * save
     */
    public function save($data='',$options=array())
    {
        if (empty($data))
        {
            return false;
        }
        $data = $this->create($data);
    
        return parent::save($data, $options);
    }
     
    
    
    public function __construct($name = '', $tablePrefix = '', $connection ='')
    {
        parent::__construct($name, $tablePrefix, $connection);
        //数据字段赋值
        $this->setDbFileds($this);
    }
}