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
use Think\Page;
use Common\TraitClass\callBackClass;
use Common\Tool\Tool;
use Common\Model\BaseModel;

class InvoiceModel extends BaseModel{  
    private static $obj;
    public static $id_d;    //发票id

    public static $orderId_d;   //订单编号

    public static $invoiceTitle_d;  //发票标题

    public static $invoiceType_d;   //发票类型 

    public static $createTime_d;    //创建时间

    public static $updateTime_d;    // 修改日期

    public static $type_d;  //1我的发货2我的发票3我的付款4其他订单5未出兑账单6已出兑账单

    public static $userId_d;    //用户id

    public static $remarks_d;   //备注

    public static $voucherNo_d; //凭证号

    public static $overdueAccount_d;    //过期账目

    public static $dueDate_d;   //到期日

    public static $price_d; //金额

    public static $purchaseUnit_d;  //购货单位

    public static $salesUnit_d; //销货单位(收款公司)

    public static $billingDate_d;   //开票日期

    public static $createPeople_d;  //创建人


	public static $orderType_d;	//订单累心


	public static $payType_d;	//支付类型


	public static $expenditure_d;	//总支出

	public static $income_d;	//总收入

	public static $balance_d;	//总余额


	public static $mobile_d;	//收票人手机

	public static $email_d;	//收票人邮箱


	public static $checkStatus_d;	//是否是默认选择的抬头 1：是

    public static $invoiceHeader_d; //发票抬头
	public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    //查询我的发货
    public function getMyShipmentByUser($user_id){
    	$user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
        	return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $where['type'] = 1;
        $data  = $this->where($where)->order('create_time DESC')->page($_GET['p'].',10')->select();
        $count = $this->where($where)->count();
        $Page  = new \Think\Page($count,10);
        $page  = $Page->show();
        return array('data'=>$data,'page'=>$page);
    }
    //查询我的发票
    public function getMyInvoiceByUser($user_id){
    	$user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
        	return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $where['type'] = 2;
        $data  = $this->where($where)->order('create_time DESC')->page($_GET['p'].',10')->select();
        $count = $this->where($where)->count();
        $Page  = new \Think\Page($count,10);
        $page  = $Page->show();
        return array('data'=>$data,'page'=>$page);
    }
    //查询我的付款
    public function getMyPaymentByUser($user_id){
    	$user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
        	return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $where['type'] = 3;
        $data  = $this->where($where)->order('create_time DESC')->page($_GET['p'].',10')->select();
        $count = $this->where($where)->count();
        $Page  = new \Think\Page($count,10);
        $page  = $Page->show();
        return array('data'=>$data,'page'=>$page);
    }
    //查询其他订单
    public function getOtherOrdersByUser($user_id){
    	$user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
        	return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $where['type'] = 4;
        $data  = $this->where($where)->order('create_time DESC')->page($_GET['p'].',10')->select();
        $count = $this->where($where)->count();
        $Page  = new \Think\Page($count,10);
        $page  = $Page->show();
        return array('data'=>$data,'page'=>$page);
    }
    //查询已出兑账单
    public function getClosedOrderByUser($user_id){
    	$user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
        	return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $where['type'] = 6;
        $data  = $this->where($where)->order('create_time DESC')->page($_GET['p'].',10')->select();
        $count = $this->where($where)->count();
        $Page  = new \Think\Page($count,10);
        $page  = $Page->show();
        return array('data'=>$data,'page'=>$page);
    }
     //查询未出兑账单
    public function getOutStandingOrderByUser($user_id){
    	$user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
        	return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $where['type'] = 5;
        $data  = $this->where($where)->order('create_time DESC')->page($_GET['p'].',10')->select();
        $count = $this->where($where)->count();
        $Page  = new \Think\Page($count,10);
        $page  = $Page->show();
        return array('data'=>$data,'page'=>$page);
    }
    //根据ID查询我的发票
    public function getInvoiceById($id){
        if (empty($id)) {
            return false;
        }
        $where['id'] = $id;
        $data  = $this->where($where)->find();
        return $data;
    }
    //根据条件查询发票信息
    public function getInvoiceByWhere(array $where){
        if (empty($where)) {
            return false;
        }
        $data  = $this->where($where)->order('create_time DESC')->select();
        return $data;
    }
}