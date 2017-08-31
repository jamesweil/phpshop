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
use Common\Model\BaseModel;
use Home\Model\UserModel;
use Upload\Controller\UploadController;
//用户设置
class UserSetController extends BaseController{
    //判断是否登录
     public function __construct()
    {
        parent::__construct();
        
        $this->isLogin();
    }

    //收货地址
    public function address(){
    	$Data = UserModel::getAddressByUserId();
        $data = UserModel::getRegionByData($Data);
        $prov = M('region')->where(['parentid'=>0])->select();
        $this->assign('prov',$prov);
        $this->assign('data',$data);
        $this->display();
    }
    //根据省查出市
    public function city(){
        $where['parentid'] = I('get.code');
        $city=M('region')->where($where)->select();
        if (!empty($city)) {
            $this->ajaxReturn(['data'=>1,'city'=>$city]);
        }else{
            $this->ajaxReturn(['data'=>0]);
        }         
    }
    //根据市查出县
    public function area(){
        $where['parentid'] = I('get.code');
        $dist=M('region')->where($where)->select();
        if (!empty($dist)) {
            $this->ajaxReturn(['data'=>1,'dist'=>$dist]);
        }else{
            $this->ajaxReturn(['data'=>0]);
        }   
    }
    //收货地址添加
    public function address_add(){
        if (IS_POST) {
            $data['prov'] = I('post.prov');//省份
            $data['city'] = I('post.city');//城市
            $data['dist'] = I('post.dist');//地区
            $data['address'] = I('post.address');//详细地址
            $data['zipcode'] = I('post.zipcode');//邮政编码
            $data['realname'] = I('post.realname');//收货人姓名
            $data['mobile'] = I('post.mobile');//收货人电话
            $data['status'] = I('post.status');//是否默认
            if ($data['status'] == '1') {
                $date['status'] = '1';
                $date['user_id'] = $_SESSION['user_id'];
                $result = M('user_address')->field('id')->where($date)->find();
                if (!empty($result)) {
                    $where['id'] = $result['id'];
                    $status['status'] = 0;
                    M('user_address')->where($where)->save($status);
                }
            }
            $data['create_time'] = time();//添加时间
            $data['user_id'] = $_SESSION['user_id'];//用户id
            $res = M('user_address')->data($data)->add();
            if ($res) {
                $this->success('保存成功');exit;
            }
            $this->error('保存失败');
        }
    }
    //收货地址修改
    public function address_edit(){
//        if (IS_POST) {
//            $where['id'] = I('post.id');//收货地址id
//            $data['prov'] = I('post.prov');//省份
//            $data['city'] = I('post.city');//城市
//            $data['dist'] = I('post.dist');//地区
//            $data['address'] = I('post.address');//详细地址
//            $data['zipcode'] = I('post.zipcode');//邮政编码
//            $data['realname'] = I('post.realname');//收货人姓名
//            $data['mobile'] = I('post.mobile');//收货人电话
//            $data['update_time'] = time();//修改时间
//            $res = M('user_address')->where($where)->save($data);
//            if (!$res) {
//                $this->error('修改失败');
//            }
//             $this->success('修改成功,请等待页面刷新');exit;
//        }
        $id = I('id');
        $Data = UserModel::getAddressById($id);
        $data = UserModel::getRegionById($Data);
        //dump($data);exit;
        $prov = M('region')->where(['parentid'=>0])->select();
        $this->assign('prov',$prov);
        $this->assign('data',$data);
        $this->display();
    }

    //修改为ajax提交自该收货地址
    public function from_address_data()
    {
        if(IS_POST){
            $where['id'] = I('post.id');//收货地址id
           $data['prov'] = explode('=',$_POST['fromdata'])[1];//省份
            $data['city'] = I('post.city');//城市
            $data['dist'] = I('post.dist');//地区
            $data['address'] = I('post.address');//详细地址
            $data['zipcode'] = I('post.zipcode');//邮政编码
           $data['realname'] = I('post.realname');//收货人姓名
            $data['mobile'] = I('post.mobile');//收货人电话
            $data['update_time'] = time();//修改时间
          $res = M('user_address')->where($where)->save($data);
            if($res)
            {
                $status=1;
            }else
            {
                $status=0;
            }
            $this->ajaxReturn(array('status'=>$status,'url'=>U('UserSet/address')));
        }
    }
    //收货地址删除
    public function address_del(){
        if (IS_POST) {
            $where['id'] = I('post.id');
            $res = M('user_address')->where($where)->delete();
            if ($res) {
                $this->ajaxReturn(array('status'=>1,'url'=>U('UserSet/address')));
            } 
            $this->ajaxReturn(array('status'=>0));
        }
    }
    //ajax修改默认地址
    public function address_ajax(){
         //var_dump($_GET['id']);
        $m = M('User_address');
        $data['status']  = '1';
        $data['user_id'] = $_SESSION['user_id']; 
        $result = $m->field('id')->where($data)->find();
        if (!empty($result)) {
            $where['id'] = $result['id'];
            $status['status'] = 0;
            $results = $m->where($where)->save($status);
            if (!$results) {
                $this->ajaxReturn(0);
            }
        }
        $date['status']  = '1';
        $id = $_GET['id'];
        $res = $m->where('id='.$id)->save($date);
        if (!$res) {
            $this->ajaxReturn(0);
        }
        $this->ajaxReturn(1);
    }
    //企业信息
    public function enterprise(){
        $data = UserModel::getEnterpriseByUserId();
        $this->assign('data',$data);
        $this->display();
    }
    //添加企业信息
    public function enterprise_add(){
        if (IS_POST) {
            $m = M('enterprise');
            $data = $_POST;
            if (!empty($_POST['province'])||!empty($_POST['city'])||!empty($_POST['area'])) {
                $data['reg_address'] = $_POST['province'].'-'.$_POST['city'].'-'.$_POST['area'];
            }
            if (!empty($_POST['province1'])||!empty($_POST['city1'])||!empty($_POST['area1'])) {
                $data['place_address'] = $_POST['province1'].'-'.$_POST['city1'].'-'.$_POST['area1'];
            }
            $data['category'] = 2;
            $data['set_up_time'] = strtotime($_POST['set_up_time']);
            $data['user_id'] = $_SESSION['user_id'];
            $res = $m->data($data)->add();
            if ($res) {
                $this->success('保存成功');exit;
            }
            $this->error('保存失败');
        }
        
    }
    //修改企业信息
    public function enterprise_edit(){
        if (IS_POST) {
            $m = M('enterprise');
            $data = $_POST;
            if (!empty($_POST['province'])||!empty($_POST['city'])||!empty($_POST['area'])) {
                $data['reg_address'] = $_POST['province'].'-'.$_POST['city'].'-'.$_POST['area'];
            }
            if (!empty($_POST['province1'])||!empty($_POST['city1'])||!empty($_POST['area1'])) {
                $data['place_address'] = $_POST['province1'].'-'.$_POST['city1'].'-'.$_POST['area1'];
            }
            $data['set_up_time'] = strtotime($_POST['set_up_time']);
            $data['user_id'] = $_SESSION['user_id'];
            $res = $m->save($data);
            if ($res) {
                $this->success('修改成功');exit;
            }
            $this->error('修改失败');
        }
        
    }
    //安全设置
    public function security(){
        $data = UserModel::getUserByUserId();
        //查询是否设置密保问题
        $res = M('security_question')->where('user_id='.$_SESSION['user_id'])->select();
        //查询是否绑定手机号
        $tel = M('User')->field('mobile')->where('id='.$_SESSION['user_id'])->select();
        $this->assign('tel',$tel);
        $this->assign('res',$res);
        $this->assign('data',$data);
        $this->display();
    }
    //密码修改
    public function password_edit(){
        if (IS_POST) {
            $m = M('User');
            $where['id'] = $_SESSION['user_id'];
            $res = $m->field('password ')->where($where)->find();
            if(!empty($res)){
                $id = $res['password'];  
                $password  =  I('reg_pwd');//当前密码
                if (md5($password) != $id) {
                    $this->error('当前登录密码输入错误,请重新输入!');
                }
                $new_password = md5(I('post.new_pwd'));//新密码
                $result = $m->where($where)->setField('password',$new_password);
                if (!$result) {
                    $this->error('修改密码失败!');
                }
                $this->success('修改成功,请重新登录!',U('Public/login'));exit;
            }
        }
        $this->display();       
    }

    //绑定手机号
    public function bound_phone(){
        if (IS_POST) {
            $where['id'] = $_SESSION['user_id'];//用户id
            $mobile = I('post.mobile');//绑定的手机号
            $tel = cookie('mobile');//接收验证码的手机
            if ($tel!=$mobile) {
                $this->error('手机号输入错误,请重新输入!');
            }
            $code = I('post.code');//填写的验证码
            $tel_code = cookie('reg_tel_code');//发送的验证码
            if ($rel_code != $code) {
                $this->error('验证码输入错误,请重新输入!');
            }
            $res = M('User')->where($where)->setField('mobile',$mobile);
            if (!$res) {
                $this->error('绑定失败!');
            }
            $this->success('绑定成功!');exit; 
        }
        $user = UserModel::getUserByUserId();
        $data = UserModel::getUserHeaderByUser($user);
        $this->assign('data',$data);
        $this->display(); 
    }

    //设置密保问题
    public function security_question(){
        if (IS_POST) {
            $user_id = $_SESSION['user_id'];
            $time = time();
            $problem1 = I('post.problem1');
            $answer1  = I('post.answer1');
            $problem2 = I('post.problem2');
            $answer2  = I('post.answer2');
            $problem3 = I('post.problem3');
            $answer3  = I('post.answer3');
            $dataList[] = array('problem'=>$problem1,'answer'=>$answer1,'user_id'=>$user_id,'add_time'=>$time);
            $dataList[] = array('problem'=>$problem2,'answer'=>$answer2,'user_id'=>$user_id,'add_time'=>$time);
            $dataList[] = array('problem'=>$problem3,'answer'=>$answer3,'user_id'=>$user_id,'add_time'=>$time);
            $res = M('security_question')->addAll($dataList);
            if ($res) {
                $this->success('添加成功',U('security'));exit;
            }
            $this->error('添加失败');
        }
        $data = UserModel::getQuestionByUserId();
        $this->assign('data',$data);
        $this->display();
    }

    //修改密保问题
    public function question_edit(){
        M()->startTrans();
        if (IS_POST) {
            $m = M('security_question');

            $id1 = I('post.id1');
            $data1['problem'] = I('post.problem1');
            $data1['answer'] = I('post.answer1');
            $data1['update_time'] = time();
            $res1 = $m->where('id='.$id1)->save($data1);
            if ($res1 === false) {
                M()->rollback();
                $this->error('修改失败');
            }       
            $id2 = I('post.id2');
            $data2['problem'] = I('post.problem2'); 
            $data2['answer'] = I('post.answer2');
            $data2['update_time'] = time();
            $res2 = $m->where('id='.$id2)->save($data2); 
            if ($res2 === false) {
                M()->rollback();
                $this->error('修改失败');
            }         
            $id3 = I('post.id3');
            $data3['problem'] = I('post.problem3');
            $data3['answer'] = I('post.answer3');
            $data3['update_time'] = time();
            $res3 = $m->where('id='.$id3)->save($data3);
            if ($res3 === false) {
                M()->rollback();
                $this->error('修改失败');
            }
            M()->commit(); 
            $this->success('修改成功',U('security'));exit;
        }
    }
}