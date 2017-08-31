<?php
namespace Home\Logical\Model;

use Common\Model\PayModel;
use Common\TraitClass\PayTrait;

class WxPayAssignKeyLogic
{
    use PayTrait;
    /**
     * 模型
     * @var PayModel
     */
    private $objModel;
    
    private $data ;
    
    /**
     * 支付数据
     * @param unknown $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        
        $this->objModel = PayModel::getInitnation();
    }
    
    public function assignKey()
    {
        $paySource = $this->data;
        
        if (empty($paySource['attach'])) {
            return null;
        }
        
        $payId = $paySource['attach'];
        
        $payModel = $this->objModel;
        
        $payData = $payModel->getPayConfigByPrimarykey($payId);
        
        return $this->getPayConfig($payData);
        
    }
    
    
}