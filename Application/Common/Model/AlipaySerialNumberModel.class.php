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


/**
 * 支付宝流水号模型 
 */
class AlipaySerialNumberModel extends BaseModel
{
    /**
     * 当前对象 
     */
    private static $obj;

	public static $id_d;	//支付宝订单退款编号

	public static $orderId_d;	//订单号

	public static $alipayCount_d;	//支付宝流水号


	public static $type_d;	//支付类型 0 商品支付， 1余额充值

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
 /**
     * 添加流水号 
     * @param array $data 订单流水数据
     * @return bool
     */
    public function addSerialNumber (array $data)
    {
        if (!$this->isEmpty($data)) {
            $this->rollback();
            return false;
        }
        
        $status = $this->addParse($data);
        
        if (empty($status)) {
            return false;
        }
        
        $this->commit();
        
        return $status;
    }
    
    /**
     * 添加处理操作
     */
    public function addParse (array $data)
    {
        if (empty($data)) {
            $this->rollback();
            return false;
        }
        
        $type = $data['type'];
        
        $seril = array(
            self::$orderId_d     => $data['order_sn_id'],
            self::$alipayCount_d => $data['trade_no'],
            self::$type_d        => $type
        );
         
        $status = $this->add($seril);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        
        return $status;
    }
    
    
    /**
     * 获取 流水号 
     */
    public function getSerial($orderId)
    {
        if (($orderId = intval($orderId)) === 0) {
            return false;
        }
        return $this->where(self::$orderId_d.'=%d', $orderId)->find();
    }
    
    /**
     * 获取订单编号
     * @param string $serailNumber 流水号 
     */
    public function getOrderId ($serailNumber) {
        if (empty($serailNumber)) {
            return null;
        }
        return $this->where(self::$alipayCount_d.'="%s"', $serailNumber)->getField(self::$orderId_d);
    }
}