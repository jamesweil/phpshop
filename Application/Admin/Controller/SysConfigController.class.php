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
use Admin\Model\ConfigClassModel;
use Common\Tool\Tool;
use Admin\Model\ConfigChildrenModel;
use Admin\Model\SystemConfigModel;

/**
 * 系统配置 
 */
class SysConfigController extends AuthController
{
    /**
     * 展示配置 
     */
    public function index()
    {
        //获取配置分类
        $configClass = ConfigClassModel::getInitnation()->getAllClass(array('where' =>array('is_open' => 0), 'field' => array('id', 'config_class_name', 'p_id')));
        //获取字表数据
        $children    = ConfigChildrenModel::getInitnation()->getAll();
        //获取配置值
        $configValue = SystemConfigModel::getInitnation()->getValue();
        //组合数据
        Tool::connect('ArrayParse', array('children' => $children, 'pData' => $configClass, 'configValue'=> $configValue));
        $data = Tool::buildData();
        //组合树形结构
        Tool::connect('Tree');
        $data  = Tool::makeTree($data);
        $this->data = $data;
        $this->display();
    }
    
    /**
     * 保存配置 
     */
    public function saveConfig()
    {
        Tool::checkPost($_POST, array('is_numeric' => array('class_id')), true, array('class_id'));
        
        $sysModel = SystemConfigModel::getInitnation();
        
        $sysModel->setInitURL($this->getNoCacheConfig('internet_url'));
        
        $sysModel->setLogoPath($this->getNoCacheConfig('logo_name'));
        
        $isSuccess = $sysModel->saveData($_POST);
        
        $this->updateClient($isSuccess);
    }
}