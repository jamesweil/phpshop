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

namespace Admin\Controller;

use Common\Controller\AuthController;
use Common\Model\BaseModel;
use Admin\Model\SiteModel;
use Common\Tool\Constant\ConstantTool;
use Common\TraitClass\AddressTrait;
use Common\Tool\Tool;
use Common\Model\RegionModel;


/**
 * 分站点控制器
 * @author Administrator     
 */
class SubSiteController extends AuthController
{
    use AddressTrait;
    public function siteList ()
    {
        $model = BaseModel::getInstance(SiteModel::class);
        
        $tableTitle = $model->getComment([SiteModel::$pId_d, SiteModel::$geographical_d]);
        
        $this->tableTitile = $tableTitle;
        
        //此处 认为 站点不会过多 所以 没有必要分页
        
        $data = $model->getData();
       
        $regionModel = BaseModel::getInstance(RegionModel::class);
        
        Tool::connect('parseString');
        
        $data        = $regionModel->getAreaName($data, SiteModel::$areaId_d);
       
        $this->model = SiteModel::class;
        $this->regionModel = RegionModel::class;
        $this->data = $data;
        $this->display();
    }
    
    /**
     * 添加站点 页面
     */
    public function addSiteHtml()
    {
        $model = BaseModel::getInstance(SiteModel::class);
        
        $showType = array();
        
        $showType[SiteModel::$siteName_d] = [
            SiteModel::$siteName_d => ConstantTool::INPUT_TYPE,
            'type'  => ConstantTool::INPUT_TYPE_TEXT,
            'title' => '站点名称',
            'errorNotice' => '请填写站点名称',
        ];
        
        $showType[SiteModel::$url_d] = [
            SiteModel::$url_d      => ConstantTool::INPUT_TYPE,
            'type'  => ConstantTool::INPUT_TYPE_TEXT,
            'title' => 'URL地址',
            'errorNotice' => '请填写url或者URL格式不正确',
        ];
        $this->inputData = $showType;
        $this->model = SiteModel::class;
        $this->display();
    }
    
    /**
     * 添加 
     */
    public function addSiteData()
    {
        $checkNumeric = ['is_numeric' => ['status']];
        $must = ['status', 'site_name', 'url'];
       
        //删除不需要的
        Tool::connect('UnsetData')->unsetDataByKey($_POST, ['id']);
        
        Tool::checkPost($_POST, $checkNumeric, true, $must) ? : $this->ajaxReturnData(null, 0, '操作失败');
        //检测URL 地址的合法性
        filter_var($_POST['url'], FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) ? : $this->ajaxReturnData(null, 0, 'url不合法');
        
        $model = BaseModel::getInstance(SiteModel::class);
        
        //是否存在
        $isExits = $model->IsExits($_POST);
        
        $this->alreadyInDataPjax($isExits);
        
        $status = $model->add($_POST);
        
        $this->promptPjax($status, '添加失败');
        
        $this->updateClient(['url' => U('siteList')], '操作');
    }
}

