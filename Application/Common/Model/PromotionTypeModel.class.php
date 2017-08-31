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

namespace Common\Model;

use Common\Model\BaseModel;
use Common\Tool\Tool;

/**
 * 促销类型model
 */
class PromotionTypeModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//

	public static $promationName_d;	//促销类型

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

	public static $status_d;	//0 打折，1,减价优惠,2,固定金额出售
    
	const DiscountPromotions = 0; //打折促销
	
	const Discount = 1; //减价优惠
	
	const FixedAmountSale = 2; //固定金额出售
    
	
    /**
     * @param array: $goodsData
     */
    public function setGoodsData(array $goodsData)
    {
        $this->goodsData = $goodsData;
    }

    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    /**
     * 根据其他模型数据 获取促销类型数据
     * @param array $data
     * @param string $split
     * @return array
     */
    public function getTypeData (array $data, $split)
    {
       
        if (!$this->isEmpty($data) || !is_string($split)) {
            return array();
        }
        
        $idString = Tool::characterJoin($data, $split);
       
        if (empty($idString)) { 
            return $data;
        }
        
        $typeData = $this->where(self::$id_d .' in ('.$idString.')')->select();
        
        if (empty($typeData)) {
            return $data;
        }
        
        $typeData = $this->covertKeyById($typeData, self::$id_d);
        
        foreach ($data as $key => & $value)
        {
            if (array_key_exists($value[$split], $typeData)) {
                
                $value[self::$promationName_d] = $typeData[$value[$split]][self::$promationName_d];
                $value['poopStatus']           = $typeData[$value[$split]][self::$status_d];
            }
            if ($value[$split] == -1) {
                $value[self::$promationName_d] = '买就送代金券';
                $value['poopStatus'] = -1;
            }
        }
        unset($typeData);
        return $data;
    }
    
    /**
     * 获取促销类型
     * @param int $id id编号
     * @return mixed|boolean|NULL|string|unknown|object
     */
    public function getPromotionType ($id)
    {
        if (($id = intval($id)) === 0) {
            return array();
        
        }
        
        $field = [self::$createTime_d, self::$updateTime_d];
        
        return $this->field($field, true)->find($id);
    }
}