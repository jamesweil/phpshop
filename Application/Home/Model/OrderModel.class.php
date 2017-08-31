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
use Common\TraitClass\callBackClass;
use Think\Page;
use Common\Model\BaseModel;

/**
 * 订单模型 
 */
class OrderModel extends BaseModel
{
    use callBackClass;
    
    // -1:取消订单,0 未支付，1已支付，2，发货中，3已发货，4已收货，5退货审核中，6审核失败，7审核成功，8退款中，9退款成功, 10：代发货，11待收货
    const CancellationOfOrder = -1;
    
    const NotPaid = 0;
    
    const YesPaid = 1;
    
    const InDelivery = 2;
    
    const AlreadyShipped = 3;
    
    const ReceivedGoods = 4;
    
    const ReturnAudit = 5;
    
    const AuditFalse  = 6;
    
    const AuditSuccess = 7;
    
    const Refund = 8;
    
    const ReturnMonerySucess = 9;
    
    const ToBeShipped = 1;
    
    const ReceiptOfGoods = 3;
    
    
    private static $obj ;

	public static $id_d;

	public static $orderSn_id_d;

	public static $priceSum_d;

	public static $expressId_d;

	public static $addressId_d;

	public static $userId_d;

	public static $createTime_d;

	public static $deliveryTime_d;

	public static $payTime_d;

	public static $overTime_d;

	public static $orderStatus_d;

	public static $commentStatus_d;

	public static $freightId_d;

	public static $wareId_d;

	public static $payType_d;


	public static $remarks_d;


	public static $status_d;


	public static $translate_d;


	public static $shippingMonery_d;

	public static $expId_d;


	public static $platform_d;	//平台：0代表pc，1代表app


	public static $isInvoice_d;	//是否索要发票 0否 1是


	public static $shipping_d;	//配送方式


	public static $integral_d;	//积分金额。如果不为0则为积分订单

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return self::$obj = !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    //备用
    protected function _before_update( &$data, $options)
    {
//         $data['update_time'] = time();
//         return $data;
    }
     
    protected function _before_insert(&$data, $options)
    {
        Tool::connect('Token');
        $data[self::$createTime_d] = time();
        $data[self::$orderSn_id_d] = Tool::toGUID();
        $data[self::$userId_d]     = $_SESSION['user_id'];
        $_SESSION['order_sn_id'] = $data[self::$orderSn_id_d];
        return $data;
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
     * {@inheritDoc}
     * @see \Think\Model::save()
     */
    
    public function save($data='', $options=array())
    {
        if (empty($data))
        {
            return false;
        }
        $data = $this->create($data);
    
        return parent::save($data, $options);
    }
    
    /**
     * 根据订单号获取商品编号 
     */
    public function getGoodsByOrderSn($orderSn)
    {
        if (empty($orderSn) || !is_numeric($orderSn))
        {
            return false;
        }
        
        return $this->where(self::$orderSn_id_d.' = "%s"', $orderSn)->getField('price_sum');
    }
    /**
     * 获取 该用户下的全部订单 
     */
    public function getOrderByUser(array $whereValue, $status = null, $field = null, $default = 'select')
    {
        $field = $field === null ?  $this->getDbFields() : $field;
        if (is_array($field) && isset($field[0]))
        {
            $field[0] = 'id as order_id';
            
            foreach ($field as $key => $value)
            {
                if ('user_id' === $value)
                {
                    unset($field[$key]);
                }
            }
        }        
        $where = $status === null ? null : $status;
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $data =  $this
            ->field($field)
            ->where('user_id = "%s"'.$where, $whereValue)->order('id DESC')
            ->page($_GET['p'].',5')
            ->$default();
        $count = $this->where('user_id = "%s"'.$where, $whereValue)->count();
        $Page = new \Think\Page($count,5);
        $page = $Page->show();
        return array('data'=>$data,'page'=>$page,'count'=>$count);
    }
    
    /**
     * 获取订单状态 
     */
    public function getOrderStatusByUser($id, $filed='id')
    {
        if (!is_numeric($id) || empty($filed))
        {
            return false;
        }
        
        return $this->where($filed.' = "%s"', $id)->getField('order_status');
    }
    /***
    根据id查询退货订单表信息
    */
    public function getReturnGoodsById($id){
        if (empty($id)) {
            return false;
        }
        $field = 'id,goods_id,order_id';
        $res = M('order_return_goods')->field($field)->where('id='.$id)->find();
        return $res;
    }
    /***
    根据订单id查询单个订单信息
    */
    public function getOrderByOrderId($order_id){
        if (empty($order_id)) { 
            return false;
        }
        $order = M('order')->where('id='.$order_id)->find();
        return $order;
    }
    //根据订单id查询卖家留言
    public function getMessageByOrderId($order_id){
        if (empty($order_id)) { 
            return false;
        }
        $where['order_id'] = $order_id;
        $field = 'id,content,create_time';
        $res = M('Message')->field($field)->where($where)->find();
        return $res;
    }
    //根据商品goods_id查询订单商品表信息
    public function getGoodsByGoodsId($goods_id,$order_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        if (empty($goods_id)) {
            return false;
        }
        if (empty($order_id)) {
            return false;
        }
        $where['o.user_id']  = $user_id;
        $where['g.goods_id'] = $goods_id;
        $where['g.order_id'] = $order_id;

        // 获取购物车信息
        $field = 'o.id as order_id, g.goods_price, g.goods_num, g.goods_id ';
        $goods = M('order_goods')->alias('g')->join('db_order as o ON o.id=g.order_id')
            ->field($field)->where($where)->find();

        // 获取商品信息
        $info = M('goods')->field('title')->find($goods_id);
        if (!empty($info)) {
            $temp  = D('goods')->image($$goods_id);
            $goods = array_merge($info, ['pic_url' => $temp], $goods);
        }
        return $goods;
    }
    //根据order_id查询订单商品信息
    public function getGoodsByOrderId($order_id){
        if (empty($order_id)) {
            return false;
        }
        $field = 'goods_price,goods_num,space_id,goods_id,order_id,goods_id,status';
        $goods = M('order_goods')->field($field)->where('order_id='.$order_id)->select();
        return $goods;
    }
    //根据数组查询订单信息
    public function getOrderByData(array $data,$where){
        if (empty($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $where['user_id'] = $_SESSION['user_id'];
            $where['id'] = $value['order_id'];
            $where['status'] = array('neq',1);
            $field = 'id,order_sn_id,create_time,price_sum,express_id,order_status,shipping_monery,exp_id';
            $res = M('order')->field($field)->where($where)->find();
            $data[$key]['create_time'] = $res['create_time'];
            $data[$key]['order_sn_id'] = $res['order_sn_id'];
            $data[$key]['price_sum'] = $res['price_sum'];
            $data[$key]['express_id'] = $res['express_id'];
            $data[$key]['order_status'] = $res['order_status'];
            $data[$key]['exp_id'] = $res['exp_id'];
            $data[$key]['shipping_monery'] = $res['shipping_monery'];
            if (empty($data[$key]['order_sn_id'])) {
                unset($data[$key]);
            }

        }      
        return $data;
    }   
    //根据订单商品表查询对应的商品信息
    public function getGoodsNameByOrderGoods($order_goods){
        if (empty($order_goods)) {
            return false;
        }
        $where['id']             = $order_goods['goods_id'];
        $name                    = M('Goods')->field('class_id,title,p_id,price_member')->where($where)->find();
        $order_goods['title']    = $name['title'];
        $order_goods['class_id'] = $name['class_id'];
        $order_goods['p_id']     = $name['p_id'];
        $order_goods['price_member'] = $name['price_member'];
        return $order_goods;
    }
    // //根据订单查询运费
    public function getFreightByOrderId(array $order){
        if(empty($order)) {   
            return false;
        }
        $where['freight_id'] = $order['freight_id'];
        $res = M('freight_condition')->field('id,mail_area_monery')->where($where)->find();
        $order['mail_area_monery'] = $res['mail_area_monery'];           
        return $order;
    }
    // //根据Data订单查询运费
    public function getFreightByData(array $order){
        if(empty($order)) {   
            return false;
        }
        foreach ($order as $key => $value) {
            $where['freight_id'] = $value['freight_id'];
            $res = M('freight_condition')->field('id,mail_area_monery')->where($where)->find();
            $order[$key]['mail_area_monery'] = $res['mail_area_monery'];
        }
                   
        return $order;
    }
    // //根据订单商品表查询订单信息
     public function getOrderDetailsByOrderId(array $data){
        if (empty($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $where['id'] = $value['order_id'];
            $where['user_id'] = $_SESSION['user_id'];
            $field = 'id,order_sn_id,create_time,price_sum,express_id,order_status,shipping_monery,order_type';
            $res = $this->field($field)->where($where)->find();
            $data[$key]['create_time'] = $res['create_time'];
            $data[$key]['order_sn_id'] = $res['order_sn_id'];
            $data[$key]['price_sum'] = $res['price_sum'];
            $data[$key]['express_id'] = $res['express_id'];
            $data[$key]['order_status'] = $res['order_status'];
            $data[$key]['shipping_monery'] = $res['shipping_monery'];
            $data[$key]['order_type'] = $res['order_type'];
            if (empty($data[$key]['order_sn_id'])) {
                unset($data[$key]);
            }
        }
        return $data;
    }
    // //根据订单查询快递公司名
    public function getExpressTitleByFreightId(array $order){
        if(empty($order)) {   
            return false;
        }   
        $where['id'] = $order['exp_id'];
        $res = M('express')->field('id,name,tel,code')->where($where)->find();
        $order['express_title'] = $res['name'];
        $order['tel'] = $res['tel'];  
        $order['code'] = $res['code'];           
        return $order;
    }
    
    //根据order_id查询售后信息
    public function getCheckByOrderId($order_id){
        if (empty($order_id)) {
            return false;
        }
        $res = M('order_return_goods')->where('order_id='.$order_id)->page($_GET['p'].',5')->select();
        $count =  M('order_return_goods')->where('order_id='.$order_id)->count();     // 查询满足要求的总记录数

        $page = new \Think\Page($count,5);      // 实例化分页类 传入总记录数和每页显示的记录数

        $show = $page->show();      // 分页显示输出
        return array('res' =>$res, 'page' => $show);
    }
    //根据id查询退单详情
    public function getCheckDetailByOrderId($id){
        if (empty($id)) {
            return false;
        }
        $res = M('order_return_goods')->where('id='.$id)->find();
        return $res;
    }
    //查询订单记录条数
    public function getOrderCountByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        //总记录数
        $where['user_id'] = $user_id;
        $where['order_status'] = array('neq','-1');
        $where['status'] = '0';     
        $count = $this->where($where)->count();//总记录数
        //待付款记录数
        $payments['user_id'] = $user_id;
        $payments['order_status'] ='0';
        $payments['status'] ='0';
        $payment_count = $this->where($payments)->count();//待付款记录数
        //待发货记录数
        $delivery['user_id'] = $user_id;
        $delivery['order_status'] ='1';
        $delivery_count = $this->where($delivery)->count();
        //待收货记录数
        $receiving['user_id'] = $user_id;
        $receiving['order_status'] ='3';
        $receiving_count = $this->where($receiving)->count();
        //待评价记录数
        $comment['user_id'] = $user_id;
        $comment['order_status']='4';
        $comment['comment_status']='0';
        $comment['status']='0';
        $comment_count = $this->where($comment)->count();
        //取消订单记录
        $cancel['user_id']  = $user_id;
        $cancel['order_status'] = '-1';
        $cancel['status'] = '0';
        $cancel_count =  $this->where($cancel)->count();
        //退款订单记录
        $return['user_id'] = $user_id;
        $return['status'] = array('gt','4');
        $return_count =  M('order_goods')->where($return)->count();
        //订单回收站记录
        $recycle['user_id'] = $user_id;
        $recycle['status'] = '1';
        $recycle_count =  $this->where($recycle)->count();
        return array('count' =>$count,'payment_count' => $payment_count,'delivery_count'=>$delivery_count,'receiving_count'=>$receiving_count,'comment_count'=>$comment_count,'cancel_count'=>$cancel_count,'recycle_count'=>$recycle_count,'return_count'=>$return_count);
    }
    
    /**
     * 修改状态 
     */
    public function saveStatus($orderId)
    {
        if (($orderId = intval($orderId)) === false) {
            $this->error = '参数错误';
            return false;
        }
        
        $param = [
            self::$id_d => $orderId,
            self::$payTime_d => time(),
            self::$orderStatus_d => self::YesPaid
        ];
        
        return $this->save($param);
    }
    
    /**
     * 支付回调成功后 修改订单状态 
     */
    public function paySuccessEditStatus ($orderId)
    {
        if (($orderId = (int)$orderId) === 0) {
            return false;
        }
        $this->startTrans();
        
        $status = $this->saveStatus($orderId);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        
        return $status;
    }
    
    /**
     * 生成订单 
     */
    public function addOrder (array $post)
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        $this->startTrans();
        
        $status = $this->add($post);
        
        return $status;
    }
    
    /**
     * 获取订单信息 
     */
    public function getOrderInfoById ($id)
    {
        if (($id = intval($id)) === 0) {
            return array();   
        }
        
        return $this->field(self::$createTime_d, true)
                ->where(self::$id_d.'="%s"', $id)->find();
    }
    
    //查询待评价订单信息
    public function getPendingEvaluation($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        $where['user_id'] = $user_id;
        $where['order_status'] = '4';
        $where['comment_status'] = '0';
        $where['status'] = '0';
        $_GET['p']=empty($_GET['p'])?0:$_GET['p'];
        $res = M('Order')->where($where)->page($_GET['p'].',5')->select();
        $count = M('Order')->where($where)->count();
        $Page = new \Think\Page($count,5);
        $page = $Page->show();
        return array('res'=>$res,'page'=>$page);
    }
    //查询用户所有订单信息
    public function getOrderAllByUser($user_id,$page){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return  false;
        }
        $where['user_id'] = $user_id;
        $where['order_status'] = array('neq','-1');
        $where['status'] = '0';
        $count = M('Order')->where($where)->count();
        $a = I('page');
        if ($a>$count) {
            return false;
        }
        $data = M('Order')->where($where)->limit($a,5)->select();
        
        foreach ($data as $key => $value) {
            if ($value['order_status'] == '-1') {
                $data[$key]['order_status'] = '已取消';
            }elseif($value['order_status'] == '0'&& $value['order_type']=='1'){
                $data[$key]['order_status'] = '货到付款';
            }elseif($value['order_status'] == '1'){
                $data[$key]['order_status'] = '已支付';
            }elseif($value['order_status'] == '2'){
                $data[$key]['order_status'] = '发货中';
            }elseif($value['order_status'] == '3'){
                $data[$key]['order_status'] = '已发货';
            }elseif($value['order_status'] == '4'&& $value['comment_status'] == '0'){
                $data[$key]['order_status'] = '待评价';
            }elseif($value['order_status'] == '4'&& $value['comment_status'] == '1'){
                $data[$key]['order_status'] = '已评价';
            }elseif($value['order_status'] == '5'){
                $data[$key]['order_status'] = '退货审核中';
            }elseif($value['order_status'] == '6'){
                $data[$key]['order_status'] = '审核失败';
            }elseif($value['order_status'] == '7'){
                $data[$key]['order_status'] = '审核成功';
            }elseif($value['order_status'] == '8'){
                $data[$key]['order_status'] = '退款中';
            }elseif($value['order_status'] == '9'){
                $data[$key]['order_status'] = '退款成功';
            } 
            $data[$key]['date'] = date('Y-m-d',$value['create_time']);
            $data[$key]['time'] = date('H:i:s',$value['create_time']);
        }
        return array('res'=>$data,'page'=>$a,'count'=>$count);
    }


    /**
     * 发放用户积分
     * @param  integer $user_id  用户id
     * @param  integer $order_id 订单id
     * @return boolean
     */
    public function sendIntegral($user_id, $order_id, $goods_id)
    {
        // 获取该订单下的商品的数量,且是没有评论的
        $where = [
            'o.user_id' => $user_id,
            'order_id'  => $order_id,
            'goods_id'  => $goods_id,
            'comment'   => 0
        ];
        $info  = $this->alias('o')->join('__ORDER_GOODS__ as g ON o.id=g.order_id')
            ->field('g.id, g.goods_num')->where($where)->find();
        if (empty($info)) {
            return false;
        }

        // 获取积分
        $integer = M('goods')->field('d_integral')->find($goods_id);
        $total   = $integer['d_integral'] * $info['goods_num'];
        $data    = [
            'user_id'      => $user_id,
            'integral'     => $total,
            'goods_id'     => $goods_id,
            'trading_time' => time(),
            'remarks'      => '商品返积分',
            'type'         => 1,
            'status'       => 1
        ];

        // 发放积分
        $ret = M("integralUse")->add($data);

        // 修改商品评论状态
        $ret &= M('orderGoods')->save(['id'=>$info['id'], 'comment'=>1]);

        return $ret;
    }
    //根据条件查询订单信息
    public function getOrderByWhere(array $where){
        if (empty($where)) {
            return false;
        }
        S('where',$where);
        $where['user_id'] = $_SESSION['user_id'];
        $count = M('Order')->where($where)->count();// 查询满足要求的总记录数
        $Page  = new \Think\Page($count,5);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        // foreach($where as $key=>$val) {    
        //     $Page->parameter   .=   "$key=".urlencode($val).'&';
        // }
        $show  = $Page->show();// 分页显示输出
        $field = 'id as order_id,order_sn_id,create_time,price_sum,express_id,order_status,shipping_monery';
        $res = M('Order')->field($field)->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        // showData($res,1);
        return array('res'=>$res,'page'=>$show);
    }
}