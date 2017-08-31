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
 * 运送方式表 
 */
class FreightModeModel extends BaseModel
{
    /**
     * 类的实例
     * @var FreightModeModel
     */
    private static $obj;

	public static $id_d;	//ID

	public static $freightId_d;	//运费模板编号

	public static $firstThing_d;	//首件

	public static $firstWeight_d;	//首重

	public static $fristVolum_d;	//首体积

	public static $fristMoney_d;	//首运费【起步价】

	public static $continuedHeavy_d;	//续重

	public static $continuedVolum_d;	//续体积

	public static $continuedMoney_d;	//续费

	public static $carryWay_d;	//运送方式编号

	public static $continuedThing_d;	//续件

    /**
     * 获取类的实例
     * @return \Admin\Model\FreightModeModel
     */
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  static::$obj= !(static::$obj instanceof $class) ? new static() : static::$obj;
    }
    
    /**
     * 获取运送方式编号 
     * @param int $limit 读几条
     * @param array $where 搜索条件
     */
    public function getData($limit, array $where = null)
    {
        if ( ($limit = intval($limit)) === 0) {
            return array();
        }

        if (!empty($_GET) && empty($where)) {
            return array();
        }
        
        $data = $this->getDataByPage(array(
            'field' => $this->getDbFields(),
            'where' => $where,
        ), $limit);
        
        return $data;
    }
    
    /**
     * 根据订单信息 获取运送方式 及其 运费 金额 
     * @param array $data 订单数组
     * @param BaseModel $model 订单对象
     * @return array
     */
    public function getShippingMode(array $data, BaseModel $model)
    {
        if (!$this->isEmpty($data) || !($model instanceof BaseModel)) {
            return array();
        }
        
        $shippingData = S('SHIPPING_CACHE_DATA');
        
        if (empty($shippingData)) {
            $shippingData = $this->getDataByOtherModel($data, $model::$freightId_d, [
                static::$id_d. static::DBAS .$model::$freightId_d,
                static::$carryWay_d,
            ], static::$id_d);
            
            if (empty($shippingData)) {
                return array();
            }
            S('SHIPPING_CACHE_DATA', $shippingData, 6);
        }
        return $shippingData;
    }
}