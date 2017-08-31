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

class SuperPopModel extends Model
{
    /**
     * 获取导航的不规格图片
     * @param string $nav_type 导航名字
     * @return array 返回的数据
     */
    public function getSuperPopInfo($nav_type){
        $goodsModel = M("Goods");
        //第一块数据
        $result_one =  $this->where(['img_type'=>1,'nav_type'=>$nav_type])->find();
        $result_goods_one = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_one['goods_id']])->find();
        $result_goods_one['nav_img_url'] = $result_one['img_url'];
        //第二块的数据
        $result_two = $this->where(['img_type'=>2,'nav_type'=>$nav_type])->find();
        $result_goods_two = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_two['goods_id']])->find();
        $result_goods_two['nav_img_url'] = $result_two['img_url'];
        $result_three = $this->where(['img_type'=>3,'nav_type'=>$nav_type])->find();
        $result_goods_three = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_three['goods_id']])->find();
        $result_goods_three['nav_img_url'] = $result_three['img_url'];
        //第三块的数据
        $result_four = $this->where(['img_type'=>4,'nav_type'=>$nav_type])->find();
        $result_goods_four = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_four['goods_id']])->find();
        $result_goods_four['nav_img_url'] = $result_four['img_url'];
        //第四块的数据
        $result_five = $this->where(['img_type'=>5,'nav_type'=>$nav_type])->find();
        $result_goods_five = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_five['goods_id']])->find();
        $result_goods_five['nav_img_url'] = $result_five['img_url'];
        $result_six = $this->where(['img_type'=>6,'nav_type'=>$nav_type])->find();
        $result_goods_six = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_six['goods_id']])->find();
        $result_goods_six['nav_img_url'] = $result_six['img_url'];
        return compact('result_goods_one','result_goods_two','result_goods_three','result_goods_four','result_goods_five','result_goods_six');
    }
}