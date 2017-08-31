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
use Common\Tool\Tool;
use Home\Model\OrderGroupModel;
use Home\Model\OrderModel;
use Home\Model\UserModel;
use Home\Model\GoodsModel;
use Home\Model\GoodsImagesModel;
use Common\Model\UserAddressModel;
use Common\Model\BaseModel;
use Think\Controller;
//个人中心-我的团购订单
class OrderGroupController extends BaseController{
     public function __construct()
    {
        parent::__construct();
        
        $this->isLogin();
    }
    //团购订单--全部订单列表
    public function order_group(){
    	$order = OrderGroupModel::getOrderGroupByUserId();
    	$goods = OrderGroupModel::getOrderGroupGoodsByData($order['res']);
    	foreach ($goods as $key => $value) {
    		$Goods = GoodsModel::getGoodsByData($value['goods']);
    		$img   = GoodsImagesModel::getGoodsImageByData($Goods);
    		$goods[$key]['goods'] = $img;
    	}
    	$this->assign('page',$order['page']);
    	$this->assign('count',$order['count']); 
    	$this->assign('goods',$goods);
    	$this->display();
    }  
    //团购订单--取消订单
    public function cancel_order(){
    	$id = I('post.id');//订单id
    	$data['is_del'] = '2';
    	$res = M('Order_group')->where('id='.$id)->save($data);
    	if (!$res) {
    		$this->ajaxreturn(0);
    	}
    	$this->ajaxreturn(1);
    }
    //团购订单--删除订单
    public function order_del(){
    	$id = I('post.id');//订单id
    	$data['is_del'] = '1';
    	$res = M('Order_group')->where('id='.$id)->save($data);
    	if (!$res) {
    		$this->ajaxreturn(0);
    	}
    	$this->ajaxreturn(1);
    }
    //团购订单==订单详情
    public function details(){
    	$id = I('get.order_id');//订单id
    	//查询团购订单表信息
    	$order = OrderGroupModel::getOrderGroupByOrderId($id);
        //查询团购订单商品表信息
        $Goods = OrderGroupModel::getOrderGroupGoodsByOrder($order);
        //查询对应的商品
        $goods = GoodsModel::getGoodsByData($Goods['goods']);
    	$img   = GoodsImagesModel::getGoodsImageByData($goods);
    	$order['goods'] = $img;
    	$order['goods_price_num'] = $Goods['goods_price_num'];
    	$this->assign('order',$order);
    	$this->display();
    }
}