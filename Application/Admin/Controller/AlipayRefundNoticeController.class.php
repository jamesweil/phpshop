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

use Think\Controller;
use PlugInUnit\PCAlipay\RSA\Lib\AlipayNotify;
use Common\Model\BaseModel;
use Common\Model\OrderGoodsModel;
use Admin\Model\OrderReturnGoodsModel;
use Common\Model\AlipaySerialNumberModel;
use Common\Tool\Tool;
use Common\Model\PayModel;
use Admin\Model\OrderModel;

/**
 * 支付宝 退款 通知
 */
class AlipayRefundNoticeController extends Controller
{

    /**
     * 支付宝退款
     */
    public function parseAlipayNotice()
    {
        // 退款配置
        $aplipayConfig = C('ALIPAY_REFUND_CONFIG');
        
        //获取支付账号等信息
        
        // 批量退款数据中的详细信息
        $resultDetails = $_POST['result_details'];
        
        // 获取订单号
        $serialNumberModel = BaseModel::getInstance(AlipaySerialNumberModel::class);
        
        //获取订单号
        $receiveOrderId = $serialNumberModel->getOrderId(strstr($resultDetails, "^", true));
        
        $payModel = BaseModel::getInstance(PayModel::class);
        
        //获取支付类型
        $payType = BaseModel::getInstance(OrderModel::class)->getUserNameById($receiveOrderId, OrderModel::$payType_d);
        
        //账号信息
        $accountInformation = $payModel->getPayAccount($payType);
        
        
        if (empty($accountInformation)) {
            echo 'fail';die();
        }
        
        $aplipayConfig['partner'] = $accountInformation[PayModel::$payAccount_d];
        
        $aplipayConfig['seller_user_id'] = $accountInformation[PayModel::$sellerId_d];
        
        // 计算得出通知验证结果
        $alipayNotify = new AlipayNotify($aplipayConfig);
        
        $verifyResult = $alipayNotify->verifyNotify();
        
        if (! $verifyResult) {
            echo "fail";
            die(); // 请不要修改或删除
        }
        // 订单商品表 //修改状态
        $orderGoodsModel = BaseModel::getInstance(OrderGoodsModel::class);
        
        // 获取 要退货的 商品编号
        $goodsIdArray = $orderGoodsModel->getOrderReturnGoodId($receiveOrderId);
        
        $model = BaseModel::getInstance(OrderReturnGoodsModel::class);
        
        Tool::connect('parseString');
        
        $model->setSplit(OrderGoodsModel::$goodsId_d);
        
        //修改退货状态
        $status = $model->editReturnStatus($receiveOrderId, $goodsIdArray);
    
        if ($status === false) {
            
            echo 'fail'; die();
        }
        $status = $orderGoodsModel->editManyStatus($receiveOrderId, $goodsIdArray);
        echo "success";
        header('Location:'.U("Invoice/index"));
    }
}