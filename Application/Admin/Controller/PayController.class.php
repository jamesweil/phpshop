<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <13052079525>
// +----------------------------------------------------------------------
// |简单与丰富！让外表简单一点，内涵就会更丰富一点。
// +----------------------------------------------------------------------
// |让需求简单一点，心灵就会更丰富一点。
// +----------------------------------------------------------------------
// |让言语简单一点，沟通就会更丰富一点。
// +----------------------------------------------------------------------
// |让私心简单一点，友情就会更丰富一点。
// +----------------------------------------------------------------------
// |让情绪简单一点，人生就会更丰富一点。
// +----------------------------------------------------------------------
// |让环境简单一点，空间就会更丰富一点。
// +----------------------------------------------------------------------
// |让爱情简单一点，幸福就会更丰富一点。
// +----------------------------------------------------------------------
namespace Admin\Controller;

use Common\Controller\ProductController;
use Common\Model\BaseModel;
use Common\Model\PayModel;
use Admin\Logic\PayTypeLogic;
use Common\Tool\Tool;
use Admin\Model\PayTypeModel;

class PayController 
{
    private $controllerObj;
    
    private $data;
    
    /**
     * 构造方法
     * @param unknown $args
     */
    public function __construct($args)
    {
        $this->controllerObj = new ProductController();
    
        $this->data = $args;
        
        $this->controllerObj->assign('platform', C('platform_pay'));
    }
    
    /**
     * 列表页
     */
    public function listPayConfig()
    {
        $model = BaseModel::getInstance(PayModel::class);
        
        $data = $model->select();
        
        Tool::connect('parseString');
        
        $payTypeLogic = new PayTypeLogic($data, PayModel::$payType_id_d);
        
        $payConfig = $payTypeLogic->payConfig();
        
        $action = $this->controllerObj;
        
        $action->assign('payConfig', $payConfig);
        
        $action->assign('payTypeModel', $payTypeLogic->getModelClassName());
        
        $action->assign('payModel', PayModel::class);
        
        $action->display();
    }
    
    /**
     * 编辑支付配置
     */
    public function modifyPay ()
    {
        $args = $this->data;
        
        $validate = ['id'];
        
        $action = $this->controllerObj;
        
        Tool::checkPost($args, ['is_numeric' => $validate], true, $validate) ? : $action->ajaxReturnData(null, 0, '修改失败');
        
        $payModel = BaseModel::getInstance(PayModel::class);
        
        $payType = BaseModel::getInstance(PayTypeModel::class)->getIdAndName();
        
        $data = $payModel->find($args['id']);
        
        $notice = require_once CONF_PATH.'notice.php';
        
        $action->assign('payModel', PayModel::class);
        
        $action->assign('prompt', json_encode($notice['modifyPay']));
        
        $action->assign('payType', $payType);
        
        $action->assign('info', $data);
        
        $action->display();
        
    }
    
    /**
     * 保存支付配置
     */
    public function savePayConfig ()
    {
        
        $this->vaildate();
        
        
        $status = BaseModel::getInstance(PayModel::class)->save($this->data);
        
        
        $this->controllerObj->ajaxReturnData($status);
    }
    
    /**
     * 添加配置页
     */
    public function addPayHTML ()
    {
        $payModel = BaseModel::getInstance(PayModel::class);
        
        $payType = BaseModel::getInstance(PayTypeModel::class)->getIdAndName();
        
        $notice = require_once CONF_PATH.'notice.php';
        
        $action = $this->controllerObj;
        
        $action->assign('payModel', PayModel::class);
        
        $action->assign('prompt', json_encode($notice['modifyPay']));
        
        $action->assign('payType', $payType);
        
        $action->display();
    }
    
    public function addPayConfig()
    {
        $this->vaildate();
        
        $status = BaseModel::getInstance(PayModel::class)->add($this->data);
        
        
        $this->controllerObj->ajaxReturnData($status);
    }
    
    private function vaildate ()
    {
        $args = $this->data;
        
        $action = $this->controllerObj;
        
        Tool::checkPost($args) ? : $action->ajaxReturnData(null, 0, '保存有误');
    }
}