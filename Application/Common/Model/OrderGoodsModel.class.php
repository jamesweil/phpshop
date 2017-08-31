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
use Common\Tool\Tool;

// +----------------------------------------------------------------------
// | 订单数量模型
// +----------------------------------------------------------------------
// | Another ：王强
// +----------------------------------------------------------------------

class OrderGoodsModel extends BaseModel
{
    private static $obj;

	const ReturnOrderStatus = 0x05; //退货表示

	private $curretOrderId = 0;

	public static $id_d;	//id

	public static $orderId_d;	//商品id

	public static $goodsId_d;	//商品编号

	public static $goodsNum_d;	//商品数量

	public static $goodsPrice_d;	//

	public static $status_d;	//-1：取消订单；0 未支付，1已支付，2，发货中，3已发货，4已收货，5退货审核中，6审核失败，7审核成功，8退款中，9退款成功

	public static $spaceId_d;	//商品规格id

	public static $userId_d;	//用户id

	public static $comment_d;	//是否已评价（0未评价1已评价）

	public static $over_d;	//是否已完成该单(0未完成 1已完成）

	public static $wareId_d;	//所在仓库

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    /**
     * 根据订单编号查询商品编号  
     */
    public function getGoodsIdByOrderId($orderId, $field = 'goods_id')
    {
        if (($orderId = intval($orderId)) === 0)
        {
            return array();
        }
        
        return $this->field($field)->where('order_id = %d', $orderId)->select();
    }
    
    /**
     * {@inheritDoc}
     * @see \Think\Model::add()
     */
    
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
     * 根据父类表信息查询数据 ，传递给商品表 
     */
    public function getGoodsInfoByOrder(array $data)
    {
        if (empty($data))
        {
            return array();
        }
        
        //整合编号
        $orderIds = Tool::characterJoin($data, 'order_id');
       
        $orderGoods = $this->field('order_id,goods_id,goods_num')->where('order_id in ('.$orderIds.')')->order('order_id DESC')->select();
       
        if (empty($orderGoods))
        {
            return array();
        }
        
        $parseOrder = array();
        
        foreach ($orderGoods as $value)
        {
            if (!isset($parseOrder[$value['order_id']]))
            {
                $parseOrder[$value['order_id']] = $value;
            }
            else
            {
                if (strpos($parseOrder[$value['order_id']]['goods_id'], ',') === false)
                {
                    $goodsId = $parseOrder[$value['order_id']]['goods_id'];
                }
                $parseOrder[$value['order_id']]['goods_id'] .= ','.$value['goods_id'];
                $parseOrder[$value['order_id']]['goods_num'] .= ','.$value['goods_id'].':'.$value['goods_num'];
            }
        }
        
        foreach ($parseOrder as $key => &$value)
        {
            if (strpos($value['goods_id'], ',') !== false)
            {
                $id = $value['goods_num']; 
                
                $newId = $goodsId.':'.$id;
                
                $value['goods_num'] = $newId;
            }
        }
        return $parseOrder;
    }
    
    /**
     * 获取商品编号 
     */
    public function getGoodsId($data, $field, $filter = FALSE)
    {
        if (empty($data['id']) || empty($field))
        {
            return array();
        }
        //整合编号
        return $orderGoods = $this->field($field, $filter)->where('order_id in ('.$data['id'].')')->order('order_id DESC')->select();
    }
    
    /**
     * 删除用户的购买记录 
     */
    public function deleteOrderGoodsByUserId(array $order, $id)
    {
        if (empty($order) || !is_array($order) ||empty($id)) {
            return false;
        }
        
        $ids = Tool::characterJoin($order, $id);
        
        if (empty($ids)) {
            return false;
        }
        
        return $this->delete(array(
           'where' => array(self::$orderId_d => array('in', $ids))
        ));
    }
    /**
     * 获取退货金额 
     */
    public function getMonery($orderId, $goodsId)
    {
        if (($id = intval($orderId)) === 0 || ($type = intval($goodsId)===0) ) {
            return null;
        }
        $monery = $this->where(self::$orderId_d.'=%d and '.self::$goodsId_d.'=%d', [$orderId, $goodsId])->getField(self::$goodsPrice_d);
        return $monery;
    }
    
    /**
     * 修改状态 
     */
    public function editStatus ($orderId, $goodsId, $status)
    {
        if ( ($orderId = intval($orderId)) === 0 || ($goodsId = intval($goodsId)) === 0 || empty($status)) {
             $this->rollback();
             return false;
        }
        //[$goodsId, $orderId]后进先出
        $status = $this->where(self::$orderId_d .'= %d and '.self::$goodsId_d .'= %d', [$goodsId, $orderId])->save([self::$status_d => '9']);
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        $this->commit();
        
        return $status;
    }
    
    /**
     * 验证退货款状态 
     */
    public function getIsStatus($goodsId, $orderId, $status)
    {
        
        $orderStatus = $this->getStatus($goodsId, $orderId);
       
        return $orderStatus == $status ? true : false;
    }
    
    /**
     * 修改订单子商品的状态 
     */
    public function editOrderGoodsStatus ($orderId) 
    {
        $status = $this->updateOrderGoodsStatus($orderId);
        
        if ($status === false) {
            $this->rollback();
            return false;
        }
        
        $this->commit();
        
        return $status;
        
    }
    
    /**
     * 更新订单商品状态
     */
    public function updateOrderGoodsStatus ($orderId)
    {
        if ( ($orderId = intval($orderId)) === 0) {
            $this->rollback();
            return false;
        }
        
        $status = $this->where(self::$orderId_d.'=%d', $orderId)->save([
            self::$status_d => 1
        ]);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        return $status;
    }
    
    /**
     * 获取状态
     */
    public function getStatus ($goodsId, $orderId)
    {
        if (!$this->foreachDataTypeIsEmpty(func_get_args())) {
            //数据错误
            $this->error = '数据错误';
            return array();
        }
         
        $orderStatus = $this->where(self::$orderId_d .'= %d and '.self::$goodsId_d .'= %d', [$goodsId, $orderId])->getField(self::$status_d);
        
        return $orderStatus;
    }
    
    /**
     * 根据订单号获取商品数据
     */
    public function getGoodsDataByOrderId ($orderId)
    {
        if ( ($orderId = intval($orderId)) === 0) {
            return [];
        }
            $field = self::$orderId_d.','.self::$goodsNum_d.','.self::$goodsId_d.','.self::$goodsPrice_d.','.self::$userId_d;//新增几个查询字段


        $data = $this->field($field)->where(self::$orderId_d.'=%d', $orderId)->select();
        
        return $data;
    }
    /**
     * 获取订单退货商品编号  
     * @param  $orderId 订单编号
     * @return array
     */
    public function getOrderReturnGoodId ($orderId)
    {
        if ( ($orderId = intval($orderId)) === 0) {
            return NULL;
        }
        return $this->field(self::$wareId_d, true)->where(self::$orderId_d.'=%d and '.self::$status_d.'= "'.self::ReturnOrderStatus.'"', $orderId)->select();
    }
    
    /**
     * 修改状态
     * @param int $orderId
     * @param string $goodsIdString
     * @return boolean
     */
    public function editManyStatus ($orderId, $goodsIdString)
    {
        if ( ($orderId = intval($orderId)) === 0 || !empty($goodsIdArray)) {
            $this->rollback();
            return false;
        }
        
        $status = $this->where(self::$orderId_d.'=%d and '.self::$goodsId_d.' in ('.$goodsIdString.')', $orderId)->save([
            self::$status_d => '9',
        ]);
        
        if (!$this->traceStation($status)) {
            $this->rollback();
            return false;
        }
        
        $this->commit();
        return $status;
    }
}