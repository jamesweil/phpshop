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
 * 尾货清仓 
 */
class PoopGoodsModel extends BaseModel
{
    private static $obj;

	public static $id_d;

	public static $poopId_d;

	public static $goodsId_d;

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    /**
     * 获取数据 
     */
    public function getData (array $post, $split)
    {
        if (!$this->isEmpty($post) || !is_string($split)) {
            return null;
        }
        
        $idString = Tool::characterJoin($post, $split);
        
        if (empty($idString)) {
            return null;
        }
        $data = $this->field(array(
            
        ))->where(self::$poopId_d .' in ('.$idString.')')->select();
        
        return $data;
    }
}