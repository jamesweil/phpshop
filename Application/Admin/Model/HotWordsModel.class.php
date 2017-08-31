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
use Think\Page;

/**
 * 关键词模型 
 */
class HotWordsModel extends Model
{
    private static $obj;
    
    //获取实例
    public static function getInition()
    {
        return static::$obj = !(static::$obj instanceof HotWordsModel) ? new static() : static::$obj;
    }
    
    //添加前操作
    protected  function _before_insert(&$data, $options)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        return $data;
    }
    
    //更新前操作
    protected function _before_update(&$data, $options)
    {
        $data['update_time'] = time();
        return $data;
    }
    
    /**
     * 重写添加操作 
     */
    public function add($data, array $options = array(), $replace = false)
    {
        if (empty($data))
        {
            return 0;
        }
        
        $data = $this->create($data);
        
        return parent::add($data, $options, $replace);
    }
    
    /**
     * 重写添加操作
     */
    public function save($data, array $options = array())
    {
        if (empty($data))
        {
            return 0;
        }
    
        $data = $this->create($data);
        return parent::save($data, $options);
    }
    
   
    
    //判断是否存在该类别的关键词
    
    public function isHaveHotWords(array $options)
    {
        if (empty($options))
        {
            return array();
        }
        
        return parent::find(array('where' => $options));
    }
    
    /**
     * 获取所有关键词列表
     */
    public function getAll(array $options = array(), Model $model)
    {
        if (!($model instanceof  Model))
        {
            return array();
        }
        $count = $this->count();
        $page = new Page($count, PAGE_SIZE);
        
        $data = $this->where($options)->limit($page->firstRow,$page->listRows)->select();
        if (!empty($data))
        {
            foreach ($data as $key => &$value)
            {
                $value['goods_class_id'] = $model->where('id = "'.$value['goods_class_id'].'"')->getField('class_name');
                $value['update_time']    = date('Y-m-d H:i:s', $value['update_time']);
                $value['create_time']    = date('Y-m-d H:i:s', $value['create_time']);
            }
        }
        $show = $page->show();
        return array('page' => $show, 'data' => $data);
    }
    /**
     * 重写 find 
     */
    public function find( array $options = array(), Model $model)
    {
        if (empty($options) || !($model instanceof Model))
        {
            return array();
        }
        
        $data = parent::find($options);
        
        if (!empty($data))
        {
            $data['class_name'] = $model->where('id = "'.$data['goods_class_id'].'"')->getField('class_name');
        }
        return $data;
    }
}