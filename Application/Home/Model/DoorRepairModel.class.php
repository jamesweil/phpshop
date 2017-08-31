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

use Common\Model\BaseModel;
use Common\TraitClass\ModelToolTrait;
use Common\Tool\Tool;

/**
 * 上门维修模型 
 */
class DoorRepairModel extends BaseModel{
    public static $id_d;    //上门维修表主键id

    public static $userId_d;    //用户id

    public static $repairProject_d; //维修项目

    public static $repairTime_d;    //维修时间

    public static $repairAddress_d; //维修地点

    public static $tel_d;   //联系电话

    public static $describe_d;  //详细描述

    public static $isYs_d;  //1商城商品2非商城商品

    public static $addTime_d;   //添加时间

    public static $status_d;    //1预约中2上门维修中3维修完成
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    //查询上门维修记录
    public function getListByUserId($user_id){
    	$user_id = $_SESSION['user_id'];
    	if (empty($user_id)) {
    		return false;
    	}
    	$where['user_id'] = $user_id;
    	$_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
    	$data = $this->where($where)->page($_GET['p'].',10')->order('add_time DESC')->select();
    	$count = $this->where($where)->count();
    	$Page = new \Think\Page($count,10);
    	$page = $Page->show();
    	return array('data'=>$data,'page'=>$page);
    }
}