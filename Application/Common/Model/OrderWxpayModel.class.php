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

use Think\Model;

/**
 * 微信支付 凭据模型 
 * @author 王强
 * @version 1.0.1
 */
class OrderWxpayModel extends BaseModel 
{
    private static $obj ;

	


	public static $id_d;	//编号

	public static $orderId_d;	//订单号

	public static $wxPay_id_d;	//支付码

	public static $status_d;	//0支付失败 1 支付成功


	public static $type_d;	//支付类型 0 商品支付， 1余额充值

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    protected function _before_insert(&$data, $options)
    {
        $data[static::$status_d]  = 0;
        return $data;
    }
    
    
    public function add($data='', $options=array(), $replace=false)
    {
        if (empty($data))
        {
            return false;
        }
        $data = $this->create($data);
        return parent::add($data, $options, $replace);
    }
    /**
     * 获取凭据 
     */
    public function getOrderWx($id, $where = ' and status = 1 ')
    {
        if (!is_numeric($id)) {
            return array();
        }
        return $this->field('order_id, wx_pay_id')->where('order_id = "%s" and '.self::$type_d.'= 0 ' .$where, $id)->find();
    }
    /**
     * 失败更新支付码 
     */
    public function alipayError($id, array $data)
    {
        if (!is_numeric($id) || !is_array($data) || empty($data['order_id'])) {
            return array();
        }
        
        $isHave = $this->getOrderWx($id, ' and '.self::$type_d.' = '.$data[self::$type_d]);
        
        $status = false;
        if (empty($isHave)) {
            
            $status = $this->add($data);
        } else {
            $status = $this->where('order_id = "%s"', $data['order_id'])->save($data);
        }
        
        return $status;
    }
    
    /**
     * 支付回调更新
     */
    public function nofityUpdate (array $param)
    {
        if (empty($param)) {
            $this->rollback();
            return false;
        }
        
        $orderId = $param['wx_order_id'];
        
        $type    = $param['type'];
        
        $status = $this->save(array(
            self::$status_d => 1
        ), array(
            'where' => array(
                self::$orderId_d => $orderId,
                self::$type_d    => $type
            )
        ));
    
        if (!$this->traceStation($status)) {
            return false;
        }
        return $status;
    }
}