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

use Common\Model\BaseModel;
use Home\Model\BrandModel;
use Common\Tool\Tool;
use Home\Model\GoodsModel;
use Home\Model\GoodsImagesModel;

/**
 * 品牌店 【Brand】
 */
class BrandController extends BaseController
{
    /**
     * 品牌首页 
     */
    public function index ()
    {
        //导航图片
        $navBanners = D("Ad")->getNavBanner("pc品牌店banner");
        $this->assign("navBanners",$navBanners);
        $model = BaseModel::getInstance(BrandModel::class);
        $hotBrandData = $model -> getDataByStatus();
        $this->hotData = $hotBrandData;
        $this->brandModel = BrandModel::class;
        $this->display();
    }
    /*ajax获取品牌*/
    public function ajaxBrand(){
     if(IS_POST){
            $letter=I('post.letter');
            $brandModel=M('brand');
            $goodsModel=M('goods');
            $goodsImages=M('goods_images');
            $brand=$brandModel->where(array('letter'=>$letter))->field('id,brand_name')->select();
            $brandProduct=$brandModel->where(array('letter'=>$letter))->field('id,brand_name,brand_banner,brand_logo,brand_description')->limit(3)->select();
            foreach($brandProduct as $k=>$vo){
                $id=$vo['id'];
                $goods=$goodsModel->where("`brand_id`=$id AND `p_id`!=0")->field('id,price_market,title,p_id,create_time')->order('id DESC')->limit(4)->select();
                foreach($goods as $key=>$v){
                    $fatherPid=$goodsModel->where(array('id'=>$v['p_id']))->getField('id');
                    $goodsImg=$goodsImages->where(array('goods_id'=>$fatherPid))->getField('pic_url');
                    $goods[$key]['pic_url']=$goodsImg;
                }
                $brandProduct[$k]['goods']=$goods;
            }
            $this->assign('brand',$brand);
            $this->assign('brandProduct',$brandProduct);
            $this->display();
        }
    }
    /**
     * 获取品牌
     */
    public function ajaxGetCommonly ()
    {
        $model = BaseModel::getInstance(BrandModel::class);
        
        $brandData = $model -> getDataByStatus(27);
       
        $this->brandData = $brandData;
        
        $this->brandModel = BrandModel::class;
        
        $this->display();
    }
    
    /**
     * ajax获取 相关品牌 
     */
    public function ajaxGetBrand()
    {
        Tool::checkPost($_POST, array(), false, array('firster')) ?  : $this->ajaxReturnData(null, 0, '获取品牌失败');
        
        
        $model = BaseModel::getInstance(BrandModel::class);
        
        Tool::connect('PinYin');
        
        $recive = array();
        
        $brandData = $model -> getBrandEnglish($_POST['firster'], $recive);

        $this->brandEnglish = $recive;
        
        $this->brandModel = BrandModel::class;
        
        $this->display();
        
    }
    
    /**
     * 最热品牌 
     */
    public function ajaxHotBrandList ()
    {
        $brandModel = BaseModel::getInstance(BrandModel::class);
        
        //找出推荐的品牌
        
        $hotBrandData = $brandModel -> getDataByStatus(1, 3);
        
        Tool::connect('parseString');
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $goodsData  = $goodsModel->getGoodsDataByBrand($hotBrandData, BrandModel::$id_d, count($hotBrandData)*3);
        
        $goodsData = BaseModel::getInstance(GoodsImagesModel::class)->getDataByOtherModel($goodsData, GoodsModel::$id_d, array(
            GoodsImagesModel::$id_d,
            GoodsImagesModel::$goodsId_d,
            GoodsImagesModel::$picUrl_d
        ),  GoodsImagesModel::$goodsId_d);
        
        if (!empty($goodsData) && !empty($hotBrandData)) {
            foreach ($goodsData as $key => $value)
            {
                foreach ($hotBrandData as $name => & $item) {
                    if ($value[GoodsModel::$brandId_d] === $item[BrandModel::$id_d] && !empty($item)) {
                        $item['children'][] = $value;
                    }
                }
            }
        }
        $this->goodsData = $hotBrandData;
        
        $this->brandModel = BrandModel::class;
        
        $this->goodsModel = GoodsModel::class;
        
        $this->goodsImage  = GoodsImagesModel::class;
        
        return $this->display();
        
    }
 
    /**
     * 导航banner图
     */
    public function navBanner(){
        $navBanners = M("Ad")->field("id,ad_link,pic_url")
                    ->where(['ad_space_id'=>7])
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