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
 * 促销管理模型
 * @author Administrator
 * @version 1.0
 */
class PromotionGoodsModel extends BaseModel
{
    private static $obj;
    
	public static $id_d;	     //主键编号

	public static $promId_d;	//促销编号

	public static $goodsId_d;	//商品编号

	public static $startTime_d;	//促销开始时间

	public static $endTime_d;	//促销结束时间

	public static $activityPrice_d;	//促销价格

	/**
	 * 获取类的实例
	 * @return \Admin\Model\PromotionGoodsModel
	 */
	
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 插入数据
     * @desc 根据父级id 插入数据
     * @param array $data  要插入的数据
     * @param int   $insertId 类别编号
     * @return boolean 
     */
    public function addGoodsByPromotionId(array $data, $insertId)
    {
        if (empty($data[static::$goodsId_d]) ||$insertId == 0) {
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
            $proGoods[$key][static::$promId_d]  = $insertId;
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
    
    /**
     * 保存数据 
     * @param array $post post 数据
     * @param string $mustKey  以哪个字段拼接相关关联映射
     * @return boolean
     */
    public function savePost(array $post, $mustKey) 
    {
        if (empty($post[static::$goodsId_d]) || !is_array($post) ||!is_string($mustKey)) {
            $this->rollback();
            return false;
        }
        
        if (!array_key_exists($mustKey, $post)) {
            $this->rollback();
            return false;
        }
        
        $status = $this->where(static::$promId_d.' = "%s"', $post[$mustKey])->delete();
        
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        
        $proGoods = array();
        foreach ($post[static::$goodsId_d] as $key => $value) {
            $proGoods[$key][static::$goodsId_d] = $value;
            $proGoods[$key][static::$promId_d]  = $post[$mustKey];
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
        
        if (!$this->traceStation($insertProId)) {
            return false;
        }
        $this->commit();
        return $insertProId;
    }
    /**
     * @desc 删除 
     * @param int $id 编号
     * @return bool
     */
    public function deleteProId($id)
    {
        if (!is_numeric($id) || $id == 0) {
            $this->rollback();
            return false;
        }
        
        $status = $this->where(static::$promId_d .'= "%s"', $id)->delete();
        
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return $status;
    }
}