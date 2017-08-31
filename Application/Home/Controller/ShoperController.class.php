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
use Think\Controller;
use Think\Model;

//前台模块
class ShoperController extends Controller{
	//退出登陆
	public function logout(){
		unset($_SESSION['shoper_id']);
		unset($_SESSION['shoper_mobile']);
		unset($_SESSION['shoper_name']);
		$this->success('退出登陆成功',U('Shoper/shoper_login'));
	}
	
	//供应商登陆
	public function shoper_login(){
		if(!empty($_POST)){
			$m = M('shoper');
			$where['mobile'] = $_POST['mobile'];
			$where['pwd'] = md5($_POST['pwd']);
			$result = $m->where($where)->find();
			if(empty($result)){
				$this->error('账户或密码错误');
			}else{
				$_SESSION['shoper_id'] = $result['id'];
				$_SESSION['shoper_mobile'] = $result['mobile'];
				$_SESSION['shoper_name'] = $result['shoper_name'];
				$this->success('修改成功，请登录',U('Shoper/orders_all'));
			}
		}else{
			$this->display();
		}
	}
	
	//订单列表
	public function orders_all(){
		$m = M('goods_orders');
		$where['shoper_id'] = $_SESSION['shoper_id'];
		$where['pay_status'] = 1;
		$nowPage = isset($_GET['p'])?$_GET['p']:1;
		$result = $m->where($where)->order('id DESC')->page($nowPage.',4')->select();
		echo $m->getlastsql();

		$m_2 = M('Goods_orders_record');
		foreach($result as $k=>$v){
			$where_2['goods_orders_id'] = $v['id'];
			$res = $m_2->where($where_2)->select();
			dump($res['gongying']);
			$result[$k]['goods_record'] = $res;
		}
		$this->assign('result',$result);
	
		//分页
		$count = $m->where($where)->count(id);		// 查询满足要求的总记录数
		$page = new \Think\Page($count,10);		// 实例化分页类 传入总记录数和每页显示的记录数
		$show = $page->show();		// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
	
		$this->display();
	}

	//添加发货信息
    public function kuaidi_add(){
    	if(IS_POST){
    		$info = M('Goods_orders');
    		$data['id'] = I('id');
    		$data['kuaidi_name'] = I('kuaidi_name');
    		$data['kuaidi_num'] = I('kuaidi_num');
    		$data['fahuo_time'] = time();
            $data['orders_status'] = 2;
    		$result = $info->save($data);
    		if(!$result){
    			$this->ajaxReturn(0);
    		}else{
    			$this->ajaxReturn(1);
    		}
    	}else{
    		$result['id'] = $_GET['id'];
    		$this->assign('result',$result);
    		$this->display();
    	}
    }
}



