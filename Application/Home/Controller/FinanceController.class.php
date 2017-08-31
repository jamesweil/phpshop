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
use Common\Model\BaseModel;
use Home\Model\GoodsModel;
use Home\Model\UserModel;
use Home\Model\InvoiceModel;
use Home\Model\InvoiceGoodsModel;
use Upload\Controller\UploadController;
//财务中心
class FinanceController extends BaseController{
    //判断是否登录
    public function __construct(){ 
        parent::__construct();
        $Data = 
        $this->isLogin();
    }
    //我的发货
    public function my_shipment(){ 
        //查询我的发货
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        $Data = $Invoice->getMyShipmentByUser();
        //查询发票详细商品
        $InvoiceGoods = BaseModel::getInstance(InvoiceGoodsModel::class);
        $Goods = $InvoiceGoods->getInvoiceGoodsByInvoice($Data['data']);
       // 查询对应的商品信息
        $data  = GoodsModel::getGoodsByOrder($Goods);
        $user = UserModel::getUserByUserId();
        $page = $Data['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        $this->assign('user',$user);
    	$this->display();
    }
    //我的发货--查询
    public function my_shipment_search(){ 
        if (IS_POST) {
            $Invoice = BaseModel::getInstance(InvoiceModel::class);
            $name = I('post.name');
            $patten = "/^\d{4}[\-]?(0?[1-9]|1[012])[\-]?(0?[1-9]|[12][0-9]|3[01])$/";
            if (preg_match($patten, $name)) {
                $where['overdue_account'] = strtotime($name);
                $where['type'] = 1;
                $where['user_id'] = $_SESSION['user_id'];               
            } else {
                if ($name = '普通订单') {
                    $where['user_id'] = $_SESSION['user_id'];
                    $where['order_type'] = '0';
                    $where['type'] = 1;
                }elseif ($name = '账期订单') {
                    $where['user_id'] = $_SESSION['user_id'];
                    $where['order_type'] = 1;
                    $where['type'] = 1;
                }else{
                    $where = '';
                }
            }
            $Data = $Invoice->getInvoiceByWhere($where);
            //查询发票详细商品
            $InvoiceGoods = BaseModel::getInstance(InvoiceGoodsModel::class);
            $Goods = $InvoiceGoods->getInvoiceGoodsByInvoice($Data);
            // 查询对应的商品信息
            $data  = GoodsModel::getGoodsByOrder($Goods);
            $user = UserModel::getUserByUserId();
            $page = $Data['page'];
            $this->assign('page',$page);
            $this->assign('data',$data);
            $this->assign('user',$user);
            $this->display('my_shipment');
        }
    }
    //我的发票
    public function my_invoice(){
        $user = UserModel::getUserByUserId(); 
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        //查询发票详细商品
        $Data = $Invoice->getMyInvoiceByUser();
        //查询用户信息
        $data = UserModel::getUserByData($Data['data']);
        $this->assign('data',$data);
        $this->assign('user',$user);
        $this->assign('page',$Data['page']);
    	$this->display();
    }
    //我的发票--查询
    public function my_invoice_search(){
        if (IS_POST) {
            $name = I('post.name');
            $where['id'] = $name;
            $where['user_id'] = $name;
            $where['_logic'] = "OR";
            $map['_complex'] = $where;
            $map['type'] = 2;
            $map['user_id'] = $_SESSION['user_id'];
            $Invoice = BaseModel::getInstance(InvoiceModel::class);
            $Data = $Invoice->getInvoiceByWhere($map);
            //查询用户信息
            $data = UserModel::getUserByData($Data);
            $this->assign('data',$data);
            $this->assign('user',$user);
            $this->assign('page',$Data['page']);
            $this->display('my_invoice');
        }
    }
    //我的发票_发票明细
    public function my_invoice_details(){
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        $id = I('id');
        $data = $Invoice->getInvoiceById($id); 
        $InvoiceGoods = BaseModel::getInstance(InvoiceGoodsModel::class);
        $Goods = $InvoiceGoods->getInvoiceGoodsById($data['id']); 
        //查询商品信息
        $goods = GoodsModel::getGoodsByData($Goods);
        foreach ($goods as $key => $value) {
            $goods_id = $value['goods_id'];
            $space = D('goods')->spec($goods_id);
            $goods[$key]['space'] = $space;
        }
        $this->assign('data',$data);
        $this->assign('goods',$goods);      
    	$this->display();
    }
    //我的付款
    public function my_payment(){
        $user = UserModel::getUserByUserId();
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        $Data = $Invoice->getMyPaymentByUser();
        //用户信息
        $data = UserModel::getUserByData($Data['data']);
        $this->assign('data',$data);
        $this->assign('user',$user);
        $this->assign('page',$Data['page']);
    	$this->display();
    }
    //我的付款--查询
    public function my_payment_search(){
        if (IS_POST) {
            $name = I('post.name');
            $Invoice = BaseModel::getInstance(InvoiceModel::class);
            $where['sales_unit'] = $name;
            $where['type'] = 3;
            $Data = $Invoice->getInvoiceByWhere($where);
           //用户信息
            $data = UserModel::getUserByData($Data);
            $this->assign('data',$data);
            $this->assign('user',$user);
            $this->assign('page',$Data['page']);
            $this->display('my_payment');
        }
    }
    //其他订单
    public function other_orders(){
        $user = UserModel::getUserByUserId();
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        //查询发票详细商品
        $Data = $Invoice->getOtherOrdersByUser();
        //查询用户信息
        $data = UserModel::getUserByData($Data['data']);
        $this->assign('data',$data);
        $this->assign('user',$user);
        $this->assign('page',$Data['page']);
        $this->display();
    }
    //其他订单
    public function other_orders_search(){
        if (IS_POST) {
            $name = I('post.name');
            $where['id'] = $name;
            $where['user_id'] = $name;
            $where['_logic'] = "OR";
            $map['_complex'] = $where;
            $map['type'] = 4;
            $map['user_id'] = $_SESSION['user_id'];
            $Invoice = BaseModel::getInstance(InvoiceModel::class);
            $Data = $Invoice->getInvoiceByWhere($map);
            //查询用户信息
            $data = UserModel::getUserByData($Data);
            $this->assign('data',$data);
            $this->assign('user',$user);
            $this->assign('page',$Data['page']);
            $this->display('other_orders');
        }           
    }
    //其他订单_订单明细
    public function other_orders_details(){
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        $id = I('id');
        $data = $Invoice->getInvoiceById($id); 
        $InvoiceGoods = BaseModel::getInstance(InvoiceGoodsModel::class);
        $Goods = $InvoiceGoods->getInvoiceGoodsById($data['id']); 
        //查询商品信息
        $goods = GoodsModel::getGoodsByData($Goods);
        $this->assign('data',$data);
        $this->assign('goods',$goods);      
        $this->display();
    	$this->display();
    }
    //已出兑订单
    public function closed_order(){
        $user = UserModel::getUserByUserId();
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        $Data = $Invoice->getClosedOrderByUser();
         //查询发票详细商品
        $InvoiceGoods = BaseModel::getInstance(InvoiceGoodsModel::class);
        $Goods = $InvoiceGoods->getInvoiceGoodsByInvoice($Data['data']);
       // 查询对应的商品信息
        $data  = GoodsModel::getGoodsByOrder($Goods);
        foreach ($data as $key => $value) {
            $data[$key]['user_name'] = $user['user_name'];
        }
        $page = $Data['page'];
        $this->assign('page',$page);
        $this->assign('data',$data);
        $this->assign('user',$user);
    	$this->display();
    }
    //已出兑订单--查询
    public function closed_order_search(){
        if (IS_POST) {
            $name = I('post.name');
            $patten = "/^\d{4}[\-]?(0?[1-9]|1[012])[\-]?(0?[1-9]|[12][0-9]|3[01])?$/";
            if (preg_match($patten, $name)) {
                $where['create_time'] = strtotime($name);
                $where['type'] = 6;
                $where['user_id'] = $_SESSION['user_id'];               
            } else {
                $res = M('user')->field('id,user_name')->where(['user_name'=>$name])->find();
                if (!empty($res)) {
                    $where['user_id'] = $res['id'];
                    $where['type'] = 6;
                }else{
                    $where = '';
                }
            }
            $Invoice = BaseModel::getInstance(InvoiceModel::class);
            $Data = $Invoice->getInvoiceByWhere($where);
            $InvoiceGoods = BaseModel::getInstance(InvoiceGoodsModel::class);
            $Goods = $InvoiceGoods->getInvoiceGoodsByInvoice($Data);
           // 查询对应的商品信息
            $data  = GoodsModel::getGoodsByOrder($Goods);
            $user = UserModel::getUserByUserId();
            foreach ($data as $key => $value) {
                $data[$key]['user_name'] = $user['user_name'];
            }
            
            $page = $Data['page'];
            $this->assign('page',$page);
            $this->assign('data',$data);
            $this->assign('user',$user);
            $this->display('closed_order');
        }
    }
    //未出兑订单
    public function outstanding_order(){
        $user = UserModel::getUserByUserId();
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        $Data = $Invoice->getOutStandingOrderByUser();
         //查询用户信息
        $data = UserModel::getUserByData($Data['data']);
        $this->assign('data',$data);
        $this->assign('user',$user);
        $this->assign('page',$Data['page']);
    	$this->display();
    }
    //未出兑订单--查询
    public function outstanding_order_search(){
        if (IS_POST) {
            $name = I('post.name');
            if (is_numeric($name)) {
                $where['order_id'] = $name;
                $where['type'] = 5;
                $where['user_id'] = $_SESSION['user_id'];
            }else{
                $res = M('user')->field('id,user_name')->where(['user_name'=>$name])->find();
                if (!empty($res)) {
                    $where['user_id'] = $res['id'];
                    $where['type'] = 5;
                }else{
                    $where = '';
                }
            }
            $user = UserModel::getUserByUserId();
            $Invoice = BaseModel::getInstance(InvoiceModel::class);
            $Data = $Invoice->getInvoiceByWhere($where);
             //查询用户信息
            $data = UserModel::getUserByData($Data);
            $this->assign('data',$data);
            $this->assign('user',$user);
            $this->assign('page',$Data['page']);
            $this->display('outstanding_order');
        }
    }
    //未出兑订单_明细
    public function outstanding_order_details(){
        $Invoice = BaseModel::getInstance(InvoiceModel::class);
        $id = I('id');
        $data = $Invoice->getInvoiceById($id); 
        $InvoiceGoods = BaseModel::getInstance(InvoiceGoodsModel::class);
        $Goods = $InvoiceGoods->getInvoiceGoodsById($data['id']); 
        //查询商品信息
        $goods = GoodsModel::getGoodsByData($Goods);
        $this->assign('data',$data);
        $this->assign('goods',$goods);      
        $this->display();
        $this->display();
    }
}
