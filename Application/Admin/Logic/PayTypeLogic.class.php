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

namespace Admin\Logic;

use Common\Logic\AbstractGetDataModel;
use Admin\Model\PayTypeModel;

/**
 * 支付类型逻辑处理
 * @author 王强
 */
class PayTypeLogic extends AbstractGetDataModel
{
    
    private $splitKey;
    
    private $type = 'type';
    
    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function __construct($data = [], $splitKey = null)
    {
        $this->data = $data;
        
        $this->modelObj = PayTypeModel::getInitnation();
        
        $this->splitKey = $splitKey;
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Common\Logic\AbstractGetDataModel::getResult()
     */
    public function getResult()
    {
        // TODO Auto-generated method stub
        
        return $this->modelObj->select();
        
       
    }
    
    public function getComment ()
    {
        return $this->modelObj->getComment(['is_special']);
    }
    
    public function getModelClassName ()
    {
        return PayTypeModel::class;
    }
    
    /**
     * 设置默认
     */
    public function setDefaultPay ()
    {
        $id = $this->data;
        
        if (($id = (int)$id) === 0) {
            return false;
        }
        $model = $this->modelObj;
        
        $model->startTrans();
        
        $status = $model->save([PayTypeModel::$id_d=>$id, PayTypeModel::$isDefault_d => 1]);
      
        if (!$model->traceStation($status)) {
            return false;
        }
        
        $status = $model->where(PayTypeModel::$id_d.' != %d', $id)->save([PayTypeModel::$isDefault_d => 0]);
       
        if (!$model->traceStation($status)) {
            return false;
        }
        $model->commit();
        return $status;
    }
    
    /**
     * 支付配置
     */
    public function payConfig ()
    {
        $data = $this->data;
        
        if (empty($data)) {
            return [];
        }
        
        $field = [
            PayTypeModel::$id_d,
            PayTypeModel::$typeName_d,
            PayTypeModel::$isSpecial_d,
        ];
        $mergeData = $this->modelObj->getDataByOtherModel($data, $this->splitKey, $field, PayTypeModel::$id_d);
        
        if (empty($mergeData)) {
            return $data;
        }
        
        foreach ($mergeData as $key => & $value ) {
            
            if ($value[$this->type] == 0) {
                $value[PayTypeModel::$typeName_d] = '（PC）'.$value[PayTypeModel::$typeName_d];
            } else {
                $value[PayTypeModel::$typeName_d] = '（移动设备）'.$value[PayTypeModel::$typeName_d];
            }
            
            
            if ($value[PayTypeModel::$isSpecial_d] == 0) {
                continue;
            }
            
            unset($mergeData[$key]);
        }
        
        return $mergeData;
        
    }
    
}

