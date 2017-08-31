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
namespace Common\Pay;

use Common\TraitClass\PayTrait;
use Common\Model\BaseModel;
use Home\Model\OrderModel;
use Home\Model\BalanceModel;

/**
 * 余额支付
 */
class BalancePay 
{
    use PayTrait;
    /**
     * 余额支付
     * @param \stdClass $obj
     * @param 支付信息 $info
     */
    public function pay($obj)
    {
        
        if (!is_object($obj)) {
            $obj->showMessage('参数错误');
        }
        
        
        $info = $obj->getInfo();
        
        
        if (empty($info['price_sum']) || empty($info['order_sn_id'])) {
            $obj->showMessage('参数错误');
        }
        
        $userModel = BaseModel::getInstance(BalanceModel::class);
        
        $monery    = $userModel->getBalanceMoney();
        
//         if (empty($monery) || $monery < $info['price_sum']) {
//             $obj->showMessage('余额不足');
//         }
        $obj->assignValue('orderModel', OrderModel::class);
        
        $obj->assignValue('order', $info);
        
        $obj->showDisplay('balancePay');;
    }
    
   
}