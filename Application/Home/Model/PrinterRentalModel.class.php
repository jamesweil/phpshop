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
 * 打印机租赁 
 */
class PrinterRentalModel extends BaseModel{
    //查询打印机租赁记录
    public function getPrinterRentalByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
        	return false;
        }
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
    	$field = 'id,user_id,start_time,due_time,goods_id,addtime,deposit,status';
    	$res = M('printer_rental')->where('user_id='.$user_id)->page($_GET['p'].',5')->select();
    	$count = M('printer_rental')->where('user_id='.$user_id)->count();
    	$Page = new \Think\Page($count,5);
    	$show = $page->show();      // 分页显示输出
        cookie('page', $show, 86400000000000);
        return $res;
    }
}