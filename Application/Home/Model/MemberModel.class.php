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


/**
 * Created by PhpStorm.
 * User: jiangqingfeng
 * Date: 2016/7/10
 * Time: 18:38
 */
namespace Home\Model;

use Think\Model;
class MemberModel extends Model
{
    protected $trueTableName = 'vip_member';
	public function isVip($id){
		$grade_name=$this->where(array('id'=>$id))->getField('grade_name');
		if($grade_name=="合伙人"){
			return 1;
		}elseif($grade_name=="会员"){
			return 2;
		}else{
			return 0;
		}
	}

}