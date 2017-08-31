<?php

// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>\n
// +----------------------------------------------------------------------



namespace Admin\Model;


use Think\Model;

class NavImgModel extends Model
{

    /**
     * 获取导航规格图片的信息
     * @param $nav_type 导航规格的名字
     * @return array  会显的数据
     */
    public function getNavimgInfo($nav_type){
        $goodsModel = M("Goods");
        //第一块数据
        $result_one =  $this->where(['img_type'=>1,'nav_type'=>$nav_type])->find();
        $result_goods_one = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_one['goods_id']])->find();
        //第二块的数据
        $result_two = $this->where(['img_type'=>2,'nav_type'=>$nav_type])->find();
        $result_goods_two = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_two['goods_id']])->find();
        $result_three = $this->where(['img_type'=>3,'nav_type'=>$nav_type])->find();
        $result_goods_three = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_three['goods_id']])->find();
        //第三块的数据
        $result_four = $this->where(['img_type'=>4,'nav_type'=>$nav_type])->find();
        $result_goods_four = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_four['goods_id']])->find();
        //第四块的数据
        $result_five = $this->where(['img_type'=>5,'nav_type'=>$nav_type])->find();
        $result_goods_five = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_five['goods_id']])->find();
        $result_six = $this->where(['img_type'=>6,'nav_type'=>$nav_type])->find();
        $result_goods_six = $goodsModel->field('id,title,price_market,stock')->where(['id'=>$result_six['goods_id']])->find();
        return compact('result_one','result_goods_one','result_two','result_goods_two','result_goods_three','result_three','result_four','result_goods_four','result_five','result_goods_five','result_six','result_goods_six');
    }
}