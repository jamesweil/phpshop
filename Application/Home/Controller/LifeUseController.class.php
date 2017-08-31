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


/**
 * 生活用品
 * space_id = 10
 */
class LifeUseController extends BaseController
{
      public function index(){

          //导航图片
          $navBanners = D("Ad")->getNavBanner("pc家用电器banner");
          $this->assign("navBanners",$navBanners);

          //热卖精选  分为精选商品和分类商品
          //热卖精选 分类商品 通过商品促销表查出热卖精选的id
          $proGoodsModel = D("PromotionGoods");
          $hot_choose = $proGoodsModel->chooseType("热卖精选",6,10);
          $this->assign('hot_selects',$hot_choose);
          //热卖精选 精选商品
          $careGoods = $proGoodsModel->careSelect();
          $this->assign("careGoods",$careGoods);
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
          $this->display();
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