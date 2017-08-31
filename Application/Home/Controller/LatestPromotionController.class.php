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


use Think\Controller;

class LatestPromotionController extends BaseController
{
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->getNavTitle();
        
    }
    

    public function index(){
        //热卖促销
        $hot_promotion = D("NavImg")->getNavimgDiff("最新促销");
        $this->assign($hot_promotion);

        //导航图片
         $navBanners = D("Ad")->getNavBanner("pc最新促销banner");
         $this->assign("navBanners",$navBanners);


        //热卖精选  分为精选商品和分类商品
        //热卖精选 分类商品 通过商品促销表查出热卖精选的id
        $proGoodsModel = D("PromotionGoods");
        $hot_choose = $proGoodsModel->chooseType("热卖精选",6,10);

        $this->assign('hot_chooses',$hot_choose);
        //热卖商品 精选商品
        $careGoods = $proGoodsModel->careSelect();

        $this->assign("careGoods",$careGoods);

        //最新促销广告
        $lastPro_ad =  $lastProm_banner = M("ad")
                       ->field('id,ad_link,pic_url,title')
                       ->where(['ad_space_id'=>4])
                       ->order('create_time desc')
                       ->limit(1)
                       ->select();
        $this->assign("lastPro_ads",$lastPro_ad);

        //人气特卖 特卖推荐和分类商品
        //人气特卖 分类商品 类型为3表示人气特卖除了特卖推荐的选项
        $popular_goods_sale = $proGoodsModel->chooseType("人气特卖",6,10);
        $this->assign("popular_sales",$popular_goods_sale);

        //人气特卖 特卖推荐 根据订单来查询
        $sale_recos = $proGoodsModel->careSelect();
        $this->assign("sale_recos",$sale_recos);
        $this->display();
    }



}