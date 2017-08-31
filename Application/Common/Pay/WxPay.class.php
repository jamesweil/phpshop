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

use PlugInUnit\Wxpay\Pay\UnifiedOrderPub;
use Common\Tool\Tool;
use Common\Model\OrderWxpayModel;
use Common\Model\BaseModel;
use Common\Model\PayModel;
use PlugInUnit\Wxpay\WxPayConfPub;
use Common\TraitClass\PayTrait;

class WxPay extends UnifiedOrderPub
{
    use PayTrait;

    /**
     * 微信支付
     */
    public function pay($obj)
    {
        if (! is_object($obj)) {
            throw new \Exception('参数错误');
        }
        // 生成不同的支付码
        $wxPay = 'wx_' . Tool::connect('Token')->toGUID();
        
        $model = BaseModel::getInstance(OrderWxpayModel::class);
        
        $sn = base64_decode(I('get.sshIDSAIHI'));
        
        $sn = ! empty($sn) ? $sn : $_SESSION['order_id'];
        $wxPay = $wxPay . '-' . $sn;

        // 更新支付码
        $status = $model->alipayError($sn, array(
            OrderWxpayModel::$orderId_d => $sn,
            OrderWxpayModel::$wxPay_id_d => $wxPay,
            OrderWxpayModel::$type_d     => $obj->getType()
        ));
        
        $info = $obj->getInfo();
        
        // 自定义订单号，此处仅作举例
        // $timeStamp = time();
        // $out_trade_no = \WxPayConf_pub::APPID.$timeStamp;
        if (empty($info['price_sum'])) {
            echo 'ERROR';
            header('Refresh: 3; url=' . U('index'));
            die();
        }
        
        $price_sum = $info['price_sum'];
        
        $data = $this->getPayData();
        
        if (empty($data)) {
            $obj->showMessage($data, '参数有误');
        }
        
        $payConfig = $this->getPayConfig($data);
        
        $urlNofity = $obj->getNofityURL();
        $domin = C('domin');
        $urlNofity = empty($urlNofity) ? WxPayConfPub::$NOTIFY_URL : $domin . $urlNofity . '/callBack/rechargeWx.html';
        
        
        // // 设置统一支付接口参数
        // // 设置必填参数
        // // appid已填,商户无需重复填写
        // // mch_id已填,商户无需重复填写
        // // noncestr已填,商户无需重复填写
        // // spbill_create_ip已填,商户无需重复填写
        // // sign已填,商户无需重复填写
        $this->setParameter("body", "亿速网络"); // 商品描述
        $this->setParameter("out_trade_no", $wxPay); // 商户订单号
                                                    // $unifiedOrder->setParameter("total_fee","1");//总金额
        $this->setParameter("total_fee", $price_sum * 100); // 总金额
        $this->setParameter("notify_url", $urlNofity); // 通知地址
        $this->setParameter("trade_type", "NATIVE"); // 交易类型
        $this->setParameter("attach", $data[PayModel::$id_d]); // 附加数据
                                                               // //非必填参数，商户可根据实际情况选填
                                                               // //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
                                                               // //$unifiedOrder->setParameter("device_info","XXXX");//设备号
                                                               //
                                                               // //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
                                                               // //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
                                                               // //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
                                                               // //$unifiedOrder->setParameter("openid","XXXX");//用户标识
                                                               // //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
                                                               
        // 获取统一支付接口结果
        $unifiedOrderResult = $this->getResult();
        
        $code_url = null;
        
        // 商户根据实际情况设置相应的处理流程
        if ($unifiedOrderResult["return_code"] == "FAIL") {
            // 商户自行增加处理流程
            echo "通信出错：" . $unifiedOrderResult['return_msg'] . "<br>";
            die();
        } elseif ($unifiedOrderResult["result_code"] == "FAIL") {
            // 商户自行增加处理流程
            echo "错误代码：" . $unifiedOrderResult['err_code'] . "<br>";
            echo "错误代码描述：" . $unifiedOrderResult['err_code_des'] . "<br>";
            die();
        } 

        elseif ($unifiedOrderResult["code_url"] != NULL) {
            // 从统一支付接口获取到code_url
            $code_url = $unifiedOrderResult["code_url"];
            // 商户自行增加处理流程
            // ......
        }
        
        $obj->out_trade_no = $wxPay;
        $obj->code_url = $code_url;
        $obj->unifiedOrderResult = $unifiedOrderResult;
        $obj->total_fee = $price_sum;
        $obj->check_notify = $obj->getCheckURL();
        $obj->showDisplay();
    }
}