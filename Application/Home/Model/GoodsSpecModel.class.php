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

/**
 * 商品规格
 * @author Administrator
 */

class GoodsSpecModel extends BaseModel
{
    private static $obj;
    
    public static $id_d;
    
    public static $typeId_d;
    
    public static $name_d;
    
    public static $sort_d;
    
    public static $status_d;
    
    public static $createTime_d;
    
    public static $updateTime_d;
    
    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    /**
     * 获取数据【规格组数据】
     */
    public function getSpecGroup (array $data, $split)
    {
        $data =  $this->getDataByOtherModel($data, $split, [
            self::$id_d,
            self::$name_d
        ], self::$id_d);
        
        
        return $data;
    }
    
    /**
     * 处理规格 组成 文字 例如 颜色：红色；
     */
    public function parseSpec($group, $item) 
    {
        if (!$this->isEmpty($group) || !is_string($item)) {
            return array();
        }
        
        $str = '';
        
        foreach ($group as $key => $value) {
            $str .= $value[self::$name_d].':'.$value[$item].',';
        }
        
        return substr($str, 0, -1);
    }
}