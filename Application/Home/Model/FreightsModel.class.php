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
use Common\Model\BaseModel;
use Common\Tool\Tool;

/**
 * @author Administrator
 * 商品重量模型（用于运费计算） 
 */

class FreightsModel extends BaseModel
{
    private static $obj;
    

	public static $id_d;

	public static $expressTitle_d;

	public static $sendTime_d;

	public static $isFree_shipping_d;

	public static $valuationMethod_d;

	public static $isSelect_condition_d;

	public static $stockId_d;

	public static $updateTime_d;

	public static $createTime_d;

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    public function getFreight(array $options, Model $model)
    {
        if (empty($options) || !is_array($options) || !($model instanceof Model)) {
            return array();
        }
        $areaData = $model->find($options);
        $data      = array();
        if (!empty($areaData))
        {
            $data = $this->find(array(
                'where' => array('province' => $areaData['areaid']),
                'field' => array('ykg,money,onemoney')
            ));
        }
        return $data;
    }
    
    /**
     * 是否包邮 
     */
    public function isFreeShipping (array $express, BaseModel $freightModelMonery)
    {
        if ( !$this->isEmpty($express) || !($freightModelMonery instanceof BaseModel)) {
            return array();
        }
        
        $split = $freightModelMonery::$freightId_d;
        
        $data = $this->getDataByOtherModel($express, $split, [
                self::$id_d,
                self::$isFree_shipping_d,
                self::$isSelect_condition_d,
                self::$valuationMethod_d
            ], self::$id_d);
        
        //指定条件包邮 or 包邮
        if (empty($data)) {
            return $data;
        }
        
        $flag = array(); //
        
        foreach ($express as $key => $value) {//一对多
            
            $flag = current($data);
            
            if ($value[$split] !== $flag[$split]) {
                continue;
            }
            
            if ( (int)$flag[self::$isFree_shipping_d] === 2) {//是否包邮或者 指定条件包邮【1自定义运费2卖家包邮】
                    $value[$freightModelMonery::$fristMoney_d] = 0;
                    $value[$freightModelMonery::$continuedMoney_d] = 0;
            }
        }
        
        return $data;
    }
}