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
use Think\Model;

/**
 * 广告分类表 
 */
class AdSpaceModel extends Model
{
    
    private static $obj;
    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    /**
     * 查询分类 及其子分类 
     */
    public function select($options = array(), Model $model)
    {
        if (!($model instanceof Model) || !is_object($model))
        {
            return array();
        }
        $data = parent::select($options);
        if (!empty($data))
        {
            foreach ($data as $key => &$value)
            {
                $value['children'] = $model->select(array(
                    'where' => array('ad_space_id' => $value['id'], 'type' => array('in','1,3,4') ,'isshow' => 0),
                    'order' => array('sort_num' => 'DESC', 'update_time' => 'DESC', 'create_time' => 'DESC'),
                    'field' => array('ad_link', 'id', 'title', 'pic_url'),
                    'limit' => 4
                ));
                
                if (empty($value['children']))
                {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }
    
} 