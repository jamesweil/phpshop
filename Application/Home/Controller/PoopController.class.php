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

/**
 * @author 王强
 * @version 1.0
 */
namespace Home\Controller;

use Common\Model\BaseModel;
use Home\Model\PoopClearanceModel;
use Common\Tool\Tool;
use Home\Model\GoodsModel;
use Home\Model\GoodsImagesModel;
use Common\Model\PromotionTypeModel;
use Home\Model\CouponModel;

/**
 * @desc 尾货清仓
 * @author 王强
 */
class PoopController extends BaseController
{
    private $poopStatus = true;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->getNavTitle();
    }
    
    public function index ()
    {  
        $model = BaseModel::getInstance(PoopClearanceModel::class);
       
        $this->getNavData(array(
            PoopClearanceModel::$status_d => 1
        ), $model);
    }
    
    /**
     * 获取促销产品 
     */
    public function ajaxGetPoopGoods()
    {
        $model = BaseModel::getInstance(PoopClearanceModel::class);
         
        $this->poopStatus = false;
        
        $this->getNavData(array(
            PoopClearanceModel::$status_d => 0
        ), $model);
    }
    
    
    private function getNavData (array $where, BaseModel $model) 
    {
       
        $data  = $model->getPoopData($where);
       
        // poopStatus,0 => 打折促销； 1减价优惠，2 固定金额出售, -1 买就送代金券
         
        $this->prompt($data, U('Index/index'));
        Tool::connect('parseString');
        
        $typeModel = BaseModel::getInstance(PromotionTypeModel::class);
         
        $data       = $typeModel->getTypeData($data, PoopClearanceModel::$typeId_d);
        
        //
        $conponModel = BaseModel::getInstance(CouponModel::class);
        
        $data        = $conponModel->getCouponByPoop($model->getConponListIds(), $data, $model);
        
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        
        $goodsData = $goodsModel->getGoodsByPoop($data, PoopClearanceModel::$goodsId_d);
       
        $goodsImagesModel = BaseModel::getInstance(GoodsImagesModel::class);
        
        $goodsData = $goodsImagesModel->getImageById($goodsData,GoodsModel::$pId_d);
        
        $hour = $minute = $second = $format = 0;
       
        if ($this->poopStatus) {
            $startTime = strtotime($this->getConfig('start_time'));
            $endTime   = strtotime($this->getConfig('end_time'));
        
            $acitiveTime = $endTime - $startTime;
            
            $curret = time();
            
            if($endTime > $curret  && $curret > $startTime) {
               
               $format = $endTime-time();
            } else {
                $format = 0;
            }
            $this->format = $format;
        }
        $this->goodsData = $goodsData;
         
        $this->goodsImages = GoodsImagesModel::class;
        $this->type         = PromotionTypeModel::class;
        $this->goodsModel  = GoodsModel::class;
         
        $this->poopModel = PoopClearanceModel::class;
        //导航图片
        $navBanners = D("Ad")->getNavBanner("pc尾货清仓banner");
        $this->assign("navBanners",$navBanners);
         
        return $this->display();
    }
    
    /**
     * 导航banner图
     */
    public function navBanner(){
        $navBanners = M("Ad")->field("id,ad_link,pic_url")
                    ->where(['ad_space_id'=>6])
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
