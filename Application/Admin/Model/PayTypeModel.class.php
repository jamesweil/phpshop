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
 * 支付 类型 
 * @author 王强
 * @version 1.0.1
 */
class PayTypeModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//id

	public static $typeName_d;	//支付类型

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

	public static $status_d;	//1开启 0 关闭

	public static $isDefault_d;	//是否默认

	public static $isSpecial_d;	//特殊支付方式 0 不是 1 是

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  static::$obj= !(static::$obj instanceof $class) ? new static() : static::$obj;
    }
    
    /**
     * 获取已开启的支付类型 
     */
    public function getPay()
    {
        $data = S('payType');
        
        if (empty($data)) {
            $data = $this->getField(static::$id_d.','.static::$typeName_d);
            S('payType', $data, 15);
        }
        return $data;
    }
    
    /**
     * 获取id和name
     */
    public function getIdAndName ()
    {
        return $this->where(self::$isSpecial_d.'!= 1')->getField(self::$id_d.', '.self::$typeName_d);
    }
}