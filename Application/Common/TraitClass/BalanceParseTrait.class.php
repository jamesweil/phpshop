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
namespace Common\TraitClass;


use Home\Logical\Model\RechargeLogic;
use Common\Model\BaseModel;
use Home\Model\BalanceModel;
use Think\Hook;

trait BalanceParseTrait
{
    protected $orderId = 0;
    
    protected $tradeNo = 0;
    
    protected function parseByBalance ()
    {
        $orderId = $this->orderId;
        
        $userRecharge = new RechargeLogic($orderId);
        
        $recharge = $userRecharge->getCurretRecharge();
        
        if (empty($recharge)) {
            return false;
        }
        
        //修改余额充值记录表
        
        $status = $userRecharge->update();
        
        if (empty($status)) {
            return false;
        }
        
        $param = [
            'order_sn_id' => $orderId,
            'trade_no'    => $this->tradeNo,
            'wx_order_id' => $orderId,
            'type'        => 1
        ];
       
        Hook::listen('aplipaySerial', $param);
        
        if (empty($param)) {
            return false;
        }
        
        $className = $userRecharge->getModelClass();
        
        $status = BaseModel::getInstance(BalanceModel::class)->rechargeMoney($recharge, $className);

        if (empty($status)) {
           return false;
        }
        
        return true;
    }
}