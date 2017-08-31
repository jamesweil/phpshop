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
 * 规格数据模型
 * @author 王强
 */
class GoodsSpecItemModel extends BaseModel
{
    private static $obj ;

	public static $id_d;

	public static $specId_d;

	public static $item_d;

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    /**
     * 处理规格组 规格项
     * @param array $specItem  规格项数组
     * @param array $group     规格组数组
     * @return array；
     */
    public function parseData( array $specItem, array $group, $join)
    {
        if (!$this->isEmpty($specItem) || !$this->isEmpty($group) || empty($join)) {
            return array();
        }
        $flag = null;
        
//         foreach ($specItem as $key => $value) {
            foreach ($group as $name =>  &$data) {
//                 if ($value[self::$specId_d] !== $data[self::$specId_d]) {
//                     continue;
//                 }

                if (empty($data[$join])) {
                                        continue;
                }
                $flag = $data[$join].':'.$data[self::$item_d];
                $data[self::$item_d] = $flag;
            }
//         }
        return $group;
    }
}