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
use Admin\Model\FreightModeModel;
use Common\Model\ExpressModel;
use Common\TraitClass\SearchTrait;
use Common\Tool\Tool;
use Admin\Model\FreightSendModel;
use Admin\Model\FreightsModel;
use Common\Model\RegionModel;

/**
 * 运费 控制器 
 */
class FreightCarryModeController extends AuthController
{
    use SearchTrait;
    
    private static  $validate = array(
        'freight_id',
        'first_thing',	
        'first_weight',
        'frist_volum',
        'frist_money',
        'continued_heavy',	
        'continued_volum',
        'continued_money',	
        'carry_way',
    );
    
    public function index()
    {
        
        $model = BaseModel::getInstance(FreightModeModel::class);
        
        $notes = S('notes');
        
        if (empty($notes)) {
            //获取注释 用于显示列明
            $notes = $model->getComment();
            S('notes', $notes, 60);
        }
        
     
        //获取模板
        $template = BaseModel::getInstance(FreightsModel::class);
        
        $template->setBuildWhereByKey(FreightModeModel::$freightId_d);
        
        $whereData = $_GET;
        
        $where = $template->getWhereByData($whereData, FreightsModel::$expressTitle_d);
        
        $data = $model->getData(15, $where);
        
       
        
        Tool::connect('parseString');
       
        $field = FreightsModel::$id_d.','.FreightsModel::$expressTitle_d;
       
        $data['data'] = $template->getTemplateDataByMode($data['data'], FreightModeModel::$freightId_d, $field);
        
        //获取运送方式
        $sendModel    = BaseModel::getInstance(ExpressModel::class);
       
        $field = ExpressModel::$id_d.','.ExpressModel::$name_d;
        
        $data['data'] = $sendModel->getTemplateDataByMode($data['data'], FreightModeModel::$carryWay_d, $field);
        
        Tool::isSetDefaultValue($_GET, [
            FreightModeModel::$freightId_d => ""
        ]);
        
        $this->data  = $data;
        
        $this->notes = $notes;
        
        $this->model = FreightModeModel::class; 
        
        $this->display();
    }
    /**
     * 运费设置 
     */
    public function carryModeSet()
    {
        $modeModel = BaseModel::getInstance(FreightModeModel::class);
        
        
        $this->getExpressAndTemplate();
        
        
        $this->modeModel = FreightModeModel::class;
        
        $this->display();
    }
    
    public function selectArea ()
    {
        $sendModel = BaseModel::getInstance(FreightSendModel::class);
        
        $this->getArea($sendModel);
    }
    
    private function getExpressAndTemplate () 
    {
        Tool::connect('PinYin');
        $company = S('company');
        
        if (empty($data)) {
            //获取快递公司
            $company = BaseModel::getInstance(ExpressModel::class)->getDefaultOpen();
            
            S('company', $company, 15);
        }
        
        $template = S('template');
        
        if (empty($template)) {
        
            $template = BaseModel::getInstance(FreightsModel::class)->getTemplate();
            
            S('template', $template, 5);
            
        }
        $this->company = $company;
        
        $this->template  = $template;
    }
    
    /**
     * 添加运送方式 
     */
    public function addMode () 
    {
        Tool::checkPost($_POST, array('is_numeric' => self::$validate), true, self::$validate) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(FreightModeModel::class);
        
        $insertId = $model->addByOpenTranstion($_POST);
        
        $sendModel = BaseModel::getInstance(FreightSendModel::class);
        
        $status =    $sendModel->addArea($_POST, $insertId);
        
        $this->promptPjax($status);
        
        $this->updateClient(array(
            'url' => U('index')
        ), '操作');
    }
    
    /**
     * 编辑保存 
     */
    public function saveEdit()
    {
        array_push(self::$validate, 'id');
        
        Tool::checkPost($_POST, array('is_numeric' => self::$validate), true, self::$validate) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(FreightModeModel::class);
        
        $model->setIsOpenTranstion(true);
        
        //保存
        $saveStatus = $model->save ($_POST);
        
        //保存地区
        $saveStatus = BaseModel::getInstance(FreightSendModel::class)->addArea($_POST, $_POST['id']);
        
        $this->promptPjax($saveStatus);
        
        $this->updateClient(array(
            'url' => U('index')
        ), '操作');
    }
    
    /**
     * 编辑 运送方式
     */
    public function edit ($id)
    {
        $this->errorNotice($id);
        
        $modeModel = BaseModel::getInstance(FreightModeModel::class);
        
        $data = $modeModel->find($id);
        
        
        $this->prompt($data);
        
        //获取地区
        //获取包邮地区编号 传递给地区表
        $areaModel = BaseModel::getInstance(FreightSendModel::class);
        
        $areaData  = $areaModel->where( array(FreightModeModel::$freightId_d => $data[FreightModeModel::$id_d]))->select();
       
        $this->getExpressAndTemplate();
        
        $regionModel = BaseModel::getInstance(RegionModel::class);
        
        Tool::connect('parseString');
        
        $regData      = $regionModel->getFreightArea($areaData, FreightSendModel::$mailArea_d);
        
        $this->regData = $regData;
        
        $this->areaModel = FreightSendModel::class;
        
        $this->data = $data;
        
        $this->modeModel = FreightModeModel::class;
        
        $this->display();
        
    }
}