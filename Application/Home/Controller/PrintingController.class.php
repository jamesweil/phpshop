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


class PrintingController extends BaseController
{
    
    public function __construct()
    {
        parent::__construct();
        $this->getNavTitle();
    }
    
    /**
     * 显示打印耗材的相关数据
     *     打印耗材推荐跟品牌和分类有关系。
     *         1.确定顶级分类为打印耗材推荐，子类也有继承父类的功能（即为打印耗材推荐）
     */
    public function index()
    {
        //导航图片
        $navBanners = D("Ad")->getNavBanner("pc家具厨具banner");
        $this->assign("navBanners",$navBanners);
        //打印耗材推荐全部
        $print_all = $this->printAllPic(8);


        //打印耗材推荐的其他品牌
        $print_others = $this->printRec(9,8);


        //热卖精选  分为精选商品和分类商品
        //热卖精选 分类商品 通过商品促销表查出热卖精选的id
        $proGoodsModel = D("PromotionGoods");
        $hot_choose = $proGoodsModel->chooseType("热卖精选",6,10);
        $this->assign('hot_selects',$hot_choose);
        //热卖精选 精选商品
        $careGoods = $proGoodsModel->careSelect();

        $this->assign("careGoods",$careGoods);



        //打印耗材推荐图片
        $cond= [
            'is_printing'=>1,
            'pic_url'=>['neq',''],
        ];

        //打印耗材下面的推荐
        $this->hot_search($a);

        $this->assign('hot_goods',$a);

        $print_pic = M("GoodsClass")->field("id,class_name,pic_url")->where($cond)->find();
        //打印耗材下面的推荐
        $this->hot_search($a);

        $this->assign('hot_goods',$a);
        //打印热卖
        $print_hot = D("NavImg")->getNavimgDiff("打印耗材");
        $this->assign($print_hot);
        $this->assign("print_others",$print_others);
        $this->assign("print_all",$print_all);
        $this->assign("print_pic",$print_pic['pic_url']);

        $this->display();
    }

    /**
     * 热卖商品
     * @param $goods_class_num 商品分类的数量
     * @param $goods_num  商品的数量
     * @param $goods_ids 要查的商品id
     * @return mixed array 返回的数据
     */
    protected function chooseType($goods_class_num,$goods_num,$goods_ids)
    {
       /* $goods_ids = $goods_ids;*/
        $cont['id'] = ['in',$goods_ids];
        $topId=M('goods_class')
            ->field('id ,class_name')
            ->where(['fid'=>0,'is_show_nav'=>0])
            ->limit($goods_class_num)
            ->getField("id,class_name");
        $goodsClass=D("GoodsClass");
        $goodsModel = M("Goods");
        $hot_choose=[];
        foreach($topId as $k=>$v){
            $result=$goodsClass->field('fid,id')->select();
            //获取每种分类的id
            $change_num=$goodsClass->selectClass($result,$k);
            $change_num = trim($change_num,',');
            $change_num=explode(',',$change_num);
            array_unshift($change_num,(string)$k);
            $change_num = array_filter($change_num);
            $cont['class_id']=['in',$change_num];
            $cont['p_id'] = ['gt',0];
            $hot_choose[$v]=$goodsModel->field('id,title,p_id,price_market,price_member')->where($cont)->order('create_time DESC')->group("p_id")->limit($goods_num)->select();

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
        $goodsImagesModel = M("GoodsImages");
        foreach($hot_choose as &$hot_choose_one){
            foreach($hot_choose_one as &$hot_choose_two){
                $pic_url = $goodsImagesModel->where(['goods_id'=>$hot_choose_two['p_id']])->limit(1)->find();
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
    protected function printRec($brand_num,$goods_num){
        $brand_ids = M("Brand")->where(['recommend'=>1])->limit($brand_num)->getField("id,brand_name");
        $ids = $this->printGoodsClass();
        $cond["class_id"] = ['in',$ids];
        $cond['p_id'] = ['gt',0];
        $print_rec = [];
        foreach($brand_ids as $k=>$brand_id){
            $cond['brand_id'] = $k;
            $print_rec[$brand_id] = M('goods')->field('id,p_id,title,price_market,price_member,brand_id')
                ->where($cond)
                ->order('create_time DESC')
                ->group("p_id")
                ->limit($goods_num)
                ->select();
        }
        //查询对应id的商品相册
        $goods_images =  $this->choose_picture($print_rec);

        return $goods_images;


    }

    /**
     * 查询全部打印耗材推荐的全部品牌的商品图片
     * @param integer $goods_num  表示显示商品图片的数目
     * @return array  表示返回全部品牌的商品图片
     */
    public function printAllPic($goods_num){
        $ids = $this->printGoodsClass();
        $cond["class_id"] = ['in',$ids];
        $cond['p_id'] = ['gt',0];
        //查询全部的商品
        $all_goods = M("Goods")->field('id,title,p_id,price_market,price_member')->where($cond)->group("p_id")->limit($goods_num)->select();

        foreach($all_goods as &$goods_one){
            $pic_url = M("GoodsImages")->where(['goods_id'=>$goods_one['p_id']])->limit(1)->find();
            $goods_one['pic_url'] = $pic_url['pic_url'];
        }
        unset($goods_one);
        return $all_goods;
    }

    /**
     * 取出打印耗材推荐的商品分类
     *
     *@return array 返回为打印耗材推荐的商品分类id
     */
   public function printGoodsClass(){
      //取父级id为0的商品分类，取出对应的子类
       $parent_ids = M("GoodsClass")->where(['is_printing'=>1,'fid'=>0])->getField("id,class_name");
       $str_ids = "";
       foreach($parent_ids as $parent_id=>$parent_value ){
           $str_ids .=$this->getCategory($parent_id);
       }
       $ids = M("GoodsClass")->where(['is_printing'=>1])->getField("id,class_name");
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

    //热门搜索对应下面的商品信息
    private function hot_search(&$a){
        //查询是否是打印耗材下面的商品分类
        $printing_class=M('goods_class')->field('id')->where(['is_printing'=>1])->select();
        if($printing_class){
            $printing_class=array_column($printing_class, 'id');
            $goodsCondition['class_id']= array('in',$printing_class);
            //打印耗材分类下面对应的品牌
            $where['goods_class_id']=['in',$printing_class];
            $brand_id=M('brand')->field('id')->where($where)->select();
            if($brand_id){
                $brand_id=array_column($brand_id,'id');
                $goodsCondition['brand_id']=array('in',$brand_id);
            }
        }
        $goodsCondition['recommend']=1;
        $goodsCondition['p_id'] =array('gt',0);
        $a=M('goods')->field('title,id')->where($goodsCondition)->group('p_id')->limit(6)->select();
    }


    /**
     * 导航banner图
     */
    public function navBanner(){
        $navBanners = M("Ad")->field("id,ad_link,pic_url")
                    ->where(['ad_space_id'=>8])
                    ->order('id,create_time desc')
                    ->limit(4)
                    ->select();
       if(!empty($navBanners)){
           $this->ajaxReturn($navBanners);
       }else{
           $this->ajaxReturn(['msg'=>"error"]);
       }
    }

}