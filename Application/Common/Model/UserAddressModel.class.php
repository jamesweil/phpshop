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

namespace Common\Model;
use Think\Model;

/**
 * 用户地址模型 
 */
class UserAddressModel extends BaseModel
{

    private static $obj ;

    private $isDefault = true;

	public static $id_d;	//id

	public static $realname_d;	//名字

	public static $mobile_d;	//手机号

	public static $userId_d;	//user_id

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

	public static $provId_d;	//省

	public static $city_d;	//城市编号

	public static $dist_d;	//区域编号

	public static $address_d;	//地址说

	public static $status_d;	//是否默认地址    默认 1   不默认 0

	public static $zipcode_d;	//邮编

	public static $alias_d;	//地址别名

	public static $email_d;	//电子邮件

	public static $telphone_d;	//座机


    public static function getInitnation()
    {
        $class = __CLASS__;
        return self::$obj = !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    /**
     * 获取用户地址信息 
     */
    public function getUserAddressInfo(array $options)
    {
        if (!is_array($options) || empty($options) )
        {
            return array();
        }
        
        return $this->select($options);
    }
    
    protected function _before_insert( & $data, $options)
    {
        $data[self::$updateTime_d] = time();
        $data[self::$createTime_d] = time();
        $data[self::$userId_d]     = $_SESSION['user_id'];
        //是否默认
        if ($this->isDefault) {
            $data[self::$status_d] = 1;
        }
        return $data;
    }
    
    protected function _before_update( & $data, $options)
    {
        $data[self::$updateTime_d] = time();
        return $data;
    }
    
    /**
     * 获取默认地址
     */
    public function getDefaultAddress($userId)
    {
        if (($userId = intval($userId)) === 0) {
            return array();
        }
        
        $data = S('userRegion');
        
        if (empty($data)) {
            
            $option = [
                'field' => [
                    self::$createTime_d,
                    self::$updateTime_d,
                    self::$zipcode_d
                ],
                'where' => [
                    self::$userId_d => $userId,
                    self::$status_d => 1
                ]
            ];
            
            $data = $this->getAttribute($option, true, 'find');
            
            S('userRegion', $data, 5);
        }
        
        return (array)$data;
    }
    
    /**
     * 获取 用户收货地址列表 
     */
    public function getAreaListByUserId ($userId, $fiter = FALSE)
    {
        if (($userId = intval($userId)) === 0) {
            return array();
        }
        
        $data = S('USER_ADDRESS_DATA');
        
        if (empty($data)) {
            $colum = $this->selectColums;
            
            $colum = empty($colum) ? $this->getDbFields() : $colum;
            
            $data = $this->field($this->selectColums, $fiter)->where(self::$userId_d.'= %d', $userId)->select();
            
            if (empty($data)) {
                return $data;
            }
            
            S('USER_ADDRESS_DATA', $data, 10);
        }
        return $data;
    }
    
    /**
     * 根据商品信息【 查询地址】
     */
    public function goodsAdressByOrder(array $data, $primary)
    {
        if (empty($data) || !is_array($data))
        {
            return array();
        }
        
        $orderData = $this->getDataByOtherModel($data, $primary, [
            self::$id_d,
            self::$provId_d,
            self::$realname_d,
            self::$mobile_d,
            self::$city_d,
            self::$dist_d,
        ], self::$id_d);
        return $orderData;
    }
    
    /**
     * 根据收货人 查询订单 
     */
    public function getOrderByRealName(array $post)
    {
        if (empty($post))
        {
            return array();
        }
        $where = $this->create($_POST);
       
        $userArray = array();
        if (!empty($where['realname'])) {
            $userArray = $this->field('id')->where('realname = "%s"', $where['realname'])->select();
        }
        return $userArray;
    }
    
    /**
     * 获取一条数据 
     */
    public function getOne ($id, $field = null) 
    {
        if ( ($id = intval($id)) === 0 ) {
            return array();
        }
        
        
        if (!empty($field)) {
            return $this->field($field)->where(self::$id_d.'=%d', $id)->find();
        } 
        
        return $this->field(self::$updateTime_d.','.self::$createTime_d, true)->where(self::$id_d.'=%d', $id)->select();
    }
    
    /**
     * 添加
     */
    public function addUserAddress(array $post)
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        $status = false;
        
        if ($this->isDefault) {
            $status = $this->where(self::$userId_d.'="%s"', $_SESSION['user_id'])->save(array(
                self::$status_d => 0
            ));
        }
        
        $status = $this->add($post);
        
        return $status;
        
    }
    
    /**
     * 根据订单信息 查询用户信息
     */
    public function receiveManByOrder(array $orderData, $field, $fiter = FALSE)
    {
        if (! is_array($orderData) || empty($orderData['address_id']) || empty($field)) {
            return array();
        }
        return $userInfo = $this ->field($field, $fiter) ->where('id = "%s"', $orderData['address_id'])->find();
    }
    //根据订单信息查询用户收货信息
    public function getUserAddressByData(array $data){
        if (empty($data)){
            return false;
        }
        foreach ($data as $key => $value) {
            $where['id'] = $value['address_id'];
            $field = 'id,realname,mobile,prov,city,dist,address,zipcode,status';
            $res = M('User_address')->field($field)->where($where)->find();
            $data[$key]['realname'] = $res['realname'];
        }
        return $data;
    }
    
    /**
     * 获取地址数据 
     */
    public function getAddressById ($id)
    {
        if (($id = intval($id)) === 0) {
            return array();
        }
        return $this->getOne($id, $this->getDbFields());
    }
    
    /**
     * @return the $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }
    
    /**
     * @param boolean $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    }
}