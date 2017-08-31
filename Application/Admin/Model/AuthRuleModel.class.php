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
use Common\Tool\Extend\parseString;

/**
 * 权限认证规则模型 
 */
class AuthRuleModel extends Model 
{
    /**
     * 类的实例承载着
     * @var AuthRuleModel
     */
    private static  $obj;
    
    /**
     * 获取类的实例
     * @return \Admin\Model\AuthRuleModel
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 重写添加操作
     */
    public function add($data = '', $options = array(),  $replace = false)
    { 
        if (empty($data) || !is_array($data))
        {
            return array();
        }
       
        $addData  = $this->create($data);
        return parent::add($addData, $options, $replace);
    }
    
    /**
     * 添加前操作
     */
    protected function _before_insert(&$data, $options)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['status']      = 1;
        $data['type']        = 1;
        $data['status']      = 1;
        return $data;
    }
    
    /**
     * 更新前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_update()
     */
    protected function _before_update( &$data, $options)
    {
        $data['update_time'] = time();
        return $data;
    }
    
    /**
     * 保存 
     * {@inheritDoc}
     * @see \Think\Model::save()
     */
    public function save($data='', $options=array())
    {
        if (empty($data))
        {
            return false;
        }
        $data = $this->create($data);
    
        return parent::save($data, $options);
    }
    
    /**
     *  获取权限列表 
     * @param string | array $field 字段
     * @param string | array $where where 条件
     * @param string $fun 方法名
     * @param string $order 排序
     * @return array
     */
    public function getAuthGroupById( $field, $where = null, $fun = 'select', $order = 'sort DESC')
    {
        if (empty($field))
        {
            return array();
        }
        
        $data = S('authRule');
        if (empty($data)) {
           
            $data = $this->getDataByMethod($field, $where, $fun, $order);
            
            S('authRule', $data, 5);
        }
        
        return (array)$data;
    }
    /**
     * 根据不同的方法 调用 获取不同的数据
     * @param string | array $field 字段
     * @param string | array $where where 条件
     * @param string $fun 方法名
     * @param string $order 排序
     * @return array
     */
    protected  function getDataByMethod($field, $where = null, $fun = 'select', $order = 'sort DESC')
    {
        if (empty($field) || !method_exists($this, $fun))
        {
            return array();
        }
           
        $data = $this->field($field)->where($where)->order($order)->$fun();
        
        if (empty($data)) {
            return array();
        }
        
        foreach ($data as $key => & $value) {
            $value['title'] = Tool::getFirstEnglish($value['title']) .'  '.$value['title'];
        }
            
        return $data;
    }
    
    /**
     * 获取二级分类 
     * @param array $fauther 父级分类
     * @return array
     */
    public function getTwoChildren(array $fauther,  $field)
    {
        if (empty($fauther))
        {
            return array();
        }
        $ids = (new parseString(null))->characterJoin($fauther, 'id');
      
        if (empty($ids))
        {
            return array();
        }
        $children = $this->getDataByMethod($field, 'pid in('.$ids.')');
        return $children;
    }
    
    /**
     * 根据子类编号获取爷爷类编号 
     * @param array $children
     * @return array
     */
    public function getGrandFather(array $children)
    {
        if (empty($children))
        {
            return array();
        }
        //获取父级编号
        $pId = array_shift(Tool::compressArray($children,'pid'));
        
        $grandFatherId = static::topId($pId);
        
        foreach ($children as $key => &$value)
        {
            $value['pid'] = $grandFatherId;
        }
        
        return $children;
    }
    /**
     * 获取顶级编号 
     * @param int $topId 编号
     * @return int
     */
    private  function topId($topId)
    {
        if (!is_numeric($topId))
        {
            return false;
        }
        $id = static::getPk();
        $pId = static::find(array(
            'field' => id.',pid',
            'where' => array('id' => $topId),
        ));
        
        return (empty($pId)) ? false : ($pId['pid'] == 0 ? $pId[$id] : static::topId($pId['pid']));
    }
}