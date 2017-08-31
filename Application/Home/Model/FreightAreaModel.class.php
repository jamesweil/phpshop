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
 * 包邮地区表 
 */
class FreightAreaModel extends BaseModel
{
    private static $obj;

	public static $freightId_d;

	public static $mailArea_d;
	
	private $isFreeShipping = 0;
    
	private $typeFree = 0;
	
    private $objOther ;

   

    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    /**
     * 改快递是否包含 改地区
     */
    public function isInclude (array $areaId, array $express, BaseModel $freightModelMonery)
    {
      
        if (!$this->isEmpty($areaId) || !$this->isEmpty($express) || !($freightModelMonery instanceof BaseModel)) {
            return array();
        }
        
        $expressData = S('expressDataCache');
        
        if (empty($expressData)) {
            
            $id = str_replace('"', null, Tool::characterJoin($express, 'freightCondition'));
            
            if (empty($id)) {
                return array();
            }
            
            $data = $this
            ->where(self::$freightId_d .' in ('.$id.') and '.self::$mailArea_d .' in ('. implode(',', $areaId).')')
            ->order('SUBSTRING_INDEX("'.$id.'",'.self::$freightId_d.', 1)')
            ->select();
             
            if (empty($data)) {//不再包邮地区内
                return $express;
            }
             
            //指定条件包邮 包几件
            if ( ($id = intval($this->isFreeShipping)) === 0) {
                throw new \Exception('商品数量不正确');
            }
            
            $num = $this->isFreeShipping;
            
            $monery = 0;
            
            $object = $this->objOther;
            
            foreach ($data as $key => $value) {//待优化
            
                foreach ($express as $name => & $flag ) {
            
                    if ($flag['freightCondition'] !== $value[self::$freightId_d] ) {//是按件 、还是 重量、  体积
                        continue;
                    }
            
                    if ( ($pNum =$num-$value[$object::$continuedThing_d]) < 0) {//件数
                        $pNum = 1;
                    }
            
                    if ( ($pWeight =$num-$value[$object::$firstWeight_d]) < 0) {// 重量
                        $pWeight = 1;
                    }
            
                    if ( ($pVolum =$num-$value[$object::$fristVolum_d]) < 0) {
                        $pVolum = 1;
                    }
                     
                    switch ((int)$flag['valuation_method']) {//计价方式计价方式(1:按件 2:按重量 3:按体积)
                        case 1: //        //首费                                                           //增件数                          //增值费
                            $monery = $flag[$object::$fristMoney_d] + $pNum* $flag[$object::$continuedMoney_d];
                            $flag[$object::$fristMoney_d] =  $monery- $flag[$freightModelMonery::$mailArea_num_d] * $flag[$freightModelMonery::$mailArea_monery_d];
                            break;
                        case 2:
                             
                            $monery = $flag[$object::$fristMoney_d] + $pWeight* $$flag[$object::$continuedMoney_d];
                            $flag[$object::$fristMoney_d] =  $monery- $flag[$freightModelMonery::$mailArea_wieght_d] * $flag[$freightModelMonery::$mailArea_monery_d];
                            break;
            
                        case 3:
                            $monery = $flag[$object::$fristMoney_d] + $pVolum* $flag[$object::$continuedMoney_d];
                            $flag[$object::$fristMoney_d] =  $monery- $flag[$freightModelMonery::$mailArea_volume_d] * $flag[$freightModelMonery::$mailArea_monery_d];
                            break;
                    }
                    
                    $flag[$object::$fristMoney_d] = $flag[$object::$fristMoney_d] < 0 ? 0 : $flag[$object::$fristMoney_d];
                }
            }
            S('expressDataCache', $express, 5);
        }
        return S('expressDataCache');//包含该地区
    }
    /**
     * @return the $isFreeShipping
     */
    public function getIsFreeShipping()
    {
        return $this->isFreeShipping;
    }
    
    /**
     * @param number $isFreeShipping
     */
    public function setIsFreeShipping($isFreeShipping)
    {
        $this->isFreeShipping = $isFreeShipping;
    }
    /**
     * @return the $typeFree
     */
    public function getTypeFree()
    {
        return $this->typeFree;
    }
    
    /**
     * @param number $typeFree
     */
    public function setTypeFree($typeFree)
    {
        $this->typeFree = $typeFree;
    }
    
    /**
     * @return the $objOther
     */
    public function getObjOther()
    {
        return $this->objOther;
    }
    
    /**
     * @param field_type $objOther
     */
    public function setObjOther(BaseModel $objOther)
    {
        if (!($objOther instanceof BaseModel)) {
            throw new \Exception('数据类型错误');
        }
        $this->objOther = $objOther;
    }
    
}