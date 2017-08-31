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
use Common\Tool\Tool;
use Common\Model\BaseModel;

/**
 * 团购订单 
 */
class OrderGroupModel extends BaseModel{
    //根据user_id查询所有团购订单
    public function getOrderGroupByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
         	return false;
        }
        $where['user_id'] = $user_id;
        $where['is_del']  = '0';
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $field = 'id,user_id,add_time,edit_time,price_num,status,pay_time,freights,order_sn_id';
        $res   = M('Order_group')->field($field)->where($where)->page($_GET['p'].',5')->select();
        $count = M('Order_group')->where($where)->count();
        $Page  = new \Think\Page($count,5);
        $page  = $Page->show();
        return array('res'=>$res,'page'=>$page,'count'=>$count);
    }
     //根据data查询团购订单商品表信息
    public function getOrderGroupGoodsByData($data){
        if (empty($data)) {
         	return false;
        }
        foreach ($data as $key => $value) {
        	$where['order_id'] = $value['id'];
        	$field = 'id,order_id,goods_id,goods_num,goods_price,add_time';
            $res   = M('Order_group_goods')->field($field)->where($where)->select();
            $data[$key]['goods'] = $res;
        }
        return $data;
    }
    //根据order_id查询团购订单信息
    public function getOrderGroupByOrderId($order_id){
        if (empty($order_id)) {
         	return false;
        }
        $field = 'id,user_id,add_time,edit_time,price_num,status,pay_time,freights,order_sn_id,message';
        $where['id'] = $order_id;
        $res = M('Order_group')->field($field)->where($where)->find();
        return $res;
    }
    //根据order查询团购订单商品表信息
    public function getOrderGroupGoodsByOrder($order){
        if (empty($order)) {
         	return false;
        }
        $field = 'id,order_id,goods_id,goods_num,goods_price,add_time';
        $where['order_id'] = $order['id'];
        $res = M('Order_group_goods')->field($field)->where($where)->select();
        foreach ($res as $key => $value) {
        	$price += $value['goods_num']*$value['goods_price'];
        }
        $order['goods'] = $res;
        $order['goods_price_num'] = $price;
        return $order;
    }
}