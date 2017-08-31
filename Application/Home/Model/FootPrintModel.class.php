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

use Think\Model\AdvModel;

/**
 * 我的足迹模型
 * @author 王强 
 */
class FootPrintModel extends AdvModel
{
    private static $obj ;
    
        
    public static function getInitation()
    {
        $class = __CLASS__;
        return self::$obj = !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    public function add($data, $options = array(), $replace = false)
    {
        if (empty($data))
        {
            return false;
        }
        if ($this->where('gid = "'.$data['gid'].'"')->getField($this->getPk()))
        {
            return false;
        }
        $data = $this->create($data);
        $insertId = parent::add($data);
        
        return  $insertId;
    }
    
    protected function _before_insert(& $data, $options)
    {
        $data['create_time'] = time();
        return $data;
    }

    //查询我的足迹
    public function getMyTracksByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if(empty($user_id)) {   
            return false;
        } 
        $where['uid'] = $user_id;  
        $list = M('foot_print')->where($where)->order('rand()')->limit(6)->select();
        return $list;
    }
    //根据分类id查询我的足迹
    public function getMyTracksByClassId($where){
        if(empty($where)) {   
            return false;
        }
        $count      = M('foot_print')->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        foreach($where as $key=>$val) {    
            $Page->parameter   .=   "$key=".urlencode($val).'&';
        }
        $show       = $Page->show();// 分页显示输出   
        $list = M('foot_print')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();        
        return array('res'=>$list,'page'=>$show);
    }
    //查出该用户所有的足迹
    public function getTracksByUserId($where){
        if(empty($where)) {   
            return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $res = M('foot_print')->field('id,uid,gid as goods_id,goods_pic,goods_name,goods_price')->where($where)->page($_GET['p'].',10')->select();   
        $count =  M('foot_print')->where($where)->count();
        $Page = new \Think\Page($count,10);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();      // 分页显示输出
        return array('res'=>$res,'page'=>$show);
    }
}
