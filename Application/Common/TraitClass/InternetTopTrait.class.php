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

namespace Common\TraitClass;

use Home\Model\HotWordsModel;
use Common\Model\BaseModel;
use Home\Model\SiteModel;
use Common\Model\RegionModel;
use Org\Net\IpLocation;
use Common\Tool\Tool;
use Think\Hook;

/**
 * 头部trait 
 */
trait InternetTopTrait 
{
    private static function userDataExits ()
    {
        // 获取购物车&用户信息
        return $name = M('user')->field('user_name')->where(['id' => $_SESSION['user_id']])->find();
    }
    
    /***/
    public function showMeYourShe()
    {
        Hook::listen(ASDKLJHKJHJKHKUH);
    }
    /**
     * 关键词搜索
     */
    protected static function keyWord()
    {
        // 获取关键词 及其分类
        $data = HotWordsModel::getInitnation()->getKeyWord();
        return $data;
    }
    
    protected function isLogin($isPjax = false)
    {
        if(empty($_SESSION['user_id']) && !$isPjax) {
            $this->redirect('Public/login');
        } else if (empty($_SESSION['user_id']) && $isPjax) {
            $this->ajaxReturnData(null, 0, '请登录');
        }
    }
    
    /**
     * 分站点 
     */
    public function getSite()
    {
        $model = BaseModel::getInstance(SiteModel::class);
        
        $data = $model->getData();
        
        $regionModel = BaseModel::getInstance(RegionModel::class);
       
        Tool::connect('parseString');
        
        $data = $regionModel->getAreaName($data, SiteModel::$areaId_d);
        
        $data  = $model->geographical($data);
        
        $def = $model->getDefault();
      
        $def  = $regionModel->getDataDefault($def, SiteModel::$areaId_d);
    
        $this->defaultData = $def;
        
        $this->regModel = RegionModel::class;
        
        $this->siteData = $data;
        
        $this->siteModel = SiteModel::class;
    }
    
    /**
     * 根据当前ip地址 获取所在区域 
     */
    public function getLocationArea($name='country')
    {
        $ipLocationObj = new IpLocation();
        $area = $ipLocationObj->getlocation();
        return empty($area[$name]) ? $area : $area[$name];
    }
    
    public function getList()
    {
        $this->getSite();//分站点
        $this->areaConfig = C('AreaList');
        $this->display('public/areaList');
    }
    
    public function getFamily ()
    {
        $str = S('str');
        
        if (empty($str)) {
            Hook::listen('reade', $str);
        } else {
            return $str;
        }
        
        if (empty($str)) {
            return null;
        }
        S('str', $str, 30);
        
        return $str;
    }
    
    /**
     * 文章分类页
     */
    public function arctile ()
    {
        $article_category_model = D('Article');
        if (! $article_lists = S('article_lists')) {
            // 准备商品分类数据
            $article_lists = $article_category_model->getList();
            S('article_lists', $article_lists, 30);
        }
        return $article_lists;
    }
    
}