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
//特色业务
class SpecialBusinessModel extends Model{
    //查询打印机租赁记录
    public function getPrinterRentalByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
        	return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $res = M('printer_rental')->where('user_id='.$user_id)->page($_GET['p'].',5')->select();
        $count = M('printer_rental')->where('user_id='.$user_id)->count();
        $Page = new \Think\Page($count,5);
        $show = $Page->show();
        return array('res'=>$res,'page'=>$show);
    }
    //查询用户补充耗材记录
    public function getRecordByUserId(&$user_id,$printer_id){
        if (empty($user_id)) {
            return false;
        }
        if (empty($printer_id)) {
            return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['user_id'] = $user_id;//用户id
        $where['printer_id']= $printer_id;//打印机租赁id
        $field = 'id,user_id,printer_id,add_time,consumables,num,status,remark';
        $res = M('supplementary_supplies')->field($field)->where($where)->page($_GET['p'].',10')->select();
        $count = M('supplementary_supplies')->where($where)->count();
        $Page = new \Think\Page($count,10);
        $show = $Page->show();
        return array('res'=>$res,'page'=>$show);
    }
     //查询单个打印机租赁记录
    public function getPrinterRentalByPrinterId($printer_id){
        if (empty($printer_id)) {
            return false;
        }
        $where['id'] = $printer_id;
        $res = M('printer_rental')->where($where)->find();
        return $res;
    }
    //查询最近抄表记录
    public function getRecentMeterReadingByPrinterId($printer_id){
        if (empty($printer_id)) {
            return false;
        }
        $where['printer_id'] = $printer_id;
        $res = M('printer_meter')->where($where)->order('meter_time DESC')->find();
        return $res;
    }
    //查询所有抄表记录
    public function getRecentMeterAllByPrinterId($printer_id){
        if (empty($printer_id)) {
            return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['printer_id'] = $printer_id;
        $res = M('printer_meter')->where($where)->page($_GET['p'].',10')->order('meter_time DESC')->select();
        $count = M('printer_meter')->where($where)->count();
        $Page = new \Think\Page($count,10);
        $show = $Page->show();
        return array('res'=>$res,'page'=>$show);
    }
    //查询最近抄表记录
    public function getMonthMeterByData(array $data){
        if (empty($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $time  = strtotime(date('Y-m',time()));//当月时间
            $time2 = strtotime(date('Y-m',strtotime("1 month")));//下月时间
            $where['printer_id'] = $value['id'];
            $where['meter_time'] = array(array('GT',$time),array('LT',$time2,'AND'));
            $res = M('printer_meter')->where($where)->find();
            $data[$key]['pay_price']  =  $res['pay_price'];
            $data[$key]['pay_status'] = $res['pay_status'];
            $data[$key]['meter_time'] = $res['meter_time'];            
        }
       return $data;
    }
    //查询申请退回押金审核记录
    public function getDepositDetailsByPrinterId($printer_id){
        if (empty($printer_id)) {
            return false;
        }
        $where['printer_id'] = $printer_id;
        $res = M('printer_apply_for_deposit')->where($where)->order('add_time DESC')->select();
        foreach ($res as $key => $value) {
            $res[$key]['sunday'] = $this->getTimeWeek($value['add_time']);
        }
        
        return $res;
    }
    //打印机租赁搜索
    public function getPrinterRentalByQuery(){
        if (empty($_POST)) {
            return false;
        }
        $where['user_id'] = $_SESSION['user_id'];
        if (!empty($_POST['goods_id'])) {
            $where['goods_id'] = I('post.goods_id');
        }
        if (!empty($_POST['start_time'])) {
            $where['start_time'] = I('post.start_time');
        }
        if (!empty($_POST['due_time'])) {
            $where['due_time'] = I('post.due_time');
        }
        if (!empty($_POST['status'])) {
            $where['status'] = I('post.status');
        }
        $res = M('printer_rental')->where($where)->select();
        $page = '';
        return array('res'=>$res,'page'=>$page);
    }
    //查询采购需求的商品列表
    public function getGoodsByProduct($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        $where['user_id'] = $user_id;
        $where['status']  = '0';
        $res = M('characteristic_purchase_product')->where($where)->select();
        return $res;
    }
}