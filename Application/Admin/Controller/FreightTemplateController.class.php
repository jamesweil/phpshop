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
use Admin\Model\FreightsModel;
use Admin\Model\SendAddressModel;
use Common\Tool\Tool;
use Common\Model\RegionModel;
use Admin\Model\FreightConditionModel;
use Admin\Model\FreightAreaModel;
use Common\TraitClass\SearchTrait;

/**
 * 运费模板控制器 
 */
class FreightTemplateController extends AuthController
{
    use SearchTrait;
    protected static $volidata = array(
            'freight_id',
            'mail_area_num',
            'mail_area_wieght',
            'mail_area_volume',
            'mail_area_monery'
        );
    
    /**
     * 运费模板列表 
     */
    public function lists()
    {
        $freihtsModel = BaseModel::getInstance(FreightsModel::class);
        
        //设置默认值
        Tool::isSetDefaultValue($_GET, [
            FreightsModel::$expressTitle_d => ''
        ]);
        
        Tool::connect('ArrayChildren');
        
        $where = $freihtsModel->buildSearch($_GET, true);
        $freihtsData  = $freihtsModel->getDataByPage(array(
            'field' => array(
                FreightsModel::$updateTime_d,
                FreightsModel::$createTime_d
            ),
            'where' => $where
        ), 10, true);
        
        Tool::connect('parseString');
        
        $freihtsData['data'] = BaseModel::getInstance(SendAddressModel::class)->getDataByOtherModel($freihtsData['data'], FreightsModel::$stockId_d,array(
            SendAddressModel::$id_d,
            SendAddressModel::$stockName_d,
        ),  SendAddressModel::$id_d);
        
       
        
        $this->sendModel    = SendAddressModel::class;
        $this->freightModel = FreightsModel::class;
        
        $this->data = $freihtsData;
        $this->display();
    }
    
    /**
     * 添加页面 
     */
    public function addTemplateHtml ()
    {
        $freihtsModel = BaseModel::getInstance(FreightsModel::class);
        
        //获取仓库
        $stock  = BaseModel::getInstance(SendAddressModel::class)->getStatusOpenStock(1);
                    
        $this->stock = $stock;
                    
        $this->freightsModel = FreightsModel::class;
        
        $this->display();
    }
    
    /**
     * 编辑 
     * @param string $id 数据编号
     */
    public function modifyHtml ($id)
    {
        $this->errorNotice($id);
        
        
        $freihtsModel = BaseModel::getInstance(FreightsModel::class);
        
        //获取仓库
        $stock  = BaseModel::getInstance(SendAddressModel::class)->getStatusOpenStock(1);
        
        $freigthsData = $freihtsModel->find($id);
        $this->assign('freightsData', $freigthsData);
        
        $this->stock = $stock;
        
        $this->assign('freightsModel', FreightsModel::class);
        
        $this->display();
        
    }
    
    /**
     * save 保存数据 
     */
    public function saveFreight ()
    {
        $this->paeseData(['id'], 'save');
    }
    
    /**
     * 添加数据 
     * Array
        (
            [express_title] => 四川
            [stock_id] => 1
            [send_time] => 2
            [is_free_shipping] => 1
            [valuation_method] => 1
            [is_select_condition] => 0
        )
     */
    public function addFreights ()
    {
        $this->paeseData();
    }
    
    /**
     * 添加保存处理
     * @param array $addCheck
     * @param string $function
     */
    private function paeseData (array $addCheck = array(), $function = 'add')
    {
        $check = [
            'send_time', 'is_free_shipping',
            'valuation_method', 'is_select_condition', 'stock_id'
        ];
        
        $check = array_merge($addCheck, $check);
        
        $validate = $check;
        
        $validate[] = 'express_title';
        Tool::checkPost($_POST, ['is_numeric' => $check], true, $validate)? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $freightsModel = BaseModel::getInstance(FreightsModel::class);
       
        if ($function === 'add') {
            //是否存在
            $isExits = $freightsModel->IsExits($_POST['express_title']);
            
            $this->alreadyInDataPjax($isExits);
        }
        
        $status = $freightsModel->$function($_POST);
        
        $this->promptPjax($status, '添加失败');
        
        $this->updateClient(array('url' => U('lists')), '操作');
    }
    
    /**
     * 指定条件 包邮
     */
    public function specifyCondition($id)
    {
        $this->errorNotice($id);
        
        $conditionModel = BaseModel::getInstance(FreightConditionModel::class);
        
        $this->edit($conditionModel, $id);
        
        $this->conditionModel = FreightConditionModel::class;
        
        $this->display();
    }
    
    private function edit (BaseModel $model, $id) 
    {
        
        $this->errorNotice($id);
        
        $array = array();
        //设置默认值
        Tool::isSetDefaultValue($array, array(
            $model::$id_d,
            $model::$mailArea_monery_d,
            $model::$mailArea_num_d,
            $model::$mailArea_volume_d,
            $model::$mailArea_wieght_d
        ), 0);
        
        $array = $model->getAttribute(array(
            'field' => array(
                $model::$updateTime_d,
                $model::$createTime_d,
            ),
            'where' => array(
                $model::$freightId_d => $id
            )
        ), true, 'find');
     
        //包邮地区
      
        $areaModel = BaseModel::getInstance(FreightAreaModel::class);
        
        //获取包邮地区编号 传递给地区表
        $areaData  = $areaModel->where( array(FreightAreaModel::$freightId_d => $array[$model::$id_d]))->select();
      
        
        $regionModel = BaseModel::getInstance(RegionModel::class);
        
        Tool::connect('parseString');
        $regData      = $regionModel->getFreightArea($areaData, FreightAreaModel::$mailArea_d);
        
        $this->areaModel = FreightAreaModel::class;
        
        $this->regData = $regData;
        
        $this->add = empty($array) ? true : false;
        //包邮数据
        $this->data = $array;
        
    }
    
    /**
     * 保存编辑 
     */
    public function saveEdit()
    {
        array_push(self::$volidata, 'id');
        
        Tool::checkPost($_POST, array('is_numeric' => self::$volidata), true, self::$volidata) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(FreightConditionModel::class);
        //保存
        $saveStatus = $model->saveCondition ($_POST);
       
        $this->promptPjax($saveStatus);
        
        //保存地区
        $saveStatus = BaseModel::getInstance(FreightAreaModel::class)->addArea($_POST, $_POST['id']);
        
        $this->promptPjax($saveStatus);
        
        $this->updateClient(array(
            'url' => U('lists')
        ), '操作');
        
    }
    
    /**
     * 获取地区 
     */
    public function selectArea ()
    {
        $sendModel = BaseModel::getInstance(FreightAreaModel::class);
    
        $this->getArea($sendModel);
    }
    
    /**
     * 添加包邮 
     */
    public function addArea()
    {
        Tool::checkPost($_POST, array('is_numeric' => self::$volidata), true,   self::$volidata) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(FreightConditionModel::class);
        
        $insertId = $model->addCondition($_POST);
        
        $freightArea = BaseModel::getInstance(FreightAreaModel::class);
        
        $status      = $freightArea->addArea($_POST, $insertId);
        
        $this->promptPjax($status, '添加失败');
        
        $this->updateClient(array(
            'url' => U('lists')
        ), '操作');
        
    }
}