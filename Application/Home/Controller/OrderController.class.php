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

namespace Home\Controller;

use Home\Model\OrderModel;
use Common\Tool\Tool;
use Home\Model\OrderGoodsModel;
use Home\Model\GoodsModel;
use Home\Model\GoodsCartModel;
use Home\Model\GoodsImagesModel;
use Home\Model\UserModel;
use Home\Model\FootPrintModel;
use Home\Model\CouponListModel;
use Home\Model\UserAddressModel;
use Home\Model\SendAddressModel;
use Common\Model\BaseModel;
use Home\Model\PayTypeModel;
use Home\Model\ExpressModel;
use Think\Controller;

//个人中心-我的订单
class OrderController extends BaseController{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->isLogin();
        
        $this->intnetTitle = $this->intnetTitle.' - '.C('internetTitle.orderCenter');

    }
    
	/**
     * 个人中心-首页
     */
	public function index(){
        //查询我的会员等级
        $this->user = M('User')->field('id,member_status')->where('id='.$_SESSION['user_id'])->find();
        $data = $this->orderList();
        //查询我的优惠券
        $limit = '0,5';
        $user_id = $_SESSION['user_id'];
        $coupon = CouponListModel::getUsableCouponByUserId($user_id,$limit);
        foreach ($coupon['res'] as $key => $value) {
           $coupon['res'][$key]['money'] = substr($value['money'],0,-3);
        } 
        //查询我的积分      
        $integral = UserModel::getIntegralByUserId();
        //查询最热爆款
        // $model = BaseModel::getInstance(OrderGoodsModel::class);
        // $Data  = $model->getAttribute(array(
        //     'field' => array(OrderGoodsModel::$goodsId_d, 'count(*) as count'),
        //     'group' => OrderGoodsModel::$goodsId_d,
        //     'order' => ' count '.BaseModel::DESC,
        //     'limit' => 5
        // ));
        //查询最热爆款对应的商品
        $Goods = D('Goods')->hot_buy();
        //查询最热爆款商品图片
        $goods = GoodsImagesModel::getGoodsImageByData($Goods); 
        //猜你喜欢
        $love = FootPrintModel::getMyTracksByUser();
         //订单数量
        $count = D('Order')->getOrderCountByUser();
        $this->assign('count',$count);
        $this->assign('coupon',$coupon);
        $this->assign('integral',$integral);
        $this->assign('goods',$goods);
        $this->assign('love',$love);
        $this->assign('page',$page);
        $this->assign('data',$data);
        $this->display();
	}

    public function orderList() {
        Tool::connect('parseString'); 
        
        //我的订单
        $order = OrderModel::getOrderAllByUser();

        //查询收货人信息
        $user = UserAddressModel::getUserAddressByData($order['res']);
        //查询支付类型
        $pay = PayTypeModel::getPayTypeByOrder($user);
        //查询订单商品表信息
        $order_goods = OrderGoodsModel::getOrderGoodsByOrder($pay);
        //查询对应的商品信息
        $goods = GoodsModel::getGoodsByOrder($order_goods);
        //查询商品图片
        $data = GoodsImagesModel::getGoodsImageByOrder($goods);                                                                            
        foreach ($data as $key => $value) {
            $data[$key]['images'] = $value['goods'][0]['images'];
        }
        $page = $order['page'];
        if (IS_AJAX) {
            $this->ajaxReturn(['data'=>$data,'page'=>$page]);
        }else{
            return $data;
        }       
    }

    //猜你喜欢--换一批
    public function love_ajax(){
       $love = FootPrintModel::getMyTracksByUser(); 
       $this->ajaxReturn($love);
    }
    //添加收藏
    public function colle_add(){
        $goods_id = I('post.goods_id');
        $goods = GoodsModel::getGoodsByGoodsId($goods_id);
        if (empty($goods)) {
            $this->ajaxReturn(3);//没有该商品
        }else{
            $where['user_id'] = $_SESSION['user_id'];
            $where['goods_id'] = $goods_id;
            $res = M('collection')->where($where)->find();
            if (!empty($res)) {
                $this->ajaxReturn(2);//商品已收藏
            }else{
                $data['goods_id'] = $goods_id;
                $data['user_id']  = $_SESSION['user_id'];
                $data['goods_name'] = $goods['title'];
                $data['add_time'] = time();
                $result = M('collection')->data($data)->add();
                if (!$result) {
                    $this->ajaxReturn(0);//收藏失败
                }
            }
            $this->ajaxReturn(1);//收藏成功
        }
    }
    //添加购物车
    public function cart_add(){
        if (IS_POST) {
            $_POST['goods_num'] = '1';
            $model    = BaseModel::getInstance(GoodsCartModel::class);
            $res = $model->addCart($_POST);
            if ($res === false) {
                $this->ajaxReturn(0);//添加失败
            }else{
                $this->ajaxReturn(1);//添加成功
            }
        }
    }
    /**
     * 订单中心-我的订单(全部)
     */
	public function order_myorder()
    {
		Tool::connect('parseString'); 
        //订单模型
        $orderData = $this->getOrder(array($_SESSION['user_id']),'and status ="0"');
        $data = $orderData['data'];
        $page = $orderData['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        $this->assign('count',$count);
        $this->status = 0;
		$this->display();
	}



	/** 
     * 待付款，
     * 【// -1:取消订单,0 未支付，1已支付，2，发货中，3已发货，4已收货，5退货审核中，6审核失败，7审核成功，8退款中，9退款成功, 10：代发货，11待收货】
     */
    
    public function paymentForlist()
    {   
        $orderData = $this->getOrder(array($_SESSION['user_id'],0,OrderModel::NotPaid), 'and status ="%s" and order_status ="%s"');
       
        Tool::connect('parseString');
        $data = $orderData['data'];
        $page = $orderData['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        $this->assign('count',$count);
        $this->status = 1;
        $this->display('order_myorder');
    }
    
    /**
     *  待收货
     */
    public function receiptOfGoods()
    {
       $orderData = $this->getOrder(array($_SESSION['user_id'], OrderModel::ReceiptOfGoods), ' and order_status ="%s"');
       
        Tool::connect('parseString');        
        $data = $orderData['data'];
        $page = $orderData['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        $this->assign('count',$count);
        $this->status = 2;
        $this->display('order_myorder');
    }
    
    /**
     * 待评价 
     */
    public function paymentsWaite()
    {   Tool::connect('parseString');
        $orderData = $this->getOrder(array($_SESSION['user_id'],0,0, OrderModel::ReceivedGoods), 'and status ="%s" and comment_status ="%s" and order_status ="%s"');
        $data = $orderData['data'];
        $page = $orderData['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        $this->assign('count',$count);
        $this->status = 3;
        $this->display('order_myorder');
    }
    
    /**
     * 待完成  [已发货，还没收到]
     */
    public function orderEnd()
    {   Tool::connect('parseString');
        $orderData = $this->getOrder(array($_SESSION['user_id'], OrderModel::AlreadyShipped), ' and order_status ="%s"');
        
        $data = $orderData['data'];
        $page = $orderData['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        $this->assign('count',$count);
        $this->status = 4;
        $this->display('order_myorder');
    }
    /**
     * 待发货
     */
    public function  shipped()
    {   Tool::connect('parseString');
        $orderData = $this->getOrder(array($_SESSION['user_id'], OrderModel::ToBeShipped), ' and order_status ="%s"');
        
        $data = $orderData['data'];
        $page = $orderData['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        $this->assign('count',$count);
        $this->status = 5;
        $this->display('order_myorder');
    }
    /**
     * 退款
     */
    public function  ReturnPrice()
    {   $Data = D('OrderGoods')->getReturnPriceGoods();
        $Order = D('Order')->getOrderDetailsByOrderId($Data['data']);
        // $Goods = D('OrderGoods')->getOrderGoodsByData($Order);
        foreach ($Order as $key => $value) {
            $Order[$key]['goods'][0]['order_id'] = $value['order_id'];
            $Order[$key]['goods'][0]['goods_id'] = $value['goods_id'];
            $Order[$key]['goods'][0]['goods_price'] = $value['goods_price'];
            $Order[$key]['goods'][0]['goods_num'] = $value['goods_num'];
            $Order[$key]['goods'][0]['status'] = $value['status'];
            $Order[$key]['goods'][0]['comment'] = $value['comment'];

        }
        $goods = GoodsModel::getGoodsByOrder($Order);
        $data  = GoodsImagesModel::getGoodsImageByOrder($goods);
        $this->assign('page',$Data['page']);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        $this->assign('count',$count);
        $this->status = 6;
        $this->display('order_myorder');
    }
    /**
     * 辅助方法
     */
    private  function getOrder(array $value, $where){
    	//实例化订单模型 [懂了吗]
    	$baseModel = BaseModel::getInstance(OrderModel::class);
    	
        $Order = $baseModel->getOrderByUser($value, $where);
        $data = OrderModel::getFreightByData($Order['data']);        
        Tool::connect('parseString');       
        //获取订单商品信息       
        $goodsData = OrderGoodsModel::getInitnation()->getGoodsInfoByOrder($data);
       
        //传递商品模型
        //传递给商品表

        $goods  = GoodsModel::getInitnation()->getGoodsByChildrenOrderData($goodsData);
        //组合数据        
        $orderData = Tool::parseTwoArray($data, $goods, 'order_id', array('goods'));
        $page = $Order['page'];
        return array('data'=>$orderData,'page'=>$page);
    }
    //查询物流
    public function logistics(){
        $id = I('get.id');//订单id
        //查询订单信息
        $order = OrderModel::getOrderByOrderId($id);
        //查询订单商品表信息
        $order_goods = OrderModel::getGoodsByOrderId($id);
        //查询对应的商品信息
        $Goods = GoodsModel::getGoodsByData($order_goods);
        //查询商品图片
        $goods = GoodsImagesModel::getGoodsImageByData($Goods);
        //查询仓库
        $ware = D('SendAddress')->getWareDetailsBYId($order['ware_id']);
        //查询发货地址
        $shipping_address = D('userAddress')->getAddrById($ware['address_id']);
        //查询收货地址
        $receiving_address = D('userAddress')->getAddrById($order['address_id']);
        //查询物流公司
        $express = OrderModel::getExpressTitleByFreightId($order);
        $com = $express['code'];
        $nu  = $express['express_id'];
        // $com = 'shentong';//物流公司编码
        // $nu  =  '3325998325555';//快递单号
        $data = ExpressModel::getExpress($com,$nu);
        $this->assign('data',$data);
        $this->assign('express',$express);
        $this->assign('shipping_address',$shipping_address);
        $this->assign('receiving_address',$receiving_address);
        $this->assign('goods',$goods);
        $this->display();
    }
    //订单确定收货
    public function confirm_receipt(){
        if (IS_POST) {
            $order_id = I('post.id');
            $data['order_status'] = '4';
            $res = M('order')->where('id='.$order_id)->save($data);
            if ($res === false) {
                $this->ajaxReturn(0);
            }
            $this->ajaxReturn(1);
        }
    }
	//订单详情
    public function order_details(){
    	$id = I('id');//订单id
        $Order = OrderModel::getOrderByOrderId($id);
        // // 查询订单快递公司
        $order = OrderModel::getExpressTitleByFreightId($Order); 
        //根据订单id查询订单对应的商品id
        $goods = OrderModel::getGoodsByOrderId($id);
        foreach ($goods as $key => $value) {
            $goods_price_num += $value['goods_num']*$value['goods_price']; 
        }
        $this->assign('goods_price_num',$goods_price_num);
        //查询对应的商品信息
        $Goods = GoodsModel::getGoodsByData($goods);
        //查询商品图片
        $data = GoodsImagesModel::getGoodsImageByData($Goods);
        //查询用户默认收货地址
        $addr = UserModel::getDefaultAddressByUserId();
        $address = UserModel::getRegionByAddress($addr);   
        //查询用户名
        $user = UserModel::getUserByUserId();
        //查询买家留言
        $message = OrderModel::getMessageByOrderId($id); 
		$this->assign('order',$order);
		$this->assign('address',$address);
        $this->assign('user',$user);
		$this->assign('data',$data);
        $this->assign('message',$message);
    	$this->display();   	
    }
    //我的订单--再次购买
    public function buy_again(){
        $id = $_GET['id'];//商品id
        // //根据订单id查询订单对应的商品id
        // $goods = OrderModel::getGoodsByOrderId($id);
        //查询对应的商品信息
        $Goods = GoodsModel::getGoodsByGoodsId($id);
        if (!empty($Goods)&&$Goods['status']!='3') {
            $where['user_id'] = $_SESSION['user_id'];
            $where['goods_id'] = $Goods['goods_id'];
            $goods = M('Goods_cart')->where($where)->find();
            if (!empty($goods)) {
                if ($goods['is_del'] == '0') {
                    $num['goods_num'] = $goods['goods_num']+1;
                    $num['update_time'] = time();
                    $id = $goods['id'];
                    $res = M('Goods_cart')->where('id='.$id)->save($num);
                    if (!$res) {
                        $this->error('添加失败!');
                    }
                }else{
                    $id = $goods['id'];
                    $data['is_del'] = '0';
                    $data['goods_num'] = 1;
                    $data['price_new'] = $Goods['price_member'];
                    $data['update_time'] = time();
                    $result = M('Goods_cart')->where('id='.$id)->save($data);
                    if (!$result) {
                        $this->error('添加失败!');
                    }
                }
            }else{
                $date['goods_id'] = $Goods['goods_id'];
                $date['user_id'] = $_SESSION['user_id'];
                $date['goods_num'] = 1;
                $date['price_new'] = $Goods['price_member'];
                $date['integral_rebate'] = $Goods['integral_rebate'];
                $date['create_time'] = time();
                $date['is_del'] = 0;
                $come =   M('Goods_cart')->data($date)->add();
                if (!$come) {
                    $this->error('添加失败!');
                }
            }
            $this->success('添加成功!',U('Cart/goods'));exit;
        }
        $this->error('添加失败!');
        
    }
    //我的订单--再次购买--订单
    public function buy_again_order(){
        $id = $_GET['id'];//商品id
        //根据订单id查询订单对应的商品id
        $data = OrderModel::getGoodsByOrderId($id);
        //查询对应的商品信息
        $Goods = GoodsModel::getGoodsByData($data); 
        if (!empty($Goods)) {
            foreach ($Goods as $key => $value) {
                if ($value['goods_status']!='3') {
                    $where['user_id'] = $_SESSION['user_id'];
                    $where['goods_id'] = $value['goods_id'];
                    $goods = M('Goods_cart')->where($where)->find();
                    if (!empty($goods)) {
                        if ($goods['is_del'] == '0') {
                            $num['goods_num'] = $goods['goods_num']+1;
                            $num['update_time'] = time();
                            $id = $goods['id'];
                            $res = M('Goods_cart')->where('id='.$id)->save($num);
                            if (!$res) {
                                $this->error('添加失败!');
                            }
                        }else{
                            $id = $goods['id'];
                            $data['is_del'] = '0';
                            $data['goods_num'] = 1;
                            $data['price_new'] = $Goods['price_member'];
                            $data['update_time'] = time();
                            $result = M('Goods_cart')->where('id='.$id)->save($data);
                            if (!$result) {
                                $this->error('添加失败!');
                            }
                        }
                    }else{
                        $date['goods_id'] = $value['goods_id'];
                        $date['user_id'] = $_SESSION['user_id'];
                        $date['goods_num'] = 1;
                        $date['price_new'] = $value['price_member'];
                        $date['integral_rebate'] = $value['integral_rebate'];
                        $date['create_time'] = time();
                        $date['is_del'] = 0;
                        $come =   M('Goods_cart')->data($date)->add();
                        if (!$come) {
                            $this->error('添加失败!');
                        }
                    }
                    $this->success('添加成功!',U('Cart/goods'));exit;
                }
            }            
        }
        $this->error('添加失败!');
        
    }
    //d订单回收站
    public function order_recycle_bin(){
        $orderData = $this->getOrder(array($_SESSION['user_id']), 'and status ="1"'); 
        $Data = $orderData['data'];
        $page = $orderData['page'];
        Tool::connect('parseString');        
        $data = UserAddressModel::getUserAddressByData($Data);
       
        $this->assign('page',$page);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        // showData($orderData,1);
        $this->assign('count',$count); 
        $this->display();
    }
    //订单回收站--还原订单
    public function recycle_reduction(){
        $order_id = I('id');
        $m = M('order');
        $data['status'] = '0';
        $res = $m->where('id='.$order_id)->save($data);
        if ($res) {
            $this->ajaxReturn(1);
        }
        $this->ajaxReturn(0);
    }
    //订单回收站--删除订单
    public function recycle_order_del(){
        M()->startTrans(); 
        $order_id = I('id');//订单id
        $m = M('order');
        $data['status'] = '0';
        $res = $m->where('id='.$order_id)->delete();
        if (!$res) {
            M()->rollback();
            $this->ajaxReturn(0);
        }
        $result = M('Order_goods')->where('order_id='.$order_id)->delete();
        if (!$result) {
            M()->rollback();
            $this->ajaxReturn(0);
        }
        $message = M('db_message')->where('order_id='.$order_id)->find();
        if (!empty($message)) {
            $resu = M('db_message')->where('order_id='.$order_id)->delete();
            if (!$resu) {
                M()->rollback();
            $this->ajaxReturn(0);
            }
        }
        M()->commit();
        $this->ajaxReturn(1);
    }
    //取消订单
    public function cancel_order(){
        if (IS_POST) {
            M()->startTrans();
            $order_id = I('id');
            $m = M('order');
            $data['order_status'] = 0-1;
            $res = $m->where('id='.$order_id)->save($data);
            if (!$res) {
                M()->rollback();
                $this->ajaxReturn(0);
            }
            $data['reason'] = I('post.reason');
            $data['user_id'] = $_SESSION['user_id'];
            $data['order_id'] = I('post.id');
            $data['add_time'] = time();
            $result = M('order_cancel_reason')->data($data)->add();
            if (!$result) {
                M()->rollback();
                $this->ajaxReturn(0);
            }
            M()->commit();
            $this->ajaxReturn(1);
        }
    }
    //取消订单记录
    public function cancel_order_record(){
        $orderData = $this->getOrder(array($_SESSION['user_id'],0,OrderModel::CancellationOfOrder), 'and status ="%s" and order_status ="%s" ');       
        Tool::connect('parseString');        
        $data = $orderData['data'];
        $page = $orderData['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        //记录条数
        $count = D('Order')->getOrderCountByUser();
        // showData($orderData,1);
        $this->assign('count',$count); 
        $this->display();
    }
    //订单取消记录---还原订单
    public function restore_order(){
        $order_id = I('id');
        $m = M('order');
        $data['order_status'] = '0';
        $res = $m->where('id='.$order_id)->save($data);
        if ($res===false) {
            $this->ajaxReturn(0);
        }
        $this->ajaxReturn(1);
    }
    //删除取消订单记录
     public function order_del(){
        $order_id = I('id');
        $m = M('order');
        $data['status'] = '1';
        $res = $m->where('id='.$order_id)->save($data);
        if ($res===false) {
            $this->ajaxReturn(0);
        }
        $this->ajaxReturn(1);
    }
    

    /**
     * 评价-列表-选择商品
     * @return [type] [description]
     */
    public function comment_select_goods(){
        $order_id = I('id');//订单id
        //查询订单
        $order = OrderModel::getOrderByOrderId($order_id);
        //查询订单收货地址
        $this->address = UserModel::getAddressById($order['address_id']);
        //根据订单id查询订单对应的商品id
        $goods = OrderModel::getGoodsByOrderId($order_id);
        //查询对应的商品信息
        $Goods = GoodsModel::getGoodsByData($goods);
        //查询商品图片
        $this->data = GoodsImagesModel::getGoodsImageByData($Goods);
        $this->assign('order',$order);
        $this->display();
    }


    //评价-商品进行评论
    public function comment(){
        $goods_id = I('GET.goods_id', -1, 'intval');//商品id
        $order_id = I('GET.order_id', -1, 'intval');//商品id
        if ($goods_id == -1) {
            return $this->ajaxReturn('参数错误');
        }
        if ($order_id == -1) {
            return $this->ajaxReturn('参数错误');
        }
        // 查询订单商品表信息
        $Goods = OrderModel::getGoodsByGoodsId($goods_id, $order_id);

        //查询订单表数据
        $order = OrderModel::getOrderByOrderId($Goods['order_id']);

        //查询商品信息
        $Data = OrderModel::getGoodsNameByOrderGoods($Goods);

        // 获取商品印象
        $feel = D('goodsClass')->getFeelByClassId($Data['class_id']); 

        //查询商品图片

        $data = GoodsImagesModel::getGoodsImageByGoods($Data);

        $this->assign('order',$order);
        $this->assign('data',$data);
        $this->assign('feel',$feel);
        $this->display();
    }

    /**
     * 提交订单评价
     */
    public function commentSubmit()
    {
        $data = I('POST.');
        if (empty($data)) {
            $this->ajaxReturn('参数错误');
        }

        $data['user_id'] = $_SESSION['user_id'];
        $data['show_pic']= trim($data['show_pic'], ',');

        // 单一商品只能评论一次
        $where = ['user_id' =>$_SESSION['user_id'], 'goods_id'=>$data['goods_id'], 'order_id'=>$data['order_id']];
        $info  = M('orderGoods')->field('comment')->where($where)->find();
        if ($info['comment']) {
            $this->ajaxReturn(['status' => 0, 'message' => '你已经评论过该商品了']);
        }

        $ret = D('OrderComment')->submit($data);

        // 发放积分
        D('order')->sendIntegral($data['user_id'], $data['order_id'], $data['goods_id']);

        // 修改订单状态,标记为已评价
        $ret = M('order')->save(['comment_status'=>1, 'id'=>$data['order_id']]);
        $this->ajaxReturn(['status' => 1, 'message' => '评价成功']);
    }


    /**
     * 上传图片
     */
    public function uploadImage()
    {
        $rootPath = './'.UPLOAD_PATH;
        $savePath = '/show/';
        $sub_name = ['date','Y-m-d'];
        $config = array(
            "rootPath" => $rootPath,
            "savePath" => $savePath,
            "saveName" => ['uniqid',''],
            "maxSize"  => 20000000, // 单位B
            "exts"     => explode(",", 'gif,png,jpg,jpeg'),
            "subName"  => $sub_name,
        );
        $ids = D('orderComment')->uploadImage($config);
        $ids = empty($ids) ? [] : $ids;
        $this->ajaxReturn($ids);
    }
    //订单搜索
    public function search_order(){
        if (IS_POST) {
            $name=I('post.name');//用户输入的账号
            $type = I('post.type');//订单类型
            $control_date = strtotime(I('post.control_date'));//订单开始时间
            $control_date2 = strtotime(I('post.control_date2'));//订单结束时间
            $trans = I('post.trans');//订单状态
            $comment = I('post.comment');//评价状态
            if (!empty($control_date)&&!empty($control_date2)) {
                $where['create_time'] = array(array('GT',$control_date),array('LT',$control_date2),'AND');
            }
            if (!empty($trans)) {
                $where['order_status'] = ($trans=='11')?'0':$trans;
            }            
            if (!empty($comment)) {
                $where['comment_status'] = ($comment=='2')?'0':$comment;
            }
            if (!empty($name)) {
                if (is_numeric($name)) {
                    $where['id'] = $name;
                    $Order = OrderModel::getOrderByWhere($where);
                    $Order_goods = D('OrderGoods')->getOrderGoodsByData($Order);
                    $goods = GoodsModel::getGoodsByOrder($Order_goods);
                    $data = GoodsImagesModel::getGoodsImageByOrder($goods);
                    $this->assign('data',$data);
                    $page = '';
                    $this->assign('page',$page);
                    $count = D('Order')->getOrderCountByUser();
                    $this->assign('count',$count);
                    $this->display('order_myorder');exit;
                }else{
                    $Goods['title'] = array('like','%'.$name.'%');
                    $Goods['p_id'] = array('NEQ',0);
                    $res = M('Goods')->field('id as goods_id')->where($Goods)->select();
                    if (!empty($res)) {
                        foreach ($res as $key => $value) {
                            $goods['goods_id'] = $value['goods_id']; 
                            $order = M('Order_goods')->field('order_id')->where($goods)->find();
                            $res[$key]['order_id'] = $order['order_id'];
                            if (empty($res[$key]['order_id'])) {
                                unset($res[$key]);
                            }
                        }

                        $Order = OrderModel::getOrderByData($res);
                        $Order_goods = D('OrderGoods')->getOrderGoodsByData($Order);
                        $goods = GoodsModel::getGoodsByOrder($Order_goods);
                        $data = GoodsImagesModel::getGoodsImageByOrder($goods);
                        $this->assign('data',$data);
                        $page = '';
                        $this->assign('page',$page);
                        $count = D('Order')->getOrderCountByUser();
                        $this->display('order_myorder');exit;
                    }
                }
            }
            $Order = OrderModel::getOrderByWhere($where);
            $Order_goods = D('OrderGoods')->getOrderGoodsByData($Order['res']);
            $goods = GoodsModel::getGoodsByOrder($Order_goods);
            $data = GoodsImagesModel::getGoodsImageByOrder($goods);
            $count = D('Order')->getOrderCountByUser();
            $this->assign('count',$count);
            $page = $Order['page'];
            $this->assign('data',$data); 
            $this->assign('page',$page);
            $this->display('order_myorder');exit;
        }else{
            $where = S('where');
            $Order = OrderModel::getOrderByWhere($where);
            $Order_goods = D('OrderGoods')->getOrderGoodsByData($Order['res']);
            $goods = GoodsModel::getGoodsByOrder($Order_goods);
            $data = GoodsImagesModel::getGoodsImageByOrder($goods);
            $count = D('Order')->getOrderCountByUser();
            $this->assign('count',$count);
            $page = $Order['page'];
            $this->assign('data',$data); 
            $this->assign('page',$page);
            $this->display('order_myorder');exit;
        }
                      
    }
    //服务消息
    public function logistics_message(){
        $where['user_id'] = $_SESSION['user_id'];
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $mes = M('order_logistics_message')->where($where)->page($_GET['p'].',10')->order('addtime DESC')->select();
        //查询订单消息
        $Order = OrderModel::getOrderByData($mes);
        $order = D('Express')->getExpressByOrder($Order);
        //查询商品信息
        $order_goods =D('OrderGoods')->getOrderGoodsByData($order);
        //查询对应的商品
        $goods = GoodsModel::getGoodsByOrder($order_goods);
        //查询对应图片
        $data = GoodsImagesModel::getGoodsImageByOrder($goods);
        foreach ($data as $key => $value) {
            $data[$key]['img'] = $value['goods'][0]['images'];
        }
        $count = M('order_logistics_message')->where($where)->count();
        $Page  = new \Think\Page($count,10);
        $page  = $Page->show();
        $this->assign('data',$data);
        $this->assign('page',$page); 
        $this->display();
    }
    //服务消息-修改状态
    public function logistics_message_edit(){
        $id = I('get.id');//订单id
        $where['id'] = I('get.mes_id');
        $data['status'] = 1;
        $res = M('order_logistics_message')->where($where)->save($data);
        if ($res ===false) {
            $this->error('未知错误!');   
        }
        $this->redirect('Order/logistics',['id'=>$id]);
    }
}