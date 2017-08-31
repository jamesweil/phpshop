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
use Think\Model;

/**
 * 用户地址模型 
 */
class UserAddressModel extends Model
{
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
    
    /**
     * 获取默认地址 
     */
    public function getDefaultAddress($userId)
    {
        if ( empty($userId) || !is_numeric($userId))
        {
            return array();
        }
        
        $count = $this->field('id')->where('user_id = "'.$userId.'" and status = 1')->count();
        
        $res_addr_alone = $this->field('prov,city,dist,address,realname,mobile,alias')->where('user_id = "'.$userId.'" and status = 1')->find();
        
        if(empty($res_addr_alone)){
            $res_addr_alone = $this->field('prov,city,dist,address,realname,mobile,alias')->where('user_id = "'.$userId.'"')->order('create_time')->find();
        }
        if(!empty($res_addr_alone)){
        
            $addr_alone = $res_addr_alone['prov'].$res_addr_alone['city'].$res_addr_alone['dist'].$res_addr_alone['address'];
            $res_addr_alone['addr_alone'] = $addr_alone;
        }
        
        return array('count' => $count, 'res_ad' => $res_addr_alone);
    }


    /**
     * 获取用户收货地址
     * @param  integer  $user_id 用户ID
     * @param  boolean  $default 是否只获取默认收货地址
     * @return array
     */
    public function getAddrByUser($user_id, $default = true)
    {
        if (empty($user_id) || !is_numeric($user_id)) {
            return array();
        }

        $where['user_id'] = $user_id;
        $default ? $where['status'] = 1 : true;
        $field = 'id,prov,city,dist,address,realname,mobile,alias,status';
        $data  = $this->field($field)->where($where)->order('status DESC,create_time DESC')->select();
        if (!is_array($data)) {
            return array();
        }

        $region = M('region');
        foreach ($data as &$addr) {
            $temp = $region->field('name')->find($addr['prov']);
            $addr['prov_name']  = $temp['name'];
            $temp = $region->field('name')->find($addr['city']);
            $addr['city_name']  = $temp['name'];
            $temp = $region->field('name')->find($addr['dist']);
            $addr['dist_name']  = $temp['name'];
            $addr['addr_alone'] = $addr['prov_name'].$addr['city_name'].$addr['dist_name'].$addr['address'];
        }
        return $default ? array_shift($data) : $data;
    }


    /**
     * 通过地址获取指定信息
     * @param  integer $addr_id 地址id
     * @return array
     */
    public function getAddrById($addr_id)
    {
        $field = 'id,prov,city,dist,address,realname,mobile,alias,status,zipcode';
        $addr  = $this->field($field)->find($addr_id);
        if (!is_array($addr)) {
            return array();
        }

        $region             = M('region');
        $temp               = $region->field('name')->find($addr['prov']);
        $addr['prov_name']  = $temp['name'];
        $temp               = $region->field('name')->find($addr['city']);
        $addr['city_name']  = $temp['name'];
        $temp               = $region->field('name')->find($addr['dist']);
        $addr['dist_name']  = $temp['name'];
        $addr['addr_alone'] = $addr['prov_name'].$addr['city_name'].$addr['dist_name'].$addr['address'];
        return $addr;
    }


    /**
     * 根据商品信息【 查询地址】
     */
    public function goodsAdressByOrder(array $data)
    {
        if (empty($data) || !is_array($data))
        {
            return array();
        }
      
        $ids = Tool::characterJoin($data, 'address_id');
        
        if (empty($ids)) {
            return array();
        }
        $filed = array('prov','city,dist','address', 'realname', 'mobile');
        $address = $this->field('id as address_id,'.implode(',', $filed))->where('id in ('.$ids.')')->select();
        //此处 牵扯到 【一个收货地址 ，或多个】
        if (empty($address)) {
            return array();
        }
        $orderData = Tool::oneReflectManyArray($address, $data, 'address_id', $filed);
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
     * 编辑
     */
    public function edite($data)
    {
        if (empty($data)) {
            return false;
        }
        $data['update_time'] = time();
        return $this->save($data);
    }

}