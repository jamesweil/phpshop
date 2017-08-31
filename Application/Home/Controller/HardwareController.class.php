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

/**
 * 办公硬件
 * space_id = 9
 */
class HardwareController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->getNavTitle();
        
    }
    
    /**
     *  显示办公硬件的相关数据
     *  办公硬件推荐跟品牌和分类有关系。
     *1.确定顶级分类为办公硬件推荐，子类也有继承父类的功能（即为办公硬件推荐）
     */
    public function index()
    {

        //导航图片
        $navBanners = D("Ad")->getNavBanner("pc电脑办公banner");
        $this->assign("navBanners",$navBanners);
        //办公硬件推荐  全部
        $hardware_all = $this->hardwareAllPic(8);
        //办公硬件推荐的其他品牌
        $hardware_others = $this->hardwareRec(9,8);

        //热卖精选  分为精选商品和分类商品
        //热卖精选 分类商品 通过商品促销表查出热卖精选的id
        $proGoodsModel = D("PromotionGoods");
        $hot_choose = $proGoodsModel->chooseType("热卖精选",6,10);
        $this->assign('hot_selects',$hot_choose);
        //热卖精选 精选商品
        $careGoods = $proGoodsModel->careSelect();

        $this->assign("careGoods",$careGoods);

        //办公硬件推荐图片
        $cond= [
            'is_hardware'=>1,
            'pic_url'=>['neq',''],
        ];

        $hardware_pic = M("GoodsClass")->field("id,class_name,pic_url")->where($cond)->find();
        //硬件热卖
        $hardware_sales = D("NavImg")->getNavimgDiff("办公硬件");
        $this->assign($hardware_sales);
        $this->assign("hardware_others",$hardware_others);
        $this->assign("hardware_all",$hardware_all);
        $this->assign("hardware_pic",$hardware_pic['pic_url']);
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

    /**
     * 打印耗材推荐的各个品牌对应的商品
     * @param integer $brand_num 品牌的数目
     * @param integer $goods_num 商品的数目
     * @return array  返回其他品牌的商品
     */
    protected function hardwareRec($brand_num,$goods_num){
        $brand_ids = M("Brand")->where(['recommend'=>1])->limit($brand_num)->getField("id,brand_name");
        $ids = $this->hardwareGoodsClass();
        $cond["class_id"] = ['in',$ids];
        $cond['p_id'] = ['gt',0];
        $print_rec = [];
        foreach($brand_ids as $k=>$brand_id){
            $cond['brand_id'] = $k;
            $hardward_rec[$brand_id] = M('goods')->field('id,p_id,title,price_market,price_member,brand_id')
                ->where($cond)
                ->order('create_time DESC')
                ->group("p_id")
                ->limit($goods_num)
                ->select();
        }
        //查询对应id的商品相册
        $goods_images =  $this->choose_picture($hardward_rec);

        return $goods_images;


    }

    /**
     * 查询办公硬件推荐的全部品牌的商品图片
     * @param integer $goods_num  表示显示商品图片的数目
     * @return array  表示返回全部品牌的商品图片
     */
    public function hardwareAllPic($goods_num){
        $ids = $this->hardwareGoodsClass();
        $cond["class_id"] = ['in',$ids];
        $cond['p_id'] = ['gt',0];
        //查询全部的商品
        $all_goods = M("Goods")->field('id,p_id,title,price_market,price_member')->where($cond)->group("p_id")->limit($goods_num)->select();
        foreach($all_goods as &$goods_one){
            $pic_url = M("GoodsImages")->where(['goods_id'=>$goods_one['p_id']])->limit(1)->find();
            $goods_one['pic_url'] = $pic_url['pic_url'];
        }
        unset($goods_one);
        return $all_goods;
    }

    /**
     * 取出办公硬件推荐的商品分类
     *
     *@return array 返回为办公硬件推荐的商品分类id
     */
    public function hardwareGoodsClass(){
        //取父级id为0的商品分类，取出对应的子类
        $parent_ids = M("GoodsClass")->where(['is_hardware'=>1,'fid'=>0])->getField("id,class_name");
        $str_ids = "";
        foreach($parent_ids as $parent_id=>$parent_value ){
            $str_ids .=$this->getCategory($parent_id);
        }
        $ids = M("GoodsClass")->where(['is_hardware'=>1])->getField("id,class_name");
        foreach($ids as $k_id=>$class_name){
            $arr_id[] = (string)$k_id;
        }
        $str_ids = trim($str_ids,',');
        $str_ids = explode(",",$str_ids);
        $ids_arr = array_unique(array_merge($str_ids,$arr_id));
        return $ids_arr;
    }


    /**
     * 获取父亲的子类id
     * @param integer $category_id 父亲id
     * @return string 返回父类和子类的id
     */
    private  function getCategory($category_id ){
        $category_ids = $category_id.",";
        $child_category = M("GoodsClass") -> field("id,class_name")->where(['fid'=>$category_id])->select();
        foreach( $child_category as $key => $val ){
            $category_ids .= $this->getCategory( $val["id"] );
        }
        return $category_ids;
    }


}