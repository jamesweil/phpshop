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
use Common\TraitClass\NoticeTrait;
use Common\TraitClass\PayTrait;
use PlugInUnit\Wxpay\Pay\RefundPub;
use Common\Model\BaseModel;
use Common\Model\OrderWxpayModel;
use Admin\Model\OrderModel;
use PlugInUnit\Wxpay\WxPayConfPub;
use Common\Tool\Event;
use Common\Model\OrderGoodsModel;
use Admin\Model\OrderReturnGoodsModel;

class WxRefund extends RefundPub
{
    use NoticeTrait;
    use PayTrait;
    
    /**
     * 微信退款 
     */
    public function refundMonery($obj, array $info)
    {
        if (empty($info) || !is_array($info) || !is_object($obj)) {
            return array();
        }
        //到订单微信表
        $model = BaseModel::getInstance(OrderWxpayModel::class);
       
        $wxData = $model->getOrderWx($info[OrderModel::$id_d]);
      
        if (empty($wxData)) {
            $obj->showMessage('未找到凭据');
        }
        $this->filter = false;//屏蔽参数
        
        //添加触发方法
        Event::insetListen('payConfig', function (&$param) {
            
            if (empty($param)) {
                return $param;
            }
            unset($param['JS_API_CALL_URL']);
            unset($param['NOTIFY_URL']);
        });
        
        $data = $this->getPayConfig($this->getPayData()); 
   
        $monery = $this->getReturnMonery();
        
        $this->setParameter('out_trade_no', $wxData[OrderWxpayModel::$wxPay_id_d]);
        $this->setParameter('out_refund_no',$wxData[OrderWxpayModel::$wxPay_id_d]);
        $this->setParameter('total_fee',    $monery*100);
        $this->setParameter('refund_fee',   $monery*100);
        $this->setParameter('op_user_id',   WxPayConfPub::$MCHID_d);

        $res = $this->getResult();
        return $this->parseResulte($res);
        
    }
    /**
     * 
     * Array(
    'return_code' => SUCCESS
    'return_msg' => OK
    'appid' => wx68fa4860d905394f
    'mch_id' => 1338796801
    'nonce_str' => i3ewE9koS6FduZg4
    'sign' => B4C2C9AAB6BAD65CA7F84D46FBDD7010
    'result_code' => SUCCESS
    'transaction_id' => 4004602001201708217456116894
    'out_trade_no' => wx_201708211618361113433830-26
    'out_refund_no' => wx_201708211618361113433830-26
    'refund_id' => 50000604142017082101600104127
    'refund_channel' => Array
        (
        )

    'refund_fee' => 1
    'coupon_refund_fee' => 0
    'total_fee' => 1
    'cash_fee' => 1
    'coupon_refund_count' => 0
    'cash_refund_fee' => 1
     */
    
    /**
     * @desc 处理返回结果 【微信】
     */
    public function parseResulte($res)
    {
        if (empty($res)) {
            return false;
        }
        
        if ($res['return_code'] !== 'SUCCESS' || $res['result_code'] !== 'SUCCESS') {
            return false;
        }
        
        //更新状态
//         $_SESSION['org'];//退换货表
//         $_SESSION['RETURN_GOODS_ID'];//商品编号
        $orderId = substr(strrchr($res['out_trade_no'], '-'), 1);//主键编号 确保唯一性
        
        //订单商品表
        $orderGoodsModel = BaseModel::getInstance(OrderGoodsModel::class);
        
        $model = BaseModel::getInstance(OrderReturnGoodsModel::class);
        $status = $model->saveStatus($_SESSION['org']);
        $status = $orderGoodsModel->editStatus( $_SESSION['RETURN_GOODS_ID'], $orderId, $status);
        return $status;
    }
    
}