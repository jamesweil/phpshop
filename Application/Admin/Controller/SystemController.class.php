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
use Admin\Model\ConfigChildrenModel;
use Common\Tool\Tool;
use Common\Model\BaseModel;

/**
 * 系统设置 控制器【供开发者使用】【非开发人员 请勿动，否则后果自负】
 */
class SystemController extends AuthController
{
    /**
     * 配置列表页 
     */
    public function config()
    {
        
        $configModel = BaseModel::getInstance(ConfigClassModel::class);
        
        Tool::connect('parseString');
        
        $classData   = $configModel->getPageData();
        
        $classData['data'] = Tool::connect('Tree')->makeTree($classData['data']);
    
        $this->model = ConfigClassModel::class; 
        $this->data  = $classData;
        $this->display();
    }
    
    public function index()
    {
        if (!S('classData'))
        {
            $classData = ConfigClassModel::getInitnation()->getChildren(array('p_id' => 0), array('config_class_name', 'id', 'create_time', 'update_time', 'p_id'));
            S('classData', $classData, 10);
        }
        
        $this->ajaxReturnData(S('classData'), 1, '返回成功');
    }
    
    
    /**
     * 添加配置页面 
     */
    public function addConfig()
    {
        $this->fz();
        $this->display();
    }
    
    /**
     * 辅助方法 
     */
    private function fz()
    {
        $this->classData = ConfigClassModel::getInitnation()->getAllClass(array(
            'where' => array('is_open' => 0, 'p_id' => 0),
            'field' => array('id', 'p_id','config_class_name' )
        ));
    }
    
    /**
     * 添加配置分类 
     */
    public function addClass()
    {
        //先检查是否存在分类
//         $count = ConfigClassModel::getInitnation()->count();
        // 要检测的值
        $checkArray = empty($count) ? array('is_numeric' => array('is_open')) : array('is_numeric' => array('p_id', 'is_open'));
        $validata   = empty($count) ? array('is_open', 'config_class_name')   : array('p_id', 'is_open', 'config_class_name','show_type', 'type_name');
        Tool::checkPost($_POST, $checkArray, true, $validata) ? true : $this->ajaxReturnData(null, 0, '灌水机制已打开');
        
        //找出有几级分类
        if (!S('class_id'))
        {
            $idArray = ConfigClassModel::getInitnation()->getChildrenAndMe(array('p_id' =>$_POST['p_id'],'_logic' => 'or', 'id'=>  $_POST['p_id'], 'field' => array('id', 'p_id')));
            
            S('class_id', $idArray, 5);
        }
        if (count(S('class_id'))>=3)
        {
            Tool::connect('Tree');
            //是否超过分类级数
            $data = Tool::makeTree(S('class_id'));
            $isCG = Tool::arrayDepth($data);
            if ($isCG > 5)
            {
                $this->ajaxReturnData(null, 0, '已超过分类限制');
            }
        }
        //是否已存在
        $isHave = ConfigClassModel::getInitnation()->isHaveName(array('config_class_name' => $_POST['config_class_name'], 'field' => 'config_class_name'));
        
        if ($isHave)
        {
            $this->ajaxReturnData(null, 0, '已存在该分类内容');
        }
        
        $isSuccess = ConfigClassModel::getInitnation()->add($_POST, ConfigChildrenModel::getInitnation());
        
        
        $status  = !empty($isSuccess) ? 1 : 0;
        $message = !empty($isSuccess) ? '添加成功' : '添加失败';
        
        $this->ajaxReturnData($isSuccess, $status, $message);
    }
    
    /**
     * 编辑 页面
     */
    public function editConfig()
    {
        Tool::checkPost($_GET, array('is_numeric' => array('id')), true, array('id')) ? true : $this->error('灌水机制已打开');
        //获取该分类
        $data = ConfigClassModel::getInitnation()->getFind(array(
            'where' => array('id' => $_GET['id']),
            'field' => array('id', 'p_id', 'config_class_name','is_open')
        ), ConfigChildrenModel::getInitnation());
        $this->data = $data;
        $this->fz();
        $this->display();
    }
    
    /**
     * 保存编辑
     */
    public function saveClass()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array('is_open', 'id')
        ), true, array('is_open', 'id', 'config_class_name')) ? true : $this->ajaxReturnData(null, 0, '数据有误，请重新输入');
    
        $insert_id = ConfigClassModel::getInitnation()->save($_POST, ConfigChildrenModel::getInitnation());
    
        $status    = empty($insert_id) ? 0 : 1;
        $message   = empty($insert_id) ? '更新失败' : '更新成功';
       
        $this->ajaxReturnData($insert_id, $status, $message);
    }
    
    /**
     * 删除配置 【有bug】以后检查
     */
    public function delConfig()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array('id')
        ), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '数据有误，请重新输入');
        
        $isSuccess = ConfigClassModel::getInitnation()->delete(array(
            'where' => array('id' => $_POST['id']),
            'field' => array('id', 'p_id')
        ), ConfigChildrenModel::getInitnation());
        $status    = empty($isSuccess) ? 0 : 1;
        $message   = empty($isSuccess) ? '删除失败' : '删除成功';
    
        $this->ajaxReturnData($isSuccess, $status, $message);
    }
    
    /**
     * 是否还有下级分类 
     */
    public function isHaveClass()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array('id')
        ), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '数据有误，请重新输入');
        $isHave = ConfigClassModel::getInitnation()->isHaveClass(array(
            'p_id' => $_POST['id']
        ));
        $status    = empty($isHave) ? 0 : 1;
        $message   = empty($isHave) ? '没有下级分类' : '还有下级分类';
        $this->ajaxReturnData(null, $status, $message);
    }
}