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
 * 发货地址列表 
 */
class SendAddressModel extends BaseModel
{
    private static $obj;
    

	public static $id_d;

	public static $addressId_d;

	public static $addressDetail_d;

	public static $createTime_d;

	public static $updateTime_d;

	public static $status_d;

	public static $stockName_d;

	public static $default_d;

    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    public function getStock ()
    {
        $address = S('address');
        
        if (empty($address)) {
            $address = $this->where(self::$status_d.'=1 and '.self::$default_d.' = 0')->getField(self::$id_d.','.self::$stockName_d);

            S('address', $address, 10);
        }
        return $address;
    }
   
    /**
     * 获取默认地址 
     */
    public function getDefault ()
    {
         $address = S('def');
        
        if (empty($address)) {
            $address =  $this->getAttribute(array(
                'field' => array(self::$id_d,self::$stockName_d),
                'where' => array(self::$default_d => 1)
            ), false, 'find');
            S('def', $address, 5);
        }
        return $address;
    }

    //查询单个仓库
    public function getWareDetailsBYId($ware_id){
        if (empty($ware_id)) {
            return false;
        }
        $where['id'] = $ware_id;
        $field = 'id,address_id,address_detail,stock_name';
        $res = $this->field($field)->where($where)->find();
        return $res;
    }
}