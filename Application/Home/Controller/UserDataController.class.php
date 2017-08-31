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
use Home\Model\LoginModel;
use Home\Model\RegionModel;

//个人资料
class UserDataController extends BaseController{
    //判断是否登录
     public function __construct()
    {
        parent::__construct();
        
        $this->isLogin();
    }
    //图片上传属性设置
    protected $config = array(
        'mimes'         =>  array(), //允许上传的文件MiMe类型
        'maxSize'       =>  3145728, //上传的文件大小限制 (0-不做限制)
        'exts'          =>  'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'       =>  true, //自动子目录保存文件
        'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath'      =>  './Uploads/', //保存根路径
        'savePath'      =>  'header/', //保存路径
        'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
        'replace'       =>  false, //存在同名是否覆盖
        'hash'          =>  true, //是否生成hash编码
        'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
        'driver'        =>  '', // 文件上传驱动
        'driverConfig'  =>  array(), // 上传驱动配置
    );
    //个人资料
    public function user_data(){
        if (IS_POST) {
            $data = $_POST;
            $where['id'] = I('post.id');
            $res = M('user')->where($where)->save($data);
            if (!$res) {
                $this->error('保存失败');
            }
            $this->success('保存成功');exit;
        }
        //查询用户信息
        $user = UserModel::getUserByUserId();
        //查询用户头像
        $data = UserModel::getUserHeaderByUser($user);
        $this->assign('data',$data);
        $this->display();
    }
    //编辑头像
    public function head_edit(){
        if (IS_POST) {
            $user = M('User_header');
            if ($_FILES['header']['error'] == 0) {
                $upload = new \Think\Upload($this->config);// 实例化上传类
                //上传文件
                $info = $upload->upload();         
                if(!$info) {        // 上传错误提示错误信息
                    $this->error($upload->getError());
                }
                $data['user_header'] = '/'.Uploads.'/'.$info['header']['savepath'].$info['header']['savename'];
                $data['user_id']  = $_SESSION['user_id'];
                $where['user_id'] = $_SESSION['user_id'];
                $res = $user->where($where)->find();
                if (empty($res)) {
                    $result=$user->data($data)->add();
                    if (!$result) {
                        $this->error('保存失败!');
                    }
                    $this->success('保存成功!',U('user_data'));exit;
                }else{
                    $img = $res['user_header'];
                    $rst= @unlink("Uploads/$img");
                    $result=$user->where($where)->save($data);
                    if (!$result) {
                        $this->error('保存失败!');
                    }
                    $this->success('保存成功!',U('user_data'));exit;
                }
            }
        }else{
            $this->display();
        }
    }

    
    /**
     * 账号绑定
     * 登录类型: 1.手机登录,2.邮箱登录,3.支付宝登陆,4.qq登录5.微信登录,6.微博登录
     */
    public function bind_account()
    {
        $user_id = $_SESSION['user_id'];
        $temp    = D('userAuths')->authList($user_id);
        foreach ($temp as &$auth) {
            $list[$auth['identity_type']] = $auth;
        }
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 绑定支付宝
     */
    public function bindAlipay()
    {
        
        $act = I('act', 0);
        switch ($act) {
            case 1:
                return (new LoginModel())->alipay(1);
            case 2:
                $ret = M('userAuths')->where(['user_id'=>$_SESSION['user_id'], 'identity_type'=>3])->delete();
                break;
            default:
                break;
        }
        if (!$ret) {
            $this->error('处理失败');
        }
        $this->redirect('bind_account');
    }

    /**
     * 支付宝授权通知
     */
    public function Alipaynotice()
    {
        $auth = (new LoginModel())->alipay(2);
        $param = [
          'user_id'       => $_SESSION['user_id'],
          'identity_type' => 3,
          'identifier'    => $auth['user_id'],
          'credential'    => $auth['token'],
          'expires_in'    => -1, // 永久有效
          'local'         => 0   // 非本地账户
        ];
        $ret = D('userAuths')->saveInfo($param);
        $this->redirect('bind_account');
    }


    /**
     * 绑定微博
     */
    public function bindWeibo()
    {
        $act = I('act', 0);
        switch ($act) {
            case 1:
                // 检测是否已经绑定过了
                $data['referer'] = '/home/userData/bind_account';
                session('bind_auth_base', $data);
                $this->redirect('/home/public/login', ['type'=>1]);
                break;

            case 2:
                $where = ['user_id' => $_SESSION['user_id'], 'identity_type' => 6];
                $info  = M('userAuths')->field('credential')->where($where)->find();
                $ret   = (new LoginModel)->weibo(4, ['access_token'=>$info['credential']]);
                if ($ret['result'] == 'true') {
                    $ret = M('userAuths')->where($where)->delete();
                    if ($ret > 0) {
                        $this->redirect('bind_account');
                    }
                }
                $this->error('解绑失败');
                break;
            
            default:
                # code...
                break;
        }
    }

    /**
     * 绑定微信
     */
    public function bindWechat()
    {
        
    }

    /**
     * 绑定QQ
     */
    public function bindQQ()
    {
        
    }

    /**
     * 绑定人人网
     */
    public function bindRenren()
    {
        
    }

    /**
     * 绑定豆瓣
     */
    public function bindDouban()
    {
        
    }
    //申请账期支付
    public function special_application(){
        if (IS_POST) {
            $where['user_id'] = $_SESSION['user_id'];
            $res = M('enterprise_vip')->where($where)->find();
            if (empty($res)) {
                $data['company_name'] = I('post.company_name');//公司名称
                $data['company_nature'] = I('post.company_nature');//公司性质
                $data['managementtype'] = I('post.managementtype');//经营类型
                $data['prov'] = I('post.province');//请选择省份
                $data['city'] = I('post.city');//城市
                $data['dist'] = I('post.area');//地区
                $data['address'] = I('post.address');//详细地址
                $data['apply_name'] = I('post.apply_name');//申请人
                $data['applytel'] = I('post.applytel');//申请人联系电话
                $data['respon_name'] = I('post.respon_name');//对账人
                $data['respontel'] = I('post.respontel');//对账人联系电话
                $data['estimate'] = I('post.estimate');//每月采购金额
                $data['remarks'] = I('post.remarks');//备注
                $data['status'] = 0;
                $data['create_time'] = time();//添加时间
                $data['user_id'] = $_SESSION['user_id'];//用户id
                $result = M('enterprise_vip')->data($data)->add();
                if (!$result) {
                    $this->error('申请失败!');
                }
                $this->success('申请成功!');exit;
            }
            $this->error('你已经申请过了!');
        }
        //查询省份
        $region = BaseModel::getInstance(RegionModel::class);
        $province = $region->getProvince();
        $this->assign('province',$province);
        $this->display();
    }
    //根据provinceid查询下级
    public function region_ajax(){
        if (IS_POST) {
            $id  = I('post.id');
            $region = BaseModel::getInstance(RegionModel::class);
            $data = $region->getProvinceByProvinceId($id);
            $this->ajaxReturn($data);
        }
    }
}
