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

use Common\Model\BaseModel;

/**
 * 尾货清仓 
 * @author 王强
 * @version 1.0.1
 */
class PoopGoodsModel extends BaseModel
{
    private static $obj;

	public static $id_d; // 主键编号

	public static $poopId_d; // 尾货清仓主表编号

	public static $goodsId_d; // 商品编号

    /**
     * 获取类的实例
     * @return \Admin\Model\PoopGoodsModel
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * @desc 根据父级id 插入数据
     * @param array $data  要插入的数据
     * @param int   $insertId 类别编号
     * @return boolean
     */
    public function addGoodsByPromotionId(array $data, $insertId)
    {
        if (empty($data[static::$goodsId_d]) || !is_array($data) || $insertId == 0) {
            $this->rollback();
            return false;
        }
    
        $data = $this->create($data);
        if (empty($data)) {
            $this->rollback();
            return false;
        }
        $proGoods = array();
        foreach ($data[static::$goodsId_d] as $key => $value) {
            $proGoods[$key][static::$goodsId_d] = $value;
            $proGoods[$key][static::$poopId_d]  = $insertId;
        }
    
        $flag = array();
    
        //去重
        foreach ($proGoods as $key => $value) {
            $id = intval($value[static::$goodsId_d]);
            if (!isset($flag[$id])) {
                $flag[$id] =  $value;
            }
             
        }
    
        if (empty($flag)) {
            $this->rollback();
            return false;
        }
    
        rsort($flag);
    
        $insertProId = $this->addAll($flag);
    
        if (empty($insertProId)) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return $insertProId;
    
    }
}