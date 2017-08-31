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
//客户服务
class ServiceModel extends Model{
    //查询投诉记录
    public function getReportByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $field = 'id,user_id,goods_id,reason,content,time,status';
        $res = M('report')->field($field)->where('user_id='.$user_id)->order('time desc')->select();
        return $res;
    }
    //查看售后记录
    public function getRepairByUserId($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $res = M('order_return_goods')->where('user_id='.$user_id)->order('create_time desc')->page($_GET['p'].',10')->select();
        $count =  M('order_return_goods')->where('user_id='.$user_id)->count();     // 查询满足要求的总记录数
        $page = new \Think\Page($count,10);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();      // 分页显示输出
        return array('res' =>$res, 'page' => $show); 
    }
    //查看售后记录详情
    public function getRepairDetailById($id){
        if(empty($id) ) {   
            return false;
        } 
        $res = M('order_return_goods')->where('id='.$id)->find();
        return $res;
    }
    //查看网站公告
    public function getAnnouncement(){
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $field = 'id,title,create_time';
        $res = M('announcement')->field($field)->order('create_time desc')->page($_GET['p'].',15')->select();
        $count =  M('announcement')->count();     // 查询满足要求的总记录数
        $page = new \Think\Page($count,15);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();      // 分页显示输出
        return array('res' =>$res, 'page' => $show);
    }
    //查看网站公告详情
    public function getAnnouncementDetailsById($id){
        if(empty($id) ) {   
            return false;
        } 
        $field = 'id,title,create_time,intro,content';
        $res = M('announcement')->field($field)->where('id='.$id)->find();
        $res['content'] = html_entity_decode($res['content']);
        return $res;
    }
    //查询该用户所有订单
    public function getOrderByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $res = M('Order_goods as a')->field('a.id,a.goods_price,a.order_id,a.goods_id,b.create_time')->join('db_order as b on b.id=a.order_id')->where('b.user_id='.$user_id)->select();       
        return $res;
    }
    //查询返修退换货
    public function getReturnRepairByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $field = 'id,tuihuo_case,type,create_time,status';
        $res = M('order_return_goods')->field($field)->where('user_id='.$user_id)->page($_GET['p'].',10')->select();
        $count =  M('order_return_goods')->where('user_id='.$user_id)->count();     // 查询满足要求的总记录数
        $page = new \Think\Page($count,10);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();      // 分页显示输出
        return array('res' =>$res, 'page' => $show);
    }
    //查询用户提出的问题
    public function getProblemByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id) ) {   
            return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $field = 'id,problem';
        $data = M('problem')->field($field)->where('user_id='.$user_id)->page($_GET['p'].',5')->select();
        $count =  M('problem')->where('user_id='.$user_id)->count();     // 查询满足要求的总记录数
        $page = new \Think\Page($count,5);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();      // 分页显示输出
        return array('res'=>$data,'page'=>$show);
    }
    //查询问题对应的答案
    public function getAnswerByProblem(array $problem){
        if(empty($problem)) {   
            return false;
        }
        foreach ($problem['res'] as $key => $value) {
            $where['problem_id'] = $value['id'];
            $res = M('Answer')->field('answer')->where($where)->find();
            $problem['res'][$key]['answer'] = $res['answer'];
        }
        return $problem;
    }
}