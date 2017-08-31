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

//前台模块
class UserController extends BaseController
{

	//退出登陆
	public function logout(){
		unset($_SESSION['user_id']);
		unset($_SESSION['mobile']);
		session(null); 
		$this->success('退出登陆成功',U('Public/login'));
	}
	
	//修改个人资料 
	public function user_info(){
		if(!empty($_POST)){
			$m = M('user');
			$where['id'] = $_SESSION['user_id'];
			$_POST['update_time'] = time();
			$result = $m->where($where)->save($_POST);
			if($result){
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}
			
		}else{		
			$m = M('user');
			$where['id'] = $_SESSION['user_id'];
			$id = $_SESSION['user_id'];
			if(!file_exists("./Uploads/code/".$id.".png")) {
              
				$content = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/Mobile/Myzzy/web/id/" . $id;
				vendor("phpqrcode.phpqrcode");
				$QRcode = new \QRcode();
				$QRcode::png($content, "./Uploads/code/" . $id . ".png", '', 6);
			}
			$this->assign('grade_name',$grade_name);
			$this->assign('myurl',$content);
			$this->assign('id',$id);
			$result = $m->where($where)->find();
			$this->assign('result',$result);
			
			$m = M('member','vip_');
			$where2['user_id'] = $_SESSION['user_id'];
			$member = $m->where($where2)->find();
			$this->assign('member',$member);
			
			$this->display();
		}
	}
	
	
	// 收货地址
	public function address_list(){
		if(!empty($_POST)){
			$m = M('user_address');
			$_POST['update_time'] = time();
			$where['id'] = $_POST['id'];
			$result = $m->where($where)->save($_POST);
			if($result){
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}			
		}else{		
			$m = M('user_address');
			$where['user_id'] = $_SESSION['user_id'];
			$result = $m->where($where)->select();
		
			$this->assign('result',$result);
			$this->display();
		}		
	}
	
	/*
	 * 我的收藏
	 *
	 * */
	public function my_collection(){
		if(empty($_SESSION['user_id'])){
			$this->redirect('Public/login');
		}
		$goods_shoucang = M('Goods_shoucang');
		$where['user_id'] = session('user_id');
		$count = $goods_shoucang->where($where)->count();
		$Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
		$show       = $Page->show();// 分页显示输出
		$res = $goods_shoucang->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('id DESC')->select();
		
		$this->assign('page',$show);
		$this->assign('res',$res);
		$this->display();
	}
	
	
	/*
	 * 加入收藏
	 *返回值
	 * 1表示已经收藏过改商品
	 * 2表示收藏成功
	 * 3表示收藏失败
	 * */
	public function collection()
	{
	    if(!isset($_SESSION['user_id'])){
	        $this->ajaxReturnData(array('url' => U('Public/login')), 0, '请登录');
	    }
	    if ($_SESSION['form'] !== $_POST['form']) {
	        $this->ajaxReturnData(null, 0, '恶意攻击将负法律责任');
	    }
	    //检测传值
	   // \Common\Tool\Tool::checkPost($_POST, array('is_numeric' => array('goods_id', 'price_new', 'price_old','detail_title','is_type','pic_url')), true) === false ? $this->ajaxReturnData(null, 0, '数据传输错误') : true;

	     $model = new \Home\Model\GoodsShoucangModel();
	     
	     $isSuccess = $model->addCollection($_POST);
	     $status    = $isSuccess === false ? 0 : 1;
	     $message   = $isSuccess === false ? '添加失败或者已经添加过了':'添加成功';
	     
	     $this->ajaxReturnData(null, $status, $message);
	}
	
	
	
	//我的足迹列表
	public function my_footprint(){
		if(empty($_SESSION['user_id'])){
			$this->redirect('Public/login');
		}
		$info = M('FootPrint');
		$where['uid'] = $_SESSION['user_id'];
		$count = $info->where($where)->count();
		$Page = new \Think\Page($count,12);
		$show = $Page->show();// 分页显示输出
		$list = $info->field('id,gid,goods_pic,goods_name,goods_price')->order('create_time desc')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $k=>$v){
			if(strlen($list[$k]['goods_name']) >= 54){
			    //截取字符串
				$list[$k]['goods_name1'] = Tool::utf8sub($v['goods_name'],18).'...';
			}else{
				$list[$k]['goods_name1'] = $v['goods_name'];
			}
		}
		$this->assign('list',$list);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
	
	
	//删除足记
	public function del(){
		$id = I('id');
		$info = M('FootPrint');
		$result = $info->where(array("id"=> $id))->delete();
		if(!result){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	}
	
	
	//优惠券列表
	public function coupons_list(){
		if(empty($_SESSION['user_id'])){
			$this->redirect('Public/login');
		}
		$mobile = $_SESSION['mobile'];
		$info = M('UserCoupons');
		$count = $info->where(array('mobile' => $mobile))->count();
		$Page = new \Think\Page($count,12);
		$show = $Page->show();// 分页显示输出
		$list = $info->where(array('mobile' => $mobile))->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
	
	
	//删除优惠券
	public function coupons_del(){
		$id = $_POST['id'];
		$info = M('UserCoupons');
		$result = $info->where(array('id'=>$id))->delete();
		if(!$result){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	}
	
	/*
	 *我的订单 全部订单
	 *
	 */

	public function my_order_lst(){
		if(session('user_id')==''){
			$this->redirect('Public/login');
		}
		$goods_orders = M('Goods_orders');
		$user_id = $_SESSION['user_id'];
		$where['user_id'] = $user_id;
		$count = $goods_orders->where($where)->count();

		$Page  = new \Think\Page($count,10);
		$show   = $Page->show();
		
		$res = $goods_orders->where($where)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();

		$goods_orders_record = M('Goods_orders_record');
		foreach($res as $k=>$v){
			$where2['goods_orders_id'] = $v['id'];
			$result = $goods_orders_record->where($where2)->select();
			$res[$k]['result'] = $result;
		}
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('res',$res);


		//查询是否是用户身份代码
		$member = M('member','vip_');
		$where_m['id'] = $_SESSION['user_id'];
		$res_member = $member->where($where_m)->find();

		if($res_member['grade_name'] != '会员' && $res_member['grade_name'] != '合伙人'){
			$this->redirect('User/huiyuan_zige');
		}

		$this->display();
	}
	

	//确认订单
	public function queren_shouhuo(){
		if(!empty($_POST)){
			$m = M('user');
			$where['id'] = $_SESSION['user_id'];
			$result = $m->where($where)->find();
			if($result['password'] == md5($_POST['password'])){
				$m = M('Goods_orders');
				$where2['id'] = $_POST['orders_id'];
				$data['orders_status'] = 3;
				$data['shouhuo_time'] = time();
				$m->where($where2)->save($data);
				$this->success('确认收货成功');
			}else{
				$this->error('密码错误,确认失败');
			}
		}else{
			$m = M('goods_orders');
			$where['id'] = $_GET['orders_id'];
			$result = $m->where($where)->find();
			$this->assign('result',$result);
			$this->display();
		}
	}
	
	
	/*
	 * 取消订单
	 * @paremet 0 删除失败
	 * @paremet 1 删除成功
	 */

	public function cancel_order(){
		$id = I('post.id');
		$goods_orders = M('Goods_orders');
		$goods_orders_record = M('Goods_orders_record');
		$user=M('user');
		$user_id=session('user_id');
		$message=$goods_orders->where(array('id'=>$id))->find();//保存订单信息到内存中
		if($message['pay_status']==1){
			$this->ajaxReturn(0);
			exit;//已经支付的订单不允许取消
		}
		$info = $goods_orders->where('id='.$id)->delete();
		
		if($info){
			$info1 = $goods_orders_record->where('goods_orders_id='.$id)->delete();
			/************************释放用户积分*****************************/
			if($message['use_jf_currency']>0){
				$user->where(array('id'=>$user_id))->setInc('add_jf_currency',$message['use_jf_currency']);		        
			}
			if($message['use_jf_limit']>0){
				$user->where(array('id'=>$user_id))->setInc('add_jf_limit',$message['use_jf_limit']);  
			}
			if($message['use_jf_limit']+$message['use_jf_currency']>0){
				$user->where(array('id'=>$user_id))->setInc('integral',$message['use_jf_limit']+$message['use_jf_currency']);
			}
			/*****************************************************/
			if(!$info1){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
		}else{
			$this->ajaxReturn(0);
		}

	}
	
	/*
	 *订单 待付款
	 */
	public function payment_for_lst(){
		$goods_orders = M('Goods_orders');
		$user_id = session('user_id');
		$where = array();
		$where['orders_status'] = 0;
		$where['pay_status'] = 0;
		$count = $goods_orders->alias('a')->join('LEFT JOIN db_user_address b ON a.user_id = b.user_id')->where('a.user_id='.$user_id)->where($where)->count();
		$Page  = new \Think\Page($count,10);
		$show   = $Page->show();
		$res = $goods_orders->alias('a')->field('a.*,b.realname')->join('LEFT JOIN db_user_address b ON a.user_id = b.user_id')->where('a.user_id='.$user_id)->order('a.id DESC')->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();
		$goods_orders_record = M('Goods_orders_record');
		foreach($res as $k=>$v){
			$id = $v['id'];
			$result = $goods_orders_record->where('goods_orders_id='.$id)->select();
			$res[$k]['result'] = $result;
		}
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('res',$res);
		$this->display();
	}
	/*
	 *待收货
	 */

	public  function  receipt_of_goods(){
		$goods_orders = M('Goods_orders');
		$user_id = session('user_id');
		
		$where = array();
		$where['orders_status'] = 0;
		$where['pay_status'] = 1;
		$where['user_id'] = $user_id;
		
		$count = $goods_orders->where($where)->count();
		$Page  = new \Think\Page($count,10);
		$show   = $Page->show();
		$res = $goods_orders->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();
		//dump($res);
		$goods_orders_record = M('Goods_orders_record');
		foreach($res as $k=>$v){
			$id = $v['id'];
			$result = $goods_orders_record->where('goods_orders_id='.$id)->select();
			$res[$k]['result'] = $result;
		}
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('res',$res);
		$this->display();
	}
	
	/*
	 *将订单状态改成一收货状态
	 *@param 0订单修改失败
	 * @param 1 订单修改成功
	 */

	public function  get_goods(){
		$id = I('post.id');
		$goods_orders = M('Goods_orders');
		$info = $goods_orders->where('id='.$id)->setField('pay_status',3);
		if(!$info){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	}
	
	/*
	 * 待评价
	 *
	 */

	public function payments_waite(){
		$goods_orders = M('Goods_orders');
		$user_id = session('user_id');
		$where = array();
		$where['orders_status'] = 1;
		$where['pay_status'] = 3;
		$where['user_id'] = $user_id;
		$count = $goods_orders->where($where)->count();
		$Page  = new \Think\Page($count,10);
		$show   = $Page->show();
		$res = $goods_orders->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->where($where)->select();

		$goods_orders_record = M('Goods_orders_record');
		foreach($res as $k=>$v){
			$id = $v['id'];
			$result = $goods_orders_record->where('goods_orders_id='.$id)->select();
			$res[$k]['result'] = $result;
		}

		//dump($res);
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('res',$res);
		$this->display();
	}
	
	/*
	 *评价表
	 */

	public function comment_add(){
		$goods_comment = M('Goods_comment');
		$id = I('get.id');
		if(!empty($_POST)){
			if(isset($_FILES['goods_img']) && !empty($_FILES['goods_img'])){
				$cfg = array(
					'exts'          =>  array('png','jpg','jpeg','gif'), //允许上传的文件后缀
					'maxSize'       =>  2*1024*1024, //上传的文件大小限制 (0-不做限制)
					'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
					'rootPath'      =>  './Public/Uploads/comment/', //保存根路径
					/*'savePath'      =>  './Public/Uploads/comment', //保存路径*/
				);
				$upload = new \Think\Upload($cfg);// 实例化上传类
				$info   =   $upload->upload();
				if(!$info) {// 上传错误提示错误信息
					   $this->error($upload->getError());
				}else{// 上传成功 获取上传文件信息
					$_POST['goods_img'] = '';
				      foreach($info as $file){
						  $_POST['goods_img'] .= $file['savepath'].$file['savename'].'|';
					  }
					$data['goods_img'] = rtrim($_POST['goods_img'], '|');
				}
			}
			$data['com_content'] = trim($_POST['com_content']);
			$data['coment_time'] = time();
			$data['goods_id'] = I('post.id');
			$data['user_id'] = session('user_id');
			$info = $goods_comment->add($data);
			if(!$info){
				$this->error('评论失败');
			}else{
				$this->success('评论成功');
			}
		}else{
			$this->assign('id',$id);
			$this->display();
		}
	}
	
	
	
	/*
	 *添加地址
	 *
	 */
	public function add_address(){
		$user_address = M('User_address');
		if(!empty($_POST)){
			if($_POST['status']==1){
				$user_id = $_POST['user_id'];
				$res_a = $user_address->field('status,id')->where('user_id='.$user_id)->select();
				foreach($res_a as $k=>$v){
					if($v['status']==1){
						$id = $v['id'];
						$user_address->where('id='.$id)->setField('status',0);
					}
				}
			}
			$info = $user_address->add($_POST);
			if(!$info){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
		}else{
			//将用户已有的地址显示出来
			$user_id = $_SESSION['user_id'];
			$res =$user_address->where('user_id='.$user_id)->select();

			$this->assign('list',$res);
			$this->assign('user_id',$user_id);
			$this->display();
		}
	}

	/*
	 *更新收货地址
	 *
	 */
	public  function addr_edite(){
		$user_address = M('User_address');
		$id = I('get.id');
		if(!empty($_POST)){
			$id = I('post.id');
			$update_time = time();
			$user_id = $_SESSION['user_id'];
			$status = I('post.status');
			if($status==1){
				$res = $user_address->field('id')->where('user_id='.$user_id)->select();
				foreach($res as $k=>$v){
					$id = $v['id'];//地址的主键id
					$user_address->where('id='.$id)->setField('status',0);
				}
			}

			$info = $user_address->where('id='.$id)->save(array(
				'realname'=>I('post.realname'),
				'address'=>I('post.address'),
				'mobile'=>I('post.mobile'),
				'prov'=>I('post.prov'),
				'city'=>I('post.city'),
				'dist'=>I('post.dist'),
				'status'=>I('post.status'),
				'update_time' =>$update_time,
			));
			if(!$info){
				$this->ajaxReturn(0);
			}else{
				$this->ajaxReturn(1);
			}
		}else{
			$res = $user_address->where('id='.$id)->find();
			$this->assign('res',$res);
			$this->display();
		}
	}

	/*
	 *删除地址
	 * @paremet 0 删除失败
	 * @paremet 1 删除成功
	 */

	public function addr_del(){
		$user_address = M('User_address');
		$id = I('post.id');
		$info = $user_address->where('id='.$id)->delete();
		if(!$info){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}
	}

	public function send_sms(){
		$this->display();
	}
	
	/**
	 * 短信验证函数封装
	 */
	public function ajax_get_mobile(){	
		header("Content-Type: textml; charset=UTF-8");
		$flag = 0;
		$params='';//要post的数据
		$verify = rand(100000, 999999);//获取随机验证码
		$mobile = $_GET['mobile'];
		//以下信息自己填以下
		$argv = array(
				'name'=>'dxwzzy',     //必填参数。用户账号
				'pwd'=>'2E80700AF2D325872D9E11726763',     //必填参数。（web平台：基本资料中的接口密码）
				'content'=>'短信验证码为：'.$verify,   //必填参数。发送内容（1-500 个汉字）UTF-8编码
				'mobile'=>$mobile,   //必填参数。手机号码。多个以英文逗号隔开
				'stime'=>'',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
				'sign'=>'【掌中游】',    //必填参数。用户签名。
				'type'=>'pt',  //必填参数。固定值 pt
				'extno'=>''    //可选参数，扩展码，用户定义扩展码，只能为数字
		);
		foreach ($argv as $key=>$value) {
			if ($flag != 0) {
				$params .= "&";
				$flag = 1;
			}
			$params.= $key."="; $params.= urlencode($value);// urlencode($value);
			$flag = 1;
		}
		$url = "http://web.duanxinwang.cc/asmx/smsservice.aspx?".$params; //提交的url地址
		file_get_contents($url);  //获取信息发送后的状态
		$this->ajaxReturn($verify);
	}
	
	
	//重置密码
	public function update_pwd(){
		$info = M('User');
		$mobile = $_POST['mobile'];
		$data['password'] = md5($_POST['password']);
		$result = $info->where(array('mobile'=>$mobile))->save($data);
		if($result){
			$this->redirect('User/pwd_ok');
		}else{
			$this->redirect('User/pwd_error');
		}
	}
	
	/*
	 *删除收藏
	 * @paramet 0 删除失败
	 * @paramet 1 删除成功
	 */

	public function del_collection(){
		$id = I('post.id');
		$goods_shoucang = M('Goods_shoucang');
		$info =$goods_shoucang->where('id='.$id)->delete();
		if(!$info){
			$this->ajaxReturn(0);
		}else{
			$this->ajaxReturn(1);
		}

	}
	
	
	//消息
	public function news_list(){
		$info = M('News');
		$count = $info->count();
		$Page = new \Think\Page($count,12);
		$show = $Page->show();// 分页显示输出
		$list = $info->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('page',$show);// 赋值分页输出
		$this->display();
	}
	
		//积分记录列表
	public function integral_list(){
		$uid = $_SESSION['user_id'];
		$info = M('Integral');
		$count = $info->where(array('uid' => $uid))->count();
		$Page = new \Think\Page($count,10);
		$show = $Page->show();// 分页显示输出er
		$list = $info->where(array('uid' => $uid))->select();
		$this->assign('list',$list);
		$this->assign('page',$show);// 赋值分页输出
		//总积分
		$my=M('user')->where(array('id'=>$uid))->find();
		$grade=M('member','vip_')->where(array('id'=>$uid))->getField('grade_name');
		$this->assign('my',$my);
		$this->assign('grade',$grade);
		$this->display();
	}

	/*
	 *pc会员入费
	 */
	public function  huifei_money(){
		if(session('user_id')==''){
			$this->redirect('Public/login');
		}
		$user_id = $_SESSION['user_id'];
		$hf_money = I('get.hf_money');
		$member=D('member');
		$my=$member->where(array('user_id'=>$user_id))->find();
		$pid=$my['pid'];
		$grade_name=$my['grade_name'];
		if($grade_name=="合伙人" || $grade_name=="会员"){
			$this->error("您已经拥有会员资格,无需再次购买!");
			exit;
		}
		if($member->where(array('id'=>$pid))->getField('grade_name')!="合伙人"){
			if($hf_money!=365){
				$this->error("对不起,您不能购买此类型的会员等级");
				exit;
			}
		}
		if($hf_money==365||$hf_money==30000){
			$orders_num = 'hui'.time().rand(111111,999999);
			$res = strpos($orders_num,'u');
			$user_huifei = M('User_huifei');
			$info = $user_huifei->add(array(
				'user_id'=>$user_id,
				'hf_money'=>$hf_money,
				'orders_num'=>$orders_num,
			));
			if($info){
				$this->redirect('Cart/make_pay_button',array(
					'orders_num'=>$orders_num
				));
			}
		}else{
			$this->error('您输入的金额不对');
		}
	}

	//退货申请
	public function tuihuo_shenqing(){
		$m = M('goods_orders');
		$where['id'] = $_GET['id'];		//订单

		$data['tuihuo_time'] = time();		//退货时间
		$res = $m->where($where)->save($data);
		if($res){
			$this->success('已提交申请');
		}else{
			$this->error('操作错误,请重试');
		}
	}

	public function huiyuan_zige(){

		if(empty($_SESSION['user_id'])){
			$this->redirect('Public/login');
		}


		$member = M('member','vip_');
		$where_m['id'] = $_SESSION['user_id'];
		$res_member = $member->where($where_m)->find();
		$this->assign('res_member',$res_member);

		$this->display();
	}

	//评价
	public function pingjia(){
		if(!empty($_POST)){
			$m = M('goods_orders_record');
			foreach($_POST['record_id'] as $k=>$v){
				$data['pingjia_status'] = $_POST['pingjia_status'][$v];
				$data['pingjia_content'] = $_POST['pingjia_content'][$v];
				$data['pingjia_time'] = time();
				$where['id'] = $v;
				$m->where($where)->save($data);
			}
			$this->success('评论成功');
		}else{
			$m = M('goods_orders_record');
			$where['goods_orders_id'] = $_GET['orders_id'];
			$where['user_id'] = $_SESSION['user_id'];
			$result = $m->where($where)->select();
			$this->assign('result',$result);
			$this->display();
		}
	}

	//退货
	public function tuihuo(){
		if(!empty($_POST)){
			$m = M('goods_orders');
			$where['id'] = $_POST['orders_id'];
			$res = $m->field('orders_status')->where($where)->find();
			$data['tuihuo_zhiqian_status'] = $res['orders_status'];
			$data['orders_status'] = 4;	//退货状态
			$data['tuihuo_time'] = time();
			$data['tuihuo_case'] = $_POST['tuihuo_case'];

			$result = $m->where($where)->save($data);
			if($result){

				$this->success('退货申请已提交');
			}else{
				$this->error('提交失败,系统异常');
			}
		}else{
			$m = M('goods_orders');
			$where['id'] = $_GET['orders_id'];
			$result = $m->where($where)->find();
			$this->assign('result',$result);

			$this->assign('orders_id',$_GET['orders_id']);
			$this->display();
		}
	}
	
public function order_end(){
	if(!session("user_id") && !cookie('userid')){
			$this->redirect('Public/login');
			exit;
		}
	$goods_orders = M('goods_orders');
	$user_id=cookie('userid');
		if(IS_AJAX){
			$orders_num=I('post.orders_num');
			$is_use=$goods_orders->where(array('orders_num'=>$orders_num,'is_used'=>1))->count();
			if($is_use){
				$this->ajaxReturn(0);
				exit;
			}
			$map['orders_status']=5;
			$map['fanli_action']=$user_id.'触发，时间：'.date("Y-m-d H:m:s",NOW_TIME);
			$map['shouhuo_time']=NOW_TIME;
			$order_id=$goods_orders->where(array('orders_num'=>$orders_num))->getField('id');
			$rst=$goods_orders->where(array('orders_num'=>$orders_num))->save($map);
			//$rst=1;
			if($rst){
				R("Shop/buy",array($order_id));
				$this->ajaxReturn(1);
				exit;
			}else{
				$this->ajaxReturn(0);
				exit;
			}
			
		}else{
			$count = $goods_orders->where(array('pay_status'=>1,'user_id'=>$user_id,'fahuo_time'=>array('gt',1462032000),'is_used'=>0))->count();
			$Page  = new \Think\Page($count,10);
			$show   = $Page->show();
			$res = $goods_orders->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->where(array('pay_status'=>1,'user_id'=>$user_id,'fahuo_time'=>array('gt',1462032000),'is_used'=>0))->select();
		
			$goods_orders_record = M('Goods_orders_record');
			foreach($res as $k=>$v){
				$id = $v['id'];
				$result = $goods_orders_record->where('goods_orders_id='.$id)->select();
				$res[$k]['result'] = $result;
			}
			$this->assign('page',$show);// 赋值分页输出
			//dump($res);exit;
			$this->assign('res',$res);
			$this->display();
		}
		
		
	
}
	
}



