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
namespace Common\TraitClass;

use Common\Model\BaseModel;
use Common\Model\PayModel;
use Admin\Model\OrderModel;

/**
 * 退货
 */
trait CancelOrder
{

    private $currtModel;

    /**
     *  公共退款
     * @param float   $monery 退款金额
     * @param integer $orderId 订单编号
     * @param int     $payType    支付类型
     * @return mixed
     */
    public function cancelOrder($monery, $orderId, $payType)
    {
        empty($payType) || ($monery = floatval($monery)) !== 0.0 || ($orderId = intval($orderId) !== 0) || ($payType = intval($payType) !== 0) ? true : $this->error('退款出错');
        
        // 获取支付类型
        
        $click = (int)S('click');
        
        if ($click >= 3) {
            $this->error('恶意点击');die();
        }
        
        if (empty($click) || $click < 3) {
        
            $click += 1;
            
            S('click', $click, 20);
        }
        
        $data = $this->getPayConfigByDataBase($payType);
        
        $this->promptParse($data, '参数有误');
        
        $goodsOrders = BaseModel::getInstance(OrderModel::class);
        
        $goodsOrders->setSColums([
            OrderModel::$id_d,
            OrderModel::$orderSn_id_d
        ]);
        
        $info = $goodsOrders->getOrderById($orderId);
        
        $this->promptParse($info, '参数有误');
        
        $info[OrderModel::$orderSn_id_d] = $info[OrderModel::$orderSn_id_d] . '-' . $info[OrderModel::$id_d];
        
        try {
            $data[PayModel::$returnName_d] = str_replace('/', '\\', $data[PayModel::$returnName_d]);
            
            $obj = new \ReflectionClass($data[PayModel::$returnName_d]);
            
            $instance = $obj->newInstance();
            
            $obj->getMethod('setPayData')->invoke($instance, $data); // 设置支付数据
            $obj->getMethod('setReturnMonery')->invoke($instance, $monery);
            $status = $obj->getMethod('refundMonery')->invokeArgs($instance, [
                $this,
                $info
            ]); // 发起支付
        } catch (\Exception $e) {
            showData($e->getMessage());
            // $this->promptParse(false, '参数有误');die();
        }
        
        return $status;
    }

    /**
     * 获取配置数据
     */
    public function getPayConfigByDataBase(array $info)
    {
        if (empty($info)) {
            return array();
        }
        $payModel = BaseModel::getInstance(PayModel::class);
        
        $data = $payModel->getPayInfo($info[OrderModel::$payType_d], $info[OrderModel::$platform_d]);
        
        if (empty($data)) {
            return array();
        }
        $_SESSION['what_pay_id'] = $data[PayModel::$id_d];
        return $data;
    }

    public function showMessage($message, $url = '')
    {
        $this->error($message, $url);
    }
}