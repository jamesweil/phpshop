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
 * 运送方式表 
 */
class FreightModeModel extends BaseModel
{
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

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    /**
     * 获取数据 根据运送方式
     * @param array $data 快递公司数据
     * @return 运费主表编号 +快递公司数据
     */
    public function getFreightByMode (array $data)
    {
        if (!$this->isEmpty($data)) {
            return array();
        }
        
        $freightMode = S('freightModeData');
        
        if (empty($freightMode)) {
            
            $id = null;
            
            foreach ($data as $key => $value) {
                if (!is_int($key)) {
                    return array();
                }
                $id .= ','.$key;
            }
            
            if (empty($id)) {
                return array();
            }
            
            $id = substr($id, 1);
            
            $freightMode = $this->where(self::$carryWay_d .' in ('.$id.')')->select();
            
            if (empty($freightMode)) {
                return array();
            }
            $expressId = 0;
            foreach ($freightMode as $key => & $value) {
                if (array_key_exists($value[self::$carryWay_d], $data)) {
                    $expressId = $value[self::$carryWay_d];
                    $value[self::$carryWay_d] = $data[$value[self::$carryWay_d]];
                    $value['expressPrimayId'] = $expressId;
                }
            }
            unset($data);
            S('freightModeData', $freightMode, 5);
        }
       
        return $freightMode;
    }
    
    /**
     * 根据运送方式 获取运费设置列表
     * @param int $id 运送方式编号
     * @return array;
     */
    public function getShipModeConfig ($id)
    {
        if (($id = intval($id)) === 0) {
            return [];
        }
        $key = md5($id).'_freight_mode';
    
        $data = S($key);
    
        $fields = implode(',', $this->getDbFields());
    
        if (empty($data)) {
            $data = $this->where(self::$carryWay_d.'=%d', $id)->getField($fields);
        } else {
            return $data;
        }
    
        if (empty($data)) {
            return array();
        }
        S($key, $data, 10);
    
        return $data;
    }
    
    /**
     * 获取数据
     */
    public function getShipping($id)
    {
        if ( ($id = intval($id)) === 0) {
            return array();
        }
        
        return $this->where(self::$id_d.'= "%s"', $id)->find();
    }
    
    
}