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
use Common\Tool\Tool;
use Common\Model\BaseModel;

// +----------------------------------------------------------------------
// | 订单数量模型
// +----------------------------------------------------------------------
// | Another ：王强
// +----------------------------------------------------------------------

class OrderGoodsModel extends BaseModel
{
    private static $obj;

	public static $id_d;

	public static $orderId_d;

	public static $goodsId_d;

	public static $goodsNum_d;

	public static $goodsPrice_d;

	public static $status_d;

	public static $spaceId_d;


	public static $userId_d;

	public static $comment_d;

	public static $over_d;


	public static $wareId_d;	//所在仓库


	public static $type1_d;	//type0为对应单品赠品 里面数据为gift表里的id 值：多条id

	public static $type0_d;	//type0为对应满赠 里面数据为gift表里的id 值：单条id

    
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
        if (empty($orderId) || !is_numeric($orderId))
        {
            return array();
        }
        
        return $this->field($field)->where('order_id = %s', $orderId)->select();
    }
    
    protected function _before_insert(& $data, $options)
    {
        $data[self::$status_d] = 0;
        return $data;
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
       
        $orderGoods = $this->field('order_id,goods_id,goods_num,comment,status')->where('order_id in ('.$orderIds.')')->order('order_id DESC')->select();
        if (empty($orderGoods))
        {
            return array();
        }
      
        $parseOrder = array();
         
        /**
         * 合并数组[相同的订单号]
         */
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
                    $goodsId[$value['order_id']] = $parseOrder[$value['order_id']]['goods_id'];
                }
                $parseOrder[$value['order_id']]['goods_id'] .= ','.$value['goods_id'];
                $parseOrder[$value['order_id']]['goods_num'] .= ','.$value['goods_id'].':'.$value['goods_num'];
                $parseOrder[$value['order_id']]['comment'] .= ','.$value['goods_id'].':'.$value['comment'];
                $parseOrder[$value['order_id']]['status'] .= ','.$value['goods_id'].':'.$value['status'];
            }
        }
      
        //问题在这[拼接的时候错误现在可以了不, 好了不]

        foreach ($parseOrder as $key => & $value)
        {
            if (array_key_exists($key, $goodsId) )
            {
                $id = $value['goods_num']; 
                // showData($id);
                $newId = $goodsId[$key].':'.$id;                
                $value['goods_num'] = $newId;
                $comment = $value['comment'];
                $new = $goodsId[$key].':'.$comment;                
                $value['comment'] = $new;
                $status = $value['status'];
                $new = $goodsId[$key].':'.$status;                
                $value['status'] = $new;
            }
        }
        return $parseOrder;
    }
    /**
     * 添加订单商品 
     */
    public function addOrderGoods(array $post, $insertId)
    {
        if (!$this->isEmpty($post) || ($insertId = intval($insertId)) === 0) {
            $this->rollback();
            return false;
        }
        
        $addOrder = array();
        
        foreach ($post as $key => $value) {
            if (empty($value)) {
                $this->rollback();
                return false;
            }
            $addOrder[$key][self::$goodsId_d]  = $value[self::$goodsId_d];
            $addOrder[$key][self::$goodsNum_d] = $value[self::$goodsNum_d];
            $addOrder[$key][self::$goodsPrice_d] = $value[self::$goodsPrice_d];
            $addOrder[$key][self::$orderId_d]    = $insertId;
        }
        sort($addOrder);
        $status = $this->addAll($addOrder);
        
        if ($status === false) {
            $this->rollback();
            return false;
        }
        return $status;
    }
    //查询订单商品表信息
    public function getOrderGoodsByOrder(array $order){
        if (empty($order)) {
            return false;
        }
        foreach ($order as $key => $value) {           
            $where['order_id'] = $value['id'];
            $res = M('Order_goods')->field('id,order_id,goods_id,goods_num,goods_price,comment')->where($where)->select();
            $order[$key]['goods'] = $res;
        } 
        return $order;       
    }
    //查询订单商品表信息
    public function getOrderGoodsByGoodsId($goods_id,$order_id){
        if (empty($goods_id)) {
            return false;
        }
        if (empty($order_id)) {
            return false;
        }
        $where['goods_id'] = $goods_id;
        $where['order_id'] = $order_id;
        $field = 'goods_id,goods_num,goods_price,user_id';
        $res = M('Order_goods')->where($where)->find();
        return $res;
    }
    //查询订单商品表信息
    public function getOrderGoodsByData(array $data){
        if (empty($data)) {
            return false;
        }
        foreach ($data as $key => $value) {           
            $where['order_id'] = $value['order_id'];
            $res = $this->field('id,order_id,goods_id,goods_num,goods_price,comment,status')->where($where)->select();
            $data[$key]['goods'] = $res;
            if (empty($data[$key]['goods'])) {
                unset($data[$key]);
            }
        } 
        return $data;       
    }
    //查询订单中退货(退款)商品信息
    public function getReturnPriceGoods($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        $where['user_id'] = $user_id;
        $where['status']  = array('GT','4');
        $_GET['P'] = empty($_GET['P'])?0:$_GET['P'];
        $data = $this->where($where)->page($_GET['p'].',5')->select();
        $count = $this->where($where)->count();
        $Page = new \Think\Page($count,5);
        $page = $Page->show();
        return array('data'=>$data,'page'=>$page);
    }
}