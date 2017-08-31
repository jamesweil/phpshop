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
 * 热卖商品
 */
class BestSellersController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->getNavTitle();
    }

    public function index(){

        //热卖单品的分类
        $hot_single_goods =  $this->hotSingle(1,6,5);


        //卖品
        $goodsAlls = $this->oneCate(3,10);


        //热卖商品
        $this->assign(D("SuperPop")->getSuperPopInfo("热卖商品"));
        $this->assign("goodsAlls",$goodsAlls);
        $this->assign("hot_single_goodlist",$hot_single_goods);
        $this->display();
    }

    /**
     * 热卖单品
     * @param $type 类型
     * @param $goods_class_num 商品分类的数目
     * @param $goods_num 商品的数目
     * @return mixed array 返回的数据
     */
    protected function hotSingle($type,$goods_class_num,$goods_num){
        $topId=M('GoodsClass')->field('id ,class_name')->where(['hot_single'=>$type])->limit($goods_class_num)->select();
        $goodsClass=D("GoodsClass");
        $goodsModel = M("Goods");
        $cont['hot_single']=$type;
        $cont['p_id'] = ['gt',0];
        $hot_choose=[];
        foreach($topId as $k=>$v){
            $result=$goodsClass->field('fid,id')->select();
            //获取每种分类的id
            $change_num=$goodsClass->selectClass($result,$v['id']);
            $change_num=explode(',',$change_num);
            array_unshift($change_num,$v['id']);
            $change_num = array_filter($change_num);
            $cont['class_id']=['in',$change_num];
            $hot_choose[$v['class_name']]=$goodsModel->field('id,p_id,title,price_market,price_member')->where($cont)->order('create_time DESC')->group('p_id')->limit($goods_num)->select();
        }
        //查询对应id的商品相册
        return $this->choose_picture($hot_choose);

    }

    /**
     * 查询对应的商品id相册
     * @param array $hot_choose 商品的一些信息
     * @return  mixed  商品信息
     */
    public function choose_picture($hot_choose){
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


    //卖品的
    /** 获取对应的商品分类和对应的商品图片
     * @param int $goods_class_num  限制商品分类的个数
     * @param int $goods_num 限制商品的个数
     * @return array 返回一个数组集
     */
    private function oneCate($goods_class_num,$goods_num){
        $goodsClassModel = D("GoodsClass");
        $oneCate = $goodsClassModel->field('id,fid,pIid,class_name,pic_rul')->where(['fid'=>0])->limit($goods_class_num)->order("sort_num desc")->getField('class_name,id,pic_url');

        foreach($oneCate as &$v){
            $v['twoCate'] = $this->twoCate($v['id'],$goods_num);
        }
        unset($v);
        return $oneCate;

    }

    /**
     * 寻找子类的id
     * @param integer $category_id 父级分类
     * @return string $category_ids 该父级分类的子类
     */
    private  function getCategory($category_id ){
        $category_ids = $category_id.",";
        $child_category = M("GoodsClass") -> field("id,class_name")->where(['fid'=>$category_id])->select();
        foreach( $child_category as $key => $val ){
            $category_ids .= $this->getCategory( $val["id"] );
        }
        return $category_ids;
    }

    /**
     * 二级商品分类的信息 和 分类图片
     * @param int $category_id  商品的fid
     * @param int $goods_num 限制商品的个数
     * @return array
     */
    public function twoCate($category_id,$goods_num){
        $child_category = M("GoodsClass")->field("id,class_name")->where(['fid' => $category_id])->getField("id,class_name");
        $goodsClass = D("GoodsClass");
        $hot_choose = [];
        foreach ($child_category as $k => $v) {
            $result = $goodsClass->field('fid,id')->select();
            //获取每种分类的id
            $change_num = $goodsClass->selectClass($result,$k);
            $change_num = explode(',', $change_num);
            array_unshift($change_num,(string)$k );
            $change_num = array_filter($change_num);
            $cont['class_id'] = ['in', $change_num];
            $hot_choose[$v] = M('goods')->field('id,p_id,title,price_market,price_member')->where($cont)->order('create_time DESC')->group("p_id")->limit($goods_num)->select();
        }

        //查询对应id的商品相册
        return $this->choose_picture($hot_choose);
    }


    /**
     * 导航banner图
     */
    public function navBanner(){
        $navBanners = M("Ad")->field("id,ad_link,pic_url")
                    ->where(['ad_space_id'=>5])
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

