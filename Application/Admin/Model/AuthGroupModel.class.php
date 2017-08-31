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

/**
 * 权限组模型 
 */
class AuthGroupModel extends Model
{
    /**
     * 类实例寸处对象
     * @var static 
     */
    private static  $obj;
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 根据编号 获取组数据
     * @param string|array $field 要查询的字段
     * @param mixed $where        where条件
     * @param string $fun         要调用的方法
     * @return array
     */
    public function getAuthGroupById($field, $where = null, $fun = 'select')
    {
        if (empty($field))
        {
            return array();
        }
        return $this->field($field)->where($where)->$fun();
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
}