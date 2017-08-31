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

namespace Home\Controller;
//加盟商城
class JoinController extends BaseController{   
	public function join(){
		$this->display();
	}
      public function index(){

          //热卖精选
          $hot_selects = $this->hotSelect(6,10);
          //热卖精选下的精选商品
          $hot_pick = M("Goods")->field("id,title,price_market,price_member")
                     ->where(['pid'=>["gt",0]])
                     ->group("p_id")
                     ->limit(10)
                    ->select();





          //生活用品推荐全部
           $lifeuse_all = $this->lifeAllPic(8);

          //生活用品推荐的其他品牌
          $lifeuse_others = $this->lifeUseRec(9,8);

          //生活用品推荐图片
          $cond= [
              'recommend'=>1,
              'brand_logo'=>['neq',''],
          ];
          $lifeuse_pic = M("Brand")->field("id,brand_logo")->where($cond)->find();
          //人气爆款
          $this->assign($this->personBurst());
          $this->assign("lifeuse_others",$lifeuse_others);
          $this->assign("lifeuse_all",$lifeuse_all);
          $this->assign("lifeuse_pic",$lifeuse_pic['brand_logo']);
          $this->assign("hot_selects",$hot_selects);
          $this->display();
      }

      protected function hotSelect($goods_class_num,$goods_num)
       {
           $topId=M('goods_class')
               ->field('id ,class_name')
               ->where(['fid'=>0,'is_show_nav'=>0])
               ->limit($goods_class_num)
               ->getField("id,class_name");
           $goodsClass=D("GoodsClass");
           $hot_choose=[];
           $goods_model = M("Goods");
           $cont['p_id'] = ['gt',0];
           foreach($topId as $k=>$v){
               $result=$goodsClass->field('fid,id')->select();
               //获取每种分类的id
               $change_num=$goodsClass->selectClass($result,$k);
               $change_num = trim($change_num,',');
               $change_num=explode(',',$change_num);
               array_unshift($change_num,(string)$k);
               $change_num = array_filter($change_num);
               $cont['class_id']=['in',$change_num];
               $hot_choose[$v]=$goods_model->field('id,p_id,title,price_market,price_member')->where($cont)->group("p_id")->order('create_time DESC')->limit($goods_num)->select();

           }

           //查询对应id的商品相册
           return $this->choose_picture($hot_choose);

       }



    /**
     * 查询对应的商品id相册
     * @param array $hot_choose 商品的一些信息
     * @return  mixed  商品信息
     */
    protected function choose_picture($hot_choose){
        foreach($hot_choose as &$hot_choose_one){
            foreach($hot_choose_one as &$hot_choose_two){
                $pic_url = M("GoodsImages")->where(['goods_id'=>$hot_choose_two['p_id']])->limit(1)->find();
                $hot_choose_two['pic_url'] = $pic_url['pic_url'];
            }
        }
        unset($hot_choose_one);
        unset($hot_choose_two);
        return $hot_choose;
    }

    protected function lifeUseRec($brand_num,$goods_num){
        $cond['p_id'] = ['gt',0];
        $brand_ids = M("Brand")->where(['recommend'=>1])->limit($brand_num)->getField("id,brand_name");
        $life_rec = [];
        $goods_model = M("Goods");
        foreach($brand_ids as $k=>$brand_id){
               $cond['brand_id'] = $k;
            $life_rec[$brand_id] = $goods_model->field('id,p_id,title,price_market,price_member,brand_id')
                                  ->where($cond)
                                  ->group("p_id")
                                  ->order('create_time DESC')
                                  ->limit($goods_num)
                                  ->select();
        }
        //查询对应id的商品相册
        $goods_images =  $this->choose_picture($life_rec);
        return $goods_images;


    }
    public function lifeAllPic($goods_num){
        $cond['p_id'] = ['gt',0];
        //查询全部的商品
        $all_goods = M("Goods")->field('id,p_id,title,price_market,price_member')->where($cond)->group("p_id")->limit($goods_num)->select();

        $goods_images_model = M("GoodsImages");
         foreach($all_goods as &$goods_one){
             $pic_url = $goods_images_model->where(['goods_id'=>$goods_one['p_id']])->limit(1)->find();
             $goods_one['pic_url'] = $pic_url['pic_url'];
         }
        unset($goods_one);
        return $all_goods;

    }

     //人气爆款
    public function personBurst(){
       return D("NavImg")->getNavimgDiff("生活用品");

    }

}