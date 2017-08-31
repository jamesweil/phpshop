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
use Common\Tool\Tool;

/**
 * 促销模型 
 */
class PromGoodsModel extends BaseModel
{
    private static $obj;

	public static $id_d;

	public static $name_d;

	public static $type_d;

	public static $expression_d;

	public static $description_d;

	public static $startTime_d;

	public static $endTime_d;

	public static $status_d;

	public static $group_d;

	public static $promImg_d;

	public static $createTime_d;

	public static $updateTime_d;

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    /**
     * 根据id查询促销活动 
     */
    public function getPromotion(array $id, $split)
    {
        if (empty($id) || !is_array($id) || !is_string($split)) {
            return array();
        }
        
        $idString = Tool::characterJoin($id, $split);
        if (empty($idString)) {
            return $id;
        }
        
        return $this->where(self::$id_d .' in ('.$idString.')')->getField(self::$id_d.','.self::$name_d);
    }
    /**
     * 查询促销数据 
     */
    public function getPromotionInfo(array $data, $split)
    {
        if (!$this->isEmpty($data) || empty($split)) {
            $this->error = '数据错误';
            return array();
        }
        $field = [
            self::$id_d .self::DBAS. $split,
            self::$endTime_d,
            self::$startTime_d,
            self::$status_d,
            self::$type_d,
            self::$expression_d
        ];
        $data = $this->getDataByOtherModel($data, $split, $field, self::$id_d);
        
        return $data;
    }
}