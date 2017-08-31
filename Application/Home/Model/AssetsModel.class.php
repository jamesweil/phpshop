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
//资产中心
class AssetsModel extends Model{
    //查询余额
    public function getBalanceByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $field = 'id,user_id,account_balance,lock_balance,status,modify_time,recharge_time,description';
        $res = M('balance')->field($field)->where('user_id='.$user_id)->order('recharge_time desc')->find();
        return $res;
    }
    //查询近3个月支付记录
    public function getNearPayByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $where['user_id'] = $user_id;
        $where['recharge_time'] = array('GT',time()-60*60*24*30*3);
        $where['status'] = array('GT',2);
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $field = 'id,user_id,account_balance,lock_balance,status,modify_time,recharge_time,description';
        $res = M('balance')->field($field)->where($where)->page($_GET['p'].',5')->order('recharge_time desc')->select();
        $count =  M('balance')->where($where)->count();
        $page = new \Think\Page($count,5);      // 实例化分页类 传入总记录数和每页显示的记录数
        foreach ($res as $key => $value) {          
                $res[$key]['pay'] = $res[$key]['lock_balance']-$res[$key]['account_balance'];
                $res[$key]['deposit'] = $res[$key]['account_balance']-$res[$key]['lock_balance'];
                if ($res[$key]['pay'] < 0) {
                    $res[$key]['pay'] = 0;
                }
                if ($res[$key]['deposit'] < 0) {
                    $res[$key]['deposit'] = 0;
                }
            $res[$key]['recharge_time'] = date('Y-m-d H:i:s',$value['recharge_time']);
        }
        foreach($where as $key=>$val) {    
            $Page->parameter.="$key=".urlencode($val).'&';
        }
        $show = $page->show();      // 分页显示输出
        return array('res' =>$res, 'page' => $show);
    }
    //查询3个月前的支付记录
    public function getFrontPayByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $where['user_id'] = $user_id;
        $where['recharge_time'] = array('LT',time()-60*60*24*30*3);
        $where['status'] = array('GT',2);
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $field = 'id,user_id,account_balance,lock_balance,status,modify_time,recharge_time,description';
        $res = M('balance')->field($field)->where($where)->page($_GET['p'].',5')->order('recharge_time desc')->select();
        $count =  M('balance')->where($where)->count();
        $page = new \Think\Page($count,5);      // 实例化分页类 传入总记录数和每页显示的记录数
        foreach ($res as $key => $value) {          
                $res[$key]['pay'] = $res[$key]['lock_balance']-$res[$key]['account_balance'];
                $res[$key]['deposit'] = $res[$key]['account_balance']-$res[$key]['lock_balance'];
                if ($res[$key]['pay'] < 0) {
                    $res[$key]['pay'] = 0;
                }
                if ($res[$key]['deposit'] < 0) {
                    $res[$key]['deposit'] = 0;
                }
            $res[$key]['recharge_time'] = date('Y-m-d H:i:s',$value['recharge_time']);
        }
        foreach($where as $key=>$val) {    
            $Page->parameter.="$key=".urlencode($val).'&';
        }
        $show = $page->show();      // 分页显示输出
        return array('res' =>$res, 'page' => $show);
    }
    //查询个人积分使用
    public function getIntegralUseByUserId($type){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)) {   
            return false;
        }
        $where['user_id'] = $user_id;
        if(!empty($type)) {
            $where['type'] = $type;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];        
        $res = M('integral_use')->field('id,user_id,integral,goods_id,trading_time,remarks,type')->where($where)->page($_GET['p'].',10')->select();
        $count = M('integral_use')->where($where)->count();
        $Page  = new \Think\Page($count,10);
        $page  = $Page->show();
        return array('res'=>$res,'page'=>$page);
    }

    ////查询个人积分兑换的商品列表
    public function getGoodsByPunkte($range){ 
        if(!empty($range)) {   
            $where['integral'] = $range;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['status'] = 0;
        $res = M('integral_goods')->field('id,goods_id,integral')->where($where)->page($_GET['p'].',12')->order('integral ASC')->select();
        $count =  M('integral_goods')->where($where)->count();
        $page = new \Think\Page($count,12);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();      // 分页显示输出
        return array('res' =>$res, 'page' => $show);
    }

    //查询商品图片
    public function getImageByGoodsId(array $goods){
        if (empty($goods)) {
            return false;
        } 
        foreach ($goods['res'] as $key => $value) {
            $where['goods_id'] = $value['id']; 
            $img = M('goods_images')->field('pic_url')->where($where)->find();
            $goods['res'][$key]['images'] = $img['pic_url'];
        }
        return $goods;
    }
    // //查询我购买过的订单
    public function getGekauftByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)) {   
            return false;
        }
        $where['user_id'] = $user_id;
        $where['order_status'] = '4';
        $res = M('Order')->field('id,create_time,order_sn_id,order_status,price_sum,freight_id')->where($where)->select();
        return $res;
    }
    // //根据订单id查询我购买过的订单的商品信息
    public function getGoodsByOrderId(array $order){
        if(empty($order)) {   
            return false;
        }
        foreach ($order as $key => $value) {
            $where['order_id'] = $value['id'];
            $field = 'goods_price,goods_num,space_id,goods_id,order_id,goods_id,status';
            $order[$key]['goods'] = M('order_goods')->where($where)->select();
        }      
        return $order;
    }
    // //根据商品信息查询图片
    public function getGoodsImageByGoods(array $goods){
        if(empty($goods)) {   
            return false;
        }
        foreach ($goods as $key => $value) {
            foreach ($value['goods'] as $k => $v) {
                $where['goods_id'] = $v['goods_id']; 
                $img = M('goods_images')->field('pic_url')->where($where)->find();
                $goods[$key]['goods'][$k]['images'] = $img['pic_url']; 
            }
        }      
        return $goods;
    }
    // //根据订单查询运费
    public function getFreightByOrderId(array $order){
        if(empty($order)) {   
            return false;
        }
        foreach ($order as $key => $value) {
            $where['freight_id'] = $value['freight_id'];
            $res = M('freight_condition')->field('mail_area_monery')->where($where)->find();
            $order[$key]['mail_area_monery'] = $res['mail_area_monery'];
        }      
        return $order;
    }
    // //根据订单查询快递公司名
    public function getExpressTitleByFreightId(array $order){
        if(empty($order)) {   
            return false;
        }
        foreach ($order as $key => $value) {
            $where['freight_id'] = $value['freight_id'];
            $res = M('freights')->field('express_title')->where($where)->find();
            $order[$key]['express_title'] = $res['express_title'];
        }      
        return $order;
    }
    //根据user_id查询收藏表全部商品id
    public function getCollectionWholeByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)) {   
            return false;
        }
        $where['user_id'] = $user_id;
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $res = M('Collection')->where('user_id='.$user_id)->page($_GET['p'].',10')->select();
        $count =  M('Collection')->where($where)->count();
        $page = new \Think\Page($count,10);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();      // 分页显示输
        return array('res'=>$res,'page'=>$show); 
    }
    //根据user_id查询收藏表全部降价商品商品id
    public function getCollectionPriceByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)) {   
            return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;
        $where['status'] = '1';     
        $res = M('Collection')->where($where)->page($_GET['p'].',10')->select();
        $count =  M('Collection')->where($where)->count();
        $page = new \Think\Page($count,10);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();      // 分页显示输出
        return array('res'=>$res,'page'=>$show); 
    }
    
    //ajax搜索收藏表
    public function getGoodsBySearch($name){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)) {   
            return false;
        }
        if(empty($name)) {   
            return false;
        }
        $where['user_id'] = $user_id;
        $where['goods_name'] = array('like',"%$name%");
        $res = M('Collection')->where($where)->select();
        return $res; 
    }
}
