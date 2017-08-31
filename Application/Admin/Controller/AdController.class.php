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

/*
 * @thinkphp3.2.2  auth认证   php5.3以上
 * @Created on 2015/08/18
 * @Author  夏日不热(老屁)   757891022@qq.com
 *
 */
namespace Admin\Controller;

use Common\Controller\AuthController;
use Common\Tool\Tool;

/**
 * 广告管理
 */
class AdController extends AuthController
{

    /**
     * 通过id获取广告内容
     */
    public function ad()
    {
        $act     = I('GET.act','add');
        $ad_id   = I('GET.ad_id');
        $ad_info = array();
        if ($ad_id) {
            $ad_info = D('ad')->where('id='.$ad_id)->find();
            $ad_info['start_time'] = date('Y-m-d',$ad_info['start_time']);
            $ad_info['end_time']   = date('Y-m-d',$ad_info['end_time']);            
        }
        if ($act == 'add') {
           $ad_info['ad_space_id'] = I('GET.ad_space_id');
        }
        $space = D('ad_space')->select();
        $this->assign('act', $act);
        $this->assign('info', $ad_info);
        $this->assign('space', $space);
        $this->display();
    }
    

    /**
     * 广告内容列表
     */
    public function adList()
    {
        $Ad    = M('ad'); 
        $where = "1=1";
        if(I('pid')){
            $where = "ad_space_id=".I('pid');
            $this->assign('pid',I('pid'));
        }
        $keywords = I('keywords',false);
        if($keywords){
            $where = "title like '%$keywords%'";
        }
        $count = $Ad->where($where)->count(); // 查询满足要求的总记录数
        $page  = new \Think\Page($count,10);  // 实例化分页类 传入总记录数和每页显示的记录数
        $list  = $Ad->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
        if (!is_array($list)) {
            $list = array();
        }
                                     
        $ad_space_list = M('AdSpace')->getField("id as space_id, name as space_name,is_open"); 
        $this->assign('ad_space_list',$ad_space_list);//广告位 
        $this->assign('page', $page->show());   // 赋值分页输出
        $this->assign('list', $list);           // 赋值数据集
        $this->display();
    }


    /**
     * 广告内容处理
     */
    public function adHandle()
    {
        $data = I('post.');
        switch ($data['act']) {
            case 'add':
                $data['pic_url']    = '/'.$data['pic_url'];
                $data['start_time'] = strtotime($data['start_time']);
                $data['end_time']   = strtotime($data['end_time']);
                $r = D('ad')->add($data);
                break;
            case 'edit':
                $data['pic_url']    = '/'.ltrim($data['pic_url'], '/');
                $data['start_time'] = strtotime($data['start_time']);
                $data['end_time']   = strtotime($data['end_time']);
                $r = D('ad')->save($data);
                break;
            case 'del':
                $r = D('ad')->where('id='.$data['del_id'])->delete();
                echo intval($r);exit;
            default:
                break;
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Ad/adList');
        
        if($r){
            $this->success("操作成功", U('Admin/Ad/adList'));
        }else{
            $this->error("操作失败", $referurl);
        }
    }

    
    /**
     * 根据广告位id获取广告位
     */
    public function space()
    {
        $act      = I('GET.act','add');
        $space_id = I('GET.space_id');
        $info     = array();
        if($space_id){
            $info = M('ad_space')->where('id='.$space_id)->find();
            $this->assign('info',$info);
        }
        $this->assign('act',$act);
        $this->display();
    }
    

    /**
     * 获取广告位列表
     */
    public function spaceList()
    {
        $space = M('ad_space');
        $count = $space->where('1=1')->count();// 查询满足要求的总记录数
        $Page  = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list  = $space->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('list',$list);// 赋值数据集                
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }


    /**
     * 广告位处理
     */
    public function spaceHandle()
    {
        $data = I('post.');
        if ($data['act'] != 'del') {
            Tool::checkPost($data, array('is_numeric' => array('width', 'height'), 'id', 'act'), true, 
                array('name','remark','is_open')) ? true : $this->error("参数错误", U('Admin/Ad/spaceList'));
        }
        switch ($data['act']) {
            case 'add':
                $ret = M('ad_space')->add($data);
                break;
            case 'edit':
                $ret = M('ad_space')->save($data);
                break;
            case 'del':
                $count = M('ad')->where('ad_space_id='.$data['del_id'])->count();
                if(intval($count) > 0) {
                    $this->error("此广告位下还有广告，请先清除", U('Admin/Ad/spaceList'));
                }
                $ret = M('ad_space')->where('id='.$data['del_id'])->delete();
                echo is_numeric($ret) ? 1 : 0;
                exit;
            default:
                break;
        }

        if($ret){
            $this->success("操作成功", U('Admin/Ad/spaceList'));
        }else{
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Ad/spaceList');
            $this->error("操作失败", $referurl);
        }
    }
    

    /**
     * 修改字段
     */
    public function changeAdField()
    {
        $data  = I('GET.');
        $table = $data['table'];
        unset($data['table']);
        $ret   = M($table)->save($data);
        if($ret){
            $this->success("操作成功",U('Admin/Ad/adList'));
        }else{
            $this->error("操作失败",$referurl);
        }
    }


    /**
     * 修改表字段
     */
    public function changeTableVal()
    {
        $data  = I('GET.');
        $table = $data['table'];
        $data  = array(
            $data['id_name'] => $data['id_value'],
            $data['field']   => $data['value'],
            );
        $ret   = M($table)->save($data);
        $this->ajaxReturn(['status'=>intval($ret)]);
    }
}