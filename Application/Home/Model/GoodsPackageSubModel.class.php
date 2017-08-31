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
 * 套餐模型 
 */
class GoodsPackageSubModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//

	public static $packageId_d;	//套餐id

	public static $goodsId_d;	//商品id

	public static $discount_d;	//商品套餐价
    
	/**
	 * @var string 字段别名
	 */

	private $fieldAs;
    
    /**
     * @return the $fieldAs
     */
    public function getFieldAs()
    {
        return $this->fieldAs;
    }

    /**
     * @param field_type $fieldAs
     */
    public function setFieldAs($fieldAs)
    {
        $this->fieldAs = $fieldAs;
    }

    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    /**
     * 获取 套餐 
     */
    public function getPackageByCart(array $cart, $split) 
    {
        if (!$this->isEmpty($cart) || empty($split)) {
            return array();
        }
        
        $idString = Tool::characterJoin($cart, $split);
        
        if (empty($idString)) {
            
            $this->error = '套餐数据有误';
            
            return array();
        }
        $data = $this->field(self::$goodsId_d/*.','.self::$discount_d.self::DBAS.$this->fieldAs*/)->where(self::$packageId_d .' in ('.$idString.')')->select();
       
        if (empty($data)) {
            return array();
        }
        
        $number = self::$number;
        
        $number += count($data);
        
        self::$number = $number;
        return (array)$data;
    }
}