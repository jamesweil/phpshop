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
use Think\Page;

/**
 * 个人中心 
 */
class PersonalModel extends Model{
	//我的订单
	public function order_myorder_list($user_id,$limit){
		$data = M('order as a')->field('a.id,a.price_sum,a.create_time,a.order_status,b.user_name')
		->join('join db_user as b on b.id=a.user_id')
		->where('a.user_id='.$user_id)
		->limit($limit)
		->select();
		foreach ($data as $key => $value) {
			$data[$key]['of'] = date('Y-m-d',$value['create_time']);
			$data[$key]['oh'] = date('H:i:s',$value['create_time']);
			$id = $value['id'];//订单ID
			$goods = M('order_goods')->field('goods_id')->where('order_id='.$id)->select();
			foreach ($goods as $k => $v) {
				$id = $v['goods_id'];//商品id
				$img = M('goods_images')->field('pic_url')->where('goods_id='.$id)->limit(2)->select();
				foreach ($img as $kk=> $vv) {
					$data[$key]['images'.$kk]=$vv['pic_url'];
				}
			}
		}
		return $data;
	}

	//订单详情
	public function order_details($id){
    	$data = M('order as a')->field('')
		->join()
		->where()
		->find();
		return $data;
	}
}