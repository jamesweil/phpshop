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
namespace Home\Logical\Model;

use Home\Model\RechargeModel;
use Common\Tool\Tool;

class RechargeLogic
{
    private $model;
    
    private $data;
    /**
     * @return the $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param field_type $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function __construct( $data )
    {
        $this->data = $data;
        
        $this->model = RechargeModel::getInitnation();
    }
    
    /**
     * 获取当前订单充值的金额
     */
    public function getCurretRecharge()
    {
        $orderId = $this->data;
        
        if (empty($orderId) || !is_numeric($orderId)) {
            return false;
        }
        
        return $this->model->field(RechargeModel::$userId_d.','.RechargeModel::$account_d)->where(RechargeModel::$id_d.'=%d', $orderId)->find();
        
    }
    
    public function getRechargeInfo()
    {
        $orderId = $this->data;
        
        if (!is_numeric($orderId)) {
            return [];
        }
        
        $data = $this->model->field(RechargeModel::$payTime_d.', '.RechargeModel::$ctime_d, true)->where(RechargeModel::$id_d.'=%d', $orderId)->find();
        
        return $data;
    }
    
    /**
     * 添加充值记录
     */
    public function add ()
    {
        $data = $this->data;
        
        if (empty($data)) {
            return false;
        }
        
        Tool::connect('Token');
        
        $status = $this->model->add($data);
        
        return $status;
    }
    
    public function getModelClass ()
    {
        return RechargeModel::class;
    }
    
    /**
     * 更新支付状态
     */
    public function update()
    {
        $orderId = $this->data;
        
        $model   = $this->model;
        
        if (!is_numeric($orderId)) {
            return false;
        }
        
        $model->startTrans();
        
        $status = $model->where(RechargeModel::$id_d.'= %d', (int)$orderId)->save([
           RechargeModel::$payStatus_d => 1,
           RechargeModel::$payTime_d => time()
        ]);
       file_put_contents('./Uploads/eee.sql', $model->getLastSql());
        if (!$model->traceStation($status, '支付失败')) {
            $model->rollback();
            return false;
        }
        
        return $status;
    }
}