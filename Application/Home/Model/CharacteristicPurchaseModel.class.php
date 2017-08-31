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
//采购需求单
class CharacteristicPurchaseModel extends BaseModel{
	public static $purchaseId_d;	//采购表

	public static $userId_d;	//用户id

	public static $purchaseTitle_d;	//采购标题

	public static $purchaseType_d;	//需求类型(1:询货 2：询价 3：询交期)

	public static $purchaseGoods_id_d;	//采购商品id

	public static $totalPrice_d;	//总预算

	public static $contacts_d;	//联系人

	public static $tel_d;	//联系电话

	public static $overtime_d;	//收货日期

	public static $payType_d;	//支付方式

	public static $invoice_d;	//发票信息

	public static $explain_d;	//说明

	public static $state_d;	//保存状态1保存 2：提交

	public static $status_d;	//阅读状态

	public static $createTime_d;	//创建时间


	public static function getInitnation()
    {
        $class = __CLASS__;
        return self::$obj = !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
	//查询采购需求单列表
	public function getListByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
         	return false;       
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $data = $this->where($where)->page($_GET['p'].',5')->order('create_time DESC')->select();
        $count = $this->where($where)->count();
        $Page = new \Think\Page($count,5);
        $page = $Page->show();
        return array('data'=>$data,'page'=>$page);
	}
	//查询
	public function getListByCheck($user_id){
		$user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
         	return false;       
        }
        $state = I('post.state');
        $purchase_type =I('post.purchase_type');
        $time = strtotime(I('post.time'));
        $date = strtotime(I('post.date'));
        if (!empty($state) && $state != '0') {
        	$where['state'] = $state;
        }
        if (!empty($purchase_type)) {
        	$where['purchase_type'] = $purchase_type;
        }
        if (!empty($time) && !empty($date)) {
        	$where['create_time'] = array(array('GT',$time),array('LT',$date),'AND');
        }
        $where['user_id'] = $user_id;
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $data = $this->where($where)->page($_GET['p'].',5')->order('create_time DESC')->select();
        $count = $this->where($where)->count();
        $Page = new \Think\Page($count,5);
        $page = $Page->show();
        echo M('characteristic_purchase')->_sql();
        return array('data'=>$data,'page'=>$page);
	}
	//查询采购需求单详情
	public function getDetailsById($id){
        if (empty($id)) {
         	return false;       
        }
        $where['purchase_id'] = $id;
        $data = $this->where($where)->find();
        return $data;
	}
	//查询采购需求单商品表
	public function getGoodsBydata(array $data){
		if (empty($data)) {
			return false;
		}
		$id = explode('_',$data['purchase_goods_id']);
		foreach ($id as $key => $value) {
			$where['purchase_product_id'] = $value;
			$goods[] = M('characteristic_purchase_product')->where($where)->find(); 
		}
		$data['goods'] = $goods;
		return $data;
	}
}