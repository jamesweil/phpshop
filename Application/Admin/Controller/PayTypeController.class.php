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
use Admin\Logic\PayTypeLogic;
use Common\Tool\Tool;
use Common\Model\BaseModel;
use Admin\Model\PayTypeModel;

/**
 * 支付类型
 * @author 王强
 */
class PayTypeController
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
    }
    /**
     * 支付类型列表
     */
    public function listPay ()
    {
        //
        $payTypeLogic = new PayTypeLogic();
        
        $comment = $payTypeLogic->getComment();
        
        //因为数据不会太多 所以省略分页
        
        $data = $payTypeLogic->getResult();
        
        $imageType = C('image_type');
        $action =  $this->controllerObj;
        
        $action->assign('comment', $comment);
        
        $action->assign('model',  $payTypeLogic->getModelClassName());
        
        $action->assign('data', $data);
        
        $action->assign('jsonImageType', json_encode($imageType));
        
        $action->assign('imageType', $imageType);
        
        $action->display();
    }
    
    /**
     * 是否开启
     */
    public function editIsOpen ()
    {
        $args = $this->data;
        $validate = ['id', 'status'];
        Tool::checkPost($args, ['is_numeric' => $validate], true, $validate) ? : $this->controllerObj->ajaxReturnData(null, 0, '修改失败');
      
        $status = BaseModel::getInstance(PayTypeModel::class)->save($args);
        
        $this->controllerObj->updateClient($status, '修改');
    }
    
    /**
     * 设置默认
     */
    public function editDefault ()
    {
        $args = $this->data;
        $validate = ['id'];
     
        Tool::checkPost($args, ['is_numeric' => $validate], true, $validate) ? : $this->controllerObj->ajaxReturnData(null, 0, '修改失败');
       
        $payTypeLogic = new PayTypeLogic($args['id']);
        
        $status = $payTypeLogic->setDefaultPay();
        $this->controllerObj->updateClient($status, '修改');
        
    }
}