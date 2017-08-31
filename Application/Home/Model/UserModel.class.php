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


use Org\Util\RandString;
use Think\Model;
use Think\Hook;
use Common\Behavior\WangJinTing;
use Common\Model\BaseModel;

/**
 * 用户模型 
 */
class UserModel extends BaseModel
{
    
    private static $obj ;
    

	public static $id_d;	//用户编号

	public static $mobile_d;	//电话号码

	public static $createTime_d;	//创建时间

	public static $status_d;	//账号状态   1正常   0禁用

	public static $updateTime_d;	//更新时间

	public static $openId_d;	//openid是公众号的普通用户的一个唯一的标识

	public static $password_d;	//密码

	public static $userName_d;	//用户名

	public static $nickName_d;	//昵称

	public static $birthday_d;	//生日

	public static $idCard_d;	//身份证号码

	public static $email_d;	//邮箱

	public static $levelId_d;	//等级编号

	public static $sex_d;	//性别

	public static $integral_d;	//积分

	public static $lastLogon_time_d;	//上次登录时间

	public static $salt_d;	//加盐字段： 和密码进行加密，增加密码强度

	public static $recommendcode_d;	//推荐人编码

	public static $validateEmail_d;	//是否验证邮箱

	public static $memberStatus_d;	//0普通会员 1 渠道会员，2 月结会员

	public static $memberDiscount_d;	//折扣率

	public static $pId_d;	//父级会员编号

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return self::$obj = !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    protected function _initialize() {
        Hook::add('reade', WangJinTing::class);
    }
    
    /**
     * 获取 积分余额 
     */
    public function getIntegral($user_id)
    {
        if (empty($user_id) || !is_numeric($user_id)) {
            return array();
        }
        $integral = D('integralUse')->valid($user_id);
        return $integral;
    }

    public function addUser($arr){
        $salt =  RandString::randString();
        $password = $arr['password'];
        $arr['salt'] = $salt;
        $arr['password'] = salt_mcrypt($password,$salt);
        $this->add($arr);
    }

    /**
     * 添加前操作
     */
    protected function _before_insert(&$data,$options)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        return $data;
    }
    //根据user_id查询收货人默认收货地址
    public function getDefaultAddressByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        $where['user_id'] = $user_id;
        $where['status'] = '1';
        $field = 'id,realname,mobile,create_time,prov,city,dist,address,zipcode,email,alias';
        $res = M('user_address')->field($field)->where($where)->find();
        return $res;
    }
    //查询用户收货地址
    public function getAddressByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $field = 'id,realname,mobile,user_id,create_time,update_time,prov,city,dist,address,status,zipcode,email,alias';
        $where['user_id'] = $user_id;
        $res = M('user_address')->field($field)->where($where)->select();
        return $res;
    }
    //查询单条收货地址具体的地区
    public function getRegionByAddress(array $data){
        if(empty($data) ) {   
            return false;
        }        
        $prov = M('region')->field('id,name')->where('id='.$data['prov'])->find();
        $city = M('region')->field('id,name')->where('id='.$data['city'])->find();
        $dist = M('region')->field('id,name')->where('id='.$data['dist'])->find();
        $data['prov'] = $prov['name'];
        $data['city'] = $city['name'];
        $data['dist'] = $dist['name'];      
        return $data;
    }
    //根据data查询具体的地区
    public function getRegionByData(array $data){
        if(empty($data) ) {   
            return false;
        }
        foreach ($data as $key => $value) {
            $prov = M('region')->field('id,name')->where('id='.$value['prov'])->find();
            $city = M('region')->field('id,name')->where('id='.$value['city'])->find();
            $dist = M('region')->field('id,name')->where('id='.$value['dist'])->find();
            $data[$key]['prov'] = $prov['name'];
            $data[$key]['city'] = $city['name'];
            $data[$key]['dist'] = $dist['name'];
        }
        return $data;
    }
    //查询单条收货地址
    public function getAddressById($id){
        if(empty($id) ) {   
            return false;
        }
        $field = 'id,realname,mobile,user_id,create_time,update_time,prov,city,dist,address,status,zipcode,email,alias';
        $where['id'] = $id;
        $res = M('user_address')->field($field)->where($where)->find();
        return $res;
    }
    //查询单条收货地址具体的地区
    public function getRegionById(array $data){
        if(empty($data) ) {   
            return false;
        }    
        $prov = M('region')->field('id,name')->where('id='.$data['prov'])->find();
        $city = M('region')->field('id,name')->where('id='.$data['city'])->find();
        $dist = M('region')->field('id,name')->where('id='.$data['dist'])->find();
        $data['prov'] = $prov['name'];
        $data['city'] = $city['name'];
        $data['dist'] = $dist['name'];
        $data['prov_id'] = $prov['id'];
        $data['city_id'] = $city['id'];
        $data['dist_id'] = $dist['id'];
        return $data;
    }
    //根据user_id查询用户企业信息
    public function getEnterpriseByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $where['user_id'] = $user_id;
        $res = M('enterprise')->where($where)->find();
        if (!empty($res['reg_address'])) {
            $reg_address = explode("-", $res['reg_address']);
            $res['province'] = $reg_address[0];
            $res['city'] = $reg_address[1];
            $res['area'] = $reg_address[2];
        }
        if (!empty($res['place_address'])) {
            $place_address = explode("-", $res['place_address']);
            $res['province1'] = $place_address[0];
            $res['city1'] = $place_address[1];
            $res['area1'] = $place_address[2];
        }
        return $res;
    }

    //根据user_id查询用户信息
    public function getUserByUserId($user_id){
        if(empty($user_id)) {   
            $user_id = $_SESSION['user_id'];
        }
        if (empty($user_id)) {
            return false;
        }
        $where['id'] = $user_id;
        $field = 'id,user_name,nick_name,email,sex,mobile,last_logon_time';
        $res = M('user')->field($field)->where($where)->find();
        return $res;
    }
    //根据data查询用户信息
    public function getUserByData(array $data){
        if (empty($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $where['id'] = $value['user_id'];
            $field = 'id,user_name,nick_name,email,sex,mobile,last_logon_time';
            $res = M('user')->field($field)->where($where)->find();
            $data[$key]['user_name'] = $res['user_name'];
        }        
        return $data;
    }
    //根据用户信息查询头像
    public function getUserHeaderByUser($data){
        if(empty($data) ) {   
            return false;
        }
        $where['user_id'] = $data['id'];
        $field = 'user_header';
        $img = M('user_header')->field($field)->where($where)->find();
        $data['user_header'] = $img['user_header'];
        return $data;
    }
    //根据user_id查询用户的密保问题
    public function getQuestionByUserId($user_id){
         $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $where['user_id'] = $user_id;
        $field = 'id,problem,answer';
        $data = M('security_question')->field($field)->where($where)->select();
        return $data;
    }
    //查询个人积分
    public function getIntegralByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $res = M('User')->field('integral,update_time')->where('id='.$user_id)->find();
        return $res;
    }
}