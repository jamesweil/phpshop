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
use Common\Model\BaseModel;
use Home\Model\UserLevelModel;
use Home\Model\LoginModel;
use Common\Controller\MsmFactory;
use Common\TraitClass\SmsVerification;
use Common\TraitClass\InternetTopTrait;

//前台模块
class PublicController extends Controller
{
    use SmsVerification;
    
    use InternetTopTrait;
   
    private $_model;
    protected  function _initialize()
    {
        $this->_model = D("User");
        
        $information = $this->getIntnetInformation();
       
        $this->assign($information);
        
        $this->assign('str', $this->getFamily());
        
        $this->assign('intnetTitle', $information['intnet_title']);
        
    }

    //显示注册页面
    public function reg(){
      
        $is_start = $this->check_open_sms('开启短信');//判断短信开关
        $this->assign('is_start',$is_start);
        $this->display();
    }

    //验证验证码
    public function check_php_code(){
        if($_SESSION['verification']==$_POST['code'])
            $msg=1;
    $this->ajaxReturn(array('msg'=>$msg));
    }

    //注册添加用户信息
    //add 31 添加成功  20注册失败
    //mobile 2 特殊原因被删除的客户 1已注册
    // user_name 3已经存在的用户
    public function add_user_info(){
        if(!M('user')->field('id')->where(array('user_name'=>$_POST['user_name'],'status'=>1))->find()) {
                if (!M('user')->field('id')->where(array('mobile' => $_POST['mobile'], 'status' => 1))->find() ||$_POST['mobile']=='') {
                    if (!M('user')->field('id')->where(array('mobile' => $_POST['mobile'], 'status' => 0))->find()) {
                        $_POST['status'] = 1;
                        $_POST['create_time'] = time();
                        $_POST['password'] = md5($_POST['password']);
                        $_POST['level_id'] = 1;
                        if (M('user')->add($_POST))
                            $add_status['add'] = 31;
                        else
                            $add_status['add'] = 20;
                    } else {
                        $add_status['mobile'] = 2;
                    }
                } else {
                    $add_status['mobile'] = 1;
                }
        } else{
            $add_status['user_name']=1;
        }
        $this->ajaxReturn(array('add_status'=>$add_status));
    }

    //填写账号信息
    public function reg_account(){
        if (IS_POST) {
            $tel_code = S('reg_tel_code');//发送的验证码
            $rel_code = $_POST['rel_code'];//用户填写的验证码
            if ($rel_code != $tel_code) {
                $this->ajaxReturn(array('code'=>4,'mes'=>'验证码输入错误,请重新输入!'));
            }
            // 好的话 正则验证手机号
            $mobile = session('mobile');//接收验证码的手机
            $tel = $_POST['mobile'];//用户填写的手机
            if ($tel!=$mobile) {
                $this->ajaxReturn(array('code'=>3,'mes'=>'手机号输入错误,请重新输入!'));
            }
            $res = $this->_model->field('id')->where('mobile='.$tel)->find();//查询手机号是否存在
            if (!empty($res)) {
                $this->ajaxReturn(array('code'=>2,'mes'=>'手机号码已存在,请重新输入!'));
            }       
            $this->ajaxReturn(array('code'=>1,'mes'=>$tel));
        }    
    }

    //验证
    public function check_code_ajax()
    {
        //实例化短信类
        $SMS = new MsmFactory;
        $data = $SMS->factory(I('post.tel'),2);//登录模板check_id =2
        if($data->Code == 'OK'){
            $this->ajaxReturn(['status' => 1]);
        }


        //if($_POST['id']==1){
        //    if(M('User')->where(array('mobile'=>$_POST['mobile']))->find()){
        //        $this->ajaxReturn(array('status'=>0));
        //    }else{
        //        $this->ajaxReturn(array('math_code' => $SMS->factory($_POST['mobile'], $_POST['id'])));
        //    }
        //}else {
        //
        //    if (!$_POST['id']) {
        //        $this->ajaxReturn(array('math_code' => $SMS->factory($_POST['mobile'], false)));
        //    } //返回短信验证码
        //    else {
        //        $this->ajaxReturn(array('math_code' => $SMS->factory($_POST['mobile'], $_POST['id'])));
        //    }
        //}
    }

    //验证手机号是否注册
    public function mobile_check(){
        if (IS_POST) {
            $tel = I('post.tel');
            $res = $this->_model->field('id')->where('mobile='.$tel)->find();//查询手机号是否存在
            if (!empty($res)) {
                $this->ajaxReturn(1);
            }
             $this->ajaxReturn(2);
        }
    }
    //填写推荐人编码判断邮箱是否存在
    public function reg_person(){
        if (IS_POST) {
            $where['email'] = $_POST['email'];//用户输入的邮箱
            if(!filter_var($where['email'], FILTER_VALIDATE_EMAIL)){
                $this->ajaxReturn(array('code'=>3,'mes'=>'邮箱格式不对,请重新输入!'));
            }
            $res = $this->_model->field('id')->where($where)->find();//查询邮箱是否存在
            if (!empty($res)) {
                $this->ajaxReturn(array('code'=>2,'mes'=>'邮箱号码已存在,请重新输入!'));
            }
            $user['user_name'] = I('post.user_name');
            $result = $this->_model->field('id')->where($user)->find();//查询邮箱是否存在
            if (!empty($result)) {
                $this->ajaxReturn(array('code'=>4,'mes'=>'用户名已存在,请重新输入!'));
            }
            $this->ajaxReturn(array('code'=>1));
        }
    }
    //添加数据库
    public function reg_complete(){
        if (IS_POST) {
            $data['mobile'] = I('post.mobile');
            $data['email'] = I('post.email');
            if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                $this->ajaxReturn(array('code'=>4,'mes'=>'邮箱格式不对,请重新输入!'));
            }
            $data['password'] = md5(I('post.password'));
            $data['user_name'] = I('post.user_name');
            $data['recommendcode'] = I('post.recommendcode');//推荐人编码
            $data['create_time'] = time();
            $resu = $this->_model->field('id')->where('mobile= "%s"', $data['mobile'])->find();//查询手机号是否存在
            if (!empty($resu)) {
                $this->ajaxReturn(array('code'=>2,'mes'=>'手机号码已存在,请重新输入!'));
            }
            $where['email'] = $data['email'];//邮箱   
            $resu = $this->_model->field('id')->where($where)->find();//查询手机号是否存在
            if (!empty($resu)) {
                $this->ajaxReturn(array('code'=>3,'mes'=>'邮箱已存在,请重新输入!'));
            }

            // 保存用户
            M()->startTrans();

            // 授权登陆的信息获取相关信息
            $auth = session('reg_auth_base');
            if ($auth['nick_name']) {
                $data['nick_name'] = $auth['nick_name'];
                $data['sex']       = $auth['sex'];
            }
            // 保存基本信息
            $user_id = $this->_model->data($data)->add();
            if (!$user_id) {
                M()->rollback();
                $this->error('出现未知错误,注册失败!');
            }
            // 保存头像
            if ($auth['avatar']) {
                $ret = M('userHeader')->add(['user_header' => $auth['avatar'], 'user_id' => $user_id]);
                if (empty($ret)) {
                    M()->rollback();
                    $this->error('出现未知错误,注册失败!');
                }
            }
            // 保存用户授权信息
            if ($auth['credential']) {
                $auth['user_id'] = $user_id;
                $ret = D('userAuths')->saveInfo($auth);
                if (!$ret) {
                    M()->rollback();
                    $this->error('出现未知错误,注册失败!');
                }
            }
            session('reg_auth_base', null);
            M()->commit();

            $this->ajaxReturn(array('code'=>1,'user_name'=>$data['user_name'],'mobile'=>$data['mobile']));
        }
    }
   

    //登陆
    public function login(){
        $type = I('type', 0);
        switch ($type) {
            case 1:
                $this->login4Weibo();
                break;
            case 2:
                $this->login4QQ();
                break;
            
            default:
                $this->login4Phone();
                break;
        }
    }

    //手机验证码登陆3
    public function mobileLogin(){
        if(IS_POST){         
            $mobile = $_POST['tel'];//用户输入的手机号
            $res = $this->_model->field('id,mobile,user_name,password,member_status')->where('mobile='.$mobile)->find();
            if (empty($res)) {
                $this->ajaxReturn(array('code'=>4));//('账号不存在,请重新输入!');
            } else {
                session('user_id',$res['id']);
                session('user_name',$res['user_name']);
                session('mobile',$res['mobile']);
                session('member_status',$res['member_status']);
                $time = time();
                $this->_model->where('id='.$res['id'])->setField('last_logon_time',$time);
                //session('reg_tel_code',null);
                $referer = I('referer');
                $this->ajaxReturn(array('code'=>1,'mes'=>'/index.php/Home/Index/index'));           
            }           
        }              
    }
    //用户协议
    public function user_xieyi(){
        $this->display();
    }
    
    //忘记密码--确认账号
    public function confirm_account(){
        if (IS_POST) {
            $name=str_replace(' ','',$_POST['name']);//用户输入的账号
            $condition['mobile'] = $name;
            $condition['email'] = $name;
            $condition['user_name'] = $name;
            $condition['_logic'] = 'OR';
            $result = $this->_model->field('id,mobile,password')->where($condition)->find();
            if (empty($result)) {
                $this->ajaxReturn(0);
            }else{
                $this->ajaxReturn(1);
            }
        }
        $this->display();
    }
    //忘记密码--验证身份
    public function verify_identity(){
        if (IS_POST) {
            $name=str_replace(' ','',$_POST['name']);//用户输入的账号
            $condition['mobile'] = $name;
            $condition['email'] = $name;
            $condition['user_name'] = $name;
            $condition['_logic'] = 'OR';
            $res = $this->_model->field('id,mobile,user_name')->where($condition)->find();
            $res['tel'] = substr_replace($res['mobile'],'****',3,4);
            $this->assign('res',$res);
            $this->display();
        }
    }
    //忘记密码--设置密码
    public function set_password(){
        if (IS_POST) {
            $id = I('post.id');
            $this->assign('id',$id);
            $this->display();
        }       
    }
    //忘记密码--完成
    public function complete(){
        if (IS_POST) {
            $where['id'] = I('post.id');
            $data['password'] = MD5(I('post.password'));
            $res = $this->_model->where($where)->setField($data);
            if (!$res) {
                $this->error('修改失败!');
            }
            $this->display();
        }       
    }
    //验证码
    public function check_tel_code(){
        $code = $_POST['code'];//用户输入的验证码
        $tel_code = S('reg_tel_code');//发送的验证码
        if ($tel_code != $code) {
            $this->ajaxReturn(0);
        }
        $this->ajaxReturn(1);
    }

    //退出登录
    public function logout(){
        session('user_id',null);    //注销 uid ，account
        session('mobile',null);
        session('discount', null);
        if($_COOKIE['user_id']) unset($_COOKIE['user_id']);
        if($_COOKIE['mobile']) unset($_COOKIE['mobile']);
        $this->success('退出登录成功',U('Public/login'));
    }
    //验证码
    public function verify(){

        ob_clean();     //清除缓存
        $Verify = new \Think\Verify();
        $Verify->fontSize = 20; //验证码字体大小
        $Verify->length = 4;    //验证码位数
        $Verify->entry();
    } 
    //ajxa检查验证码
    public function check_code(){
        $code = $_GET['code'];  //验证码
        $verify = new \Think\Verify(array('reset'=>false));
        if($verify->check($code)){
            $this->ajaxReturn(1);   //成功
        }else{
            $this->ajaxReturn(0);   //失败
        }
    }

    
    /**
     * 微博登录
     */
    private function login4Weibo()
    {
        (new LoginModel())->weibo(1);
    }


    /**
     * 微博登录的回调信息
     */
    public function login4WbReturn()
    {
        // 1.获取访问授权
        $code = I('code');
        if (empty($code)) {
            return $this->error('微博登录失败');
        }

        // 2.获取access_token
        $data = (new LoginModel())->weibo(2, ['code'=>$code]);
        if (empty($data) || $data['error_code'] > 0) {
            return $this->error('微博登录失败');
        }

        // 3.获取本地用户授权
        $auth = D('userAuths')->findInfo($data['uid'], 6);

        // 4.检测用户登录
        // 4.1 不是第一次使用微博登录
        if ($auth['user_id']) {
            $base = [
                'id'            => $auth['auth_id'],
                'user_id'       => $auth['user_id'],
                'identifier'    => $data['uid'],
                'identity_type' => 6,
                'credential'    => $data['access_token'],
                'expires_in'    => $data['expires_in'],
                'local'         => 0
            ];
            $ret  = D('userAuths')->saveInfo($base);
            session('user_id', $auth['user_id']);
            session('mobile', $auth['mobile']);
            // session('discount', $auth['user_id']);
            $this->redirect('/home/index/index');
            return true;
        }

        // 4.2 是第一次使用微博登录,需要跳转到注册页面添加基本信息
        if ($data['access_token']) {

            $param = ['uid' => $data['uid'], 'access_token' => $data['access_token']];
            $info  = (new LoginModel())->weibo(3, $param);

            switch ($info['gender']) {
                case 'm':
                    $sex = '男';
                    break;
                case 'f':
                    $sex = '女';
                    break;
                default:
                    $sex = '未知';
                    break;
            }
            $base = [
                'sex'           => $sex,
                'identity_type' => 6,
                'nick_name'     => $info['screen_name'],
                'avatar'        => $info['avatar_large'],
                'identifier'    => $data['uid'],
                'credential'    => $data['access_token'],
                'expires_in'    => $data['expires_in'],
                'local'         => 0
            ];

            // 保存授权信息,需要跳转到注册信息,绑定信息
            session('reg_auth_base', $base);

            // 注意是否是绑定账户的跳转
            $bind = session('bind_auth_base');
            if ($bind['referer'] && $_SESSION['user_id']) {
                $base['user_id'] = $_SESSION['user_id'];
                D('userAuths')->saveInfo($base);
                session('bind_auth_base', null);
                $this->redirect($bind['referer']);
            }

            $this->redirect('reg');
        }
        return false;
    }


    /**
     * qq登录
     */
    private function login4QQ()
    {
        (new LoginModel())->qq(1);
    }


    /**
     * qq回调
     */
    public function login4QQReturn()
    {
        // 1.获取访问授权code
        $code = I('code');
        if (empty($code)) {
            $this->error('qq登录失败');
        }

        // 2.获取授权
        $data = (new LoginModel())->qq(2, ['code' => $code]);
        if (empty($data)) {
            $this->error('qq登录失败');
        }

        // 3.获取openid
        $openid = (new LoginModel())->qq(3, ['access_token' => $data['access_token']]);

        // 4.获取本地授权用户
        $auth = D('userAuths')->findInfo($data['uid'], 4);

        // 4.1 不是第一次登录
        if ($auth['user_id'] > 0) {
            $base = [
                'id'            => $auth['auth_id'],
                'user_id'       => $auth['user_id'],
                'identifier'    => $openid['openid'],
                'identity_type' => 6,
                'credential'    => $data['access_token'],
                'expires_in'    => $data['expires_in'],
                'local'         => 0
            ];
            $ret  = D('userAuths')->saveInfo($base);
            session('user_id', $auth['user_id']);
            session('mobile', $auth['mobile']);
            $this->redirect('index/index');
        }
        // 4.2 第一次登录
        $param  = [
            'openid'       => $openid['openid'],
            'access_token' => $data['access_token']
        ];
        $info = (new LoginModel())->qq(4, $param);
        if (empty($info) || $info['ret'] < 0 ) {
            $msg = empty($info['msg']) ? 'QQ登录失败' : $info['msg'];
            $this->error($msg);
        }

        $avatar = empty($info['figureurl_qq_2']) ? $info['figureurl_qq_1'] :$info['figureurl_qq_2'];
        $base   = [
            'sex'           => $info['gender'],
            'identity_type' => 4,
            'nick_name'     => $info['nickname'],
            'avatar'        => $avatar,
            'identifier'    => $openid['openid'],
            'credential'    => $data['access_token'],
            'expires_in'    => $data['expires_in'],
            'local'         => 0
        ];
        session('reg_auth_base', $base);
        $this->redirect('reg');
    }


    /**
     * 微信登陆
     */
    private function login4Wechat()
    {
        (new LoginModel())->wechat(1);
    }


    /**
     * 微信回调
     */
    public function login4WXReturn()
    {
        // 1.获取授权code
        $code = I('code');
        if (empty($code)) {
            $this->error('微信登录错误');
        }
        
        // 2.获取access_token
        $data = (new LoginModel)->wechat(2, ['code' => $code]);
        if (empty($data['openid'])) {
            $this->error('微信登录错误');
        }

        // 3.获取本地授权用户
        $auth = D('userAuths')->findInfo($data['openid'], 5);
        // 3.1 不是第一次登陆
        if ($auth['user_id'] > 0) {
            $base = [
                'id'            => $auth['auth_id'],
                'user_id'       => $auth['user_id'],
                'identifier'    => $data['openid'],
                'identity_type' => 5,
                'credential'    => $data['access_token'],
                'expires_in'    => $data['expires_in'],
                'local'         => 0
            ];
            $ret  = D('userAuths')->saveInfo($base);
            session('user_id', $auth['user_id']);
            session('mobile', $auth['mobile']);
            $this->redirect('index/index');
        }

        // 3.2 第一次登录
        $param  = [
            'openid'       => $data['openid'],
            'access_token' => $data['access_token']
        ];
        $info = (new LoginModel())->wechat(3, $param);
        if (empty($info) ||  $info['errcode'] > 0) {
            $this->error('微信登录失败');
        }

        switch ($info['sex']) {
            case 1:
                $sex = '男';
                break;
            case 2:
                $sex = '女';
                break;
            default:
                $sex = '未知';
                break;
        }
        $base = [
            'sex'           => $sex,
            'identity_type' => 5,
            'nick_name'     => $info['nickname'],
            'avatar'        => $info['headimgurl'],
            'identifier'    => $info['openid'],
            'credential'    => $data['access_token'],
            'expires_in'    => $data['expires_in'],
            'local'         => 0
        ];
        session('reg_auth_base', $base);
        $this->redirect('reg');
    }

    // 默认手机登录
    private function login4Phone()
    {
        if(!empty($_POST)){
            //判断总开关
            $sms_type = $this->check_open_sms('开启短信');//查询短信是否开启
            if($sms_type)
            {
                //$is_start=M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->find()['id'];
                //$is_start=M('TemplateCategory')->where('template_category_id='.$is_start.' AND id=2')->find()['status'];
                $is_start = $this->check_open_sms('登录验证');

                if($is_start==1){
                    $name=str_replace(' ','',$_POST['name']);//用户输入的账号
                    $condition['mobile'] = $name;
                    $condition['email'] = $name;
                    $condition['user_name'] = $name;
                    $condition['_logic'] = 'OR';
                    $password = md5($_POST['pwd']); //用户输入的密码
                    $result = $this->_model->field('id,mobile,password,integral,member_status,user_name')->where($condition)->find();
                    if (empty($result)) {
                        $this->ajaxReturn(array('code'=>2));//('账号不存在,请重新输入!',U('login'));
                    }else {
                        if ($password != $result['password']) {
                            $this->ajaxReturn(array('code'=>3));//('密码错误,请重新输入!',U('login'));
                        }else{
                            session('user_id',$result['id']);
                            session('mobile',$result['mobile']);
                            session('member_status',$result['member_status']);
                            $_SESSION['user_name'] = $result['user_name'];
                            cookie('user_id',$result['id'],3600*24*7);
                            $level = BaseModel::getInstance(UserLevelModel::class)->getUserLevelByLevelId($result['integral']);
                            session('discount', $level[UserLevelModel::$discountRate_d] ? $level[UserLevelModel::$discountRate_d] : 100);
                            //如果点了自动登录,则把账号信息写入cookie
                            if ($_POST['logon'] == 1) {
                                cookie('user_id',$result['id'],3600*24*7);
                                cookie('mobile',$result['mobile'],3600*24*7);
                                cookie('member_status',$result['member_status'],3600*24*7);
                            }

                            $time = time();
                            $this->_model->where('id='.$result['id'])->setField('last_logon_time',$time);
                            session('reg_tel_code',null);
                            $referer = I('referer');
                            $this->ajaxReturn(array('code'=>1,'mes'=>'/index.php/Home/Index/index'));

                        }


                    }
                    $this->ajaxReturn(0);//('登录失败!',U('login'));
                }else{
                    $this->username_login();
                }
            }else{
                $this->username_login();
            }

        } else{
            $this->intnetTitle  = $this->getConfig('intnet_title').' - '.C('internetTitle.login');
            $this->referer = $_SERVER['HTTP_REFERER'];
            //$is_start=unserialize(M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->find()['config_value'])['IS_START_CONFIG'];
            $is_start = $this->check_open_sms('开启短信');
            //判断总开关
            if($is_start)
            {
                //$is_start=M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->find()['id'];
                //$is_start=M('TemplateCategory')->where('template_category_id='.$is_start.' AND id=2')->find()['status'];
                $is_start = $this->check_open_sms('登录验证');
            }
            $this->assign('is_start',$is_start);
            $this->display();
        }
    }

    /**
     * 当后台关闭手机验证码登录功能时，登录限制为账号登录
     */

    public function username_login()
    {
        $name=str_replace(' ','',$_POST['name']);//用户输入的账号
//        $condition['mobile'] = $name;
//        $condition['email'] = $name;
        $condition['user_name'] = $name;
//        $condition['_logic'] = 'OR';
        $password = md5($_POST['pwd']); //用户输入的密码
        $result = $this->_model->field('id,mobile,password,integral,member_status,user_name')->where($condition)->find();
        if (empty($result)) {
            $this->ajaxReturn(array('code'=>2));//('账号不存在,请重新输入!',U('login'));
        }else {
            session('user_id',$result['id']);
            session('mobile',$result['user_name']);
            session('member_status',$result['member_status']);
            cookie('user_id',$result['id'],3600*24*7);
            $level = BaseModel::getInstance(UserLevelModel::class)->getUserLevelByLevelId($result['integral']);
            session('discount', $level[UserLevelModel::$discountRate_d] ? $level[UserLevelModel::$discountRate_d] : 100);
            //如果点了自动登录,则把账号信息写入cookie
            if ($_POST['logon'] == 1) {
                cookie('user_id',$result['id'],3600*24*7);
                cookie('mobile',$result['user_name'],3600*24*7);
                cookie('member_status',$result['member_status'],3600*24*7);
            }
            if ($password != $result['password']) {
                $this->ajaxReturn(array('code'=>3));//('密码错误,请重新输入!',U('login'));
            }else{
                $time = time();
                $this->_model->where('id='.$result['id'])->setField('last_logon_time',$time);
                session('reg_tel_code',null);
                $referer = I('referer');
                $this->ajaxReturn(array('code'=>1,'mes'=>'/index.php/Home/Index/index'));
            }
        }
        $this->ajaxReturn(0);//('登录失败!',U('login'));
    }

    public function check_tel_codestatus()
    {
        if($_POST['code'] == $_SESSION['verification'])
        {
            $status=1;
        }else{
            $status=0;
        }

        //if(md5($_POST['code'])==$_POST['print_code'])
        //{
        //    $status=1;
        //}else{
        //    $status=0;
        //}
        $this->ajaxReturn(array('status'=>$status));
    }

/**
 * 注册页面用户名验证是否存在
 */
    public function ajax_check_username()
    {
        $is_exit=M('User')->where(array('user_name'=>$_POST['username']))->find();
        if($is_exit){
            $status=0;
        }else{
            $status=1;
        }
        $this->ajaxReturn(array('status'=>$status));
    }
    //检查是否开启短信服务
    public function check_open_sms($type)
    {
        return M('sms_check')->where(['check_title' => $type])->getField('status');

    }

}