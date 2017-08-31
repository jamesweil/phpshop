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
namespace Admin\Model;

use Think\Model;
use Common\Model\BaseModel;
use Common\Tool\Tool;
use Common\Model\IsExitsModel;
/**
 * 用户模型
 * @copyright  Copyright © 2003-2023 亿速网络 
 * @link www.yisu.cn
 */
class UserModel extends BaseModel implements IsExitsModel 
{
    
    private static $obj ;

	protected  $selectFiled;

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
        return static::$obj = !(static::$obj instanceof $class) ? new static() : static::$obj;
    }
    
    /**
     * 根据订单信息 查询用户信息
     */
    public function userInfoByOrder(array $orderData, $field)
    {
        if (! is_array($orderData) || empty($orderData['user_id']) || empty($field)) {
            return array();
        }
        return $userInfo = $this ->field($field) ->where('id = "%s"', $orderData['user_id'])->find();
    }
    
    /**
     * 根据咨询信息 查询用户信息
     */
    public function userInfoByConsulate($id, $field)
    {
        if ( ($id = intval($id)) === 0 || empty($field)) {
            return array();
        }
        return $userInfo = $this ->field($field) ->where('id = "%s"', $id)->find();
    }
    
    /**
     * 根据充值记录 获取 用户信息 
     * @param array  $data 充值记录数据
     * @param string $id  组合id标识
     * @param array  $select 查询字段
     * @return array;
     */
    public function getUserByRecharge(array $data, $id, array $select)
    {
        if (empty($data) || empty($id) || !is_array($data) || empty($select) || !is_array($select)) {
            return $data;
        }
        
        
        $idString = Tool::characterJoin($data, $id);
        
        if (empty($idString)) {
            return $data;
        }
        
        $userData = $this->field($select)->where(static::$id_d .' in ('.$idString.')')->select();
        
        $data = Tool::oneReflectManyArray($userData, $data, $id, array(static::$userName_d));
        
        return $data;
        
    }
    
    /**
     * 根据名字 查询数据 
     * @param array $data 
     */
    public function getUserNameByName(array $data)
    {
        if (empty($data) || !is_array($data)) {
            return $data;
        }
        
        $where = $this->create($data);
        
        $userArray = array();
        if (!empty($where[static::$userName_d])) {
            $userArray = $this->field(static::$id_d)->where(static::$userName_d.' = "%s"', $where[static::$userName_d])->select();
        }
        
        return $userArray;
    }
    
    /**
     * 添加用户 
     */
    public function addUser(array $post)
    {
        if (!$this->isEmpty($post)) {
            $this->error ='数据错误';
            return array();
        }

        $isPassWord = false;
        
        $flag = null;
        foreach ($post[static::$password_d] as $value) {
            
            if ($flag == $value && strlen($value) >= 6) {
                $isPassWord = true;
            }
            $flag = $value;
        }
        
        if (!$isPassWord) {
            $this->error ='密码不一致或者密码长度小于6';
            return false;
        }
        
        $post[static::$password_d] = md5($flag);
        $status =  $this->add($post);
        
        return $status;
        
      
    }
    
    protected function _before_insert(& $data, $options) {
        $data[static::$updateTime_d] = time();
        $data[static::$createTime_d] = time();
        return $data;
    }
    
    /**
     * 更新时间 
     */
    protected function _before_update(& $data, $options) {
        $isExits = $this->editIsOtherExit(static::$userName_d, $data[static::$userName_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$userName_d].'】';
            return false;
        }
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    public function getConditionUser ()
    {
        $data = S('COMDITION_USER');
        
        if (empty($data)) {
            
            $data = $this->where(static::$memberStatus_d.' in (1,2)')->getField(static::$id_d.','.static::$userName_d);
            
            S('COMDITION_USER', $data, 10);
        }
        
        return $data;
    }
   
    /**
     * 根据用户名 查找相似 返回编号
     */
    public function getSearchByUser($userName) {
        
        if (empty($userName)) {
            return null;
        }
        $userName = addslashes($userName);
        $data = $this->where(static::$userName_d.' like "'.$userName.'%"')->getField(static::$id_d.','.static::$userName_d);
        
        if (empty($data)) {
            return null;
        }
        $str = '';
        foreach ($data as $key => $value) {
            $str .= ','.$key;
        }
        
        $str = substr($str, 1);
        
        return [
          $userName => ['in', $str]  
        ];
    }
    
    /**
     * 保存用户信息 
     */
    public function saveData(array $post)
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
       
        $flag = 0;
        $password = $this->parsePasswordSame($post[static::$password_d]);
        
        if (empty($password)) {
            unset($post[static::$password_d]);
        } else {
            $post[static::$password_d] = md5($password);
        }
        
        return $this->save($post);
    }
    /**
     * @param unknown $value
     */
    public function set($value)
    {
        $this->selectFiled = $value;
    }
    
    /**
     * 修改状态 
     */
    public function editStatus ($userId, $statusApproval)
    {
        if (($userId = intval($userId)) === 0) {
            $this->rollback();
            $this->error = '数据错误';
            return false;
        }
        
        if (intval($statusApproval) == 1) {
            $saveData = [static::$memberStatus_d=> 1];
        } else {
            $saveData = [static::$memberStatus_d=> 0];
        }
        
        $status = $this->where(static::$id_d.'= %d', $userId)->save($saveData);
       
        if ($status=== false) {
          return $this->traceStation($status);
        }
        return $status;
    }
    
    /**
     * {@inheritDoc}
     * @see \Common\Model\IsExitsModel::IsExits()
     */
    public function IsExits($post)
    {
        // TODO Auto-generated method stub
        if (!$this->isEmpty($post)) {
            return true;
        }
        $name = $this->where(static::$userName_d.'= "%s" or '.(static::$mobile_d).' = "%s"', [$post[static::$userName_d], $post[static::$mobile_d]])->getField(static::$id_d);
        
        return $name ? true : false;
    }
}