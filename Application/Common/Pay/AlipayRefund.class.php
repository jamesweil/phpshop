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

use PlugInUnit\PCAlipay\RSA\Lib\AlipaySubmit;
use Common\TraitClass\NoticeTrait;
use Common\TraitClass\PayTrait;
use Common\Model\PayModel;
use Common\Model\BaseModel;
use Common\Model\AlipaySerialNumberModel;

/**
 * 支付宝退款 
 * @author 王强
 * @version 1.0.1
 */
class AlipayRefund extends AlipaySubmit
{
    use NoticeTrait;
    use PayTrait;

    /**
     * 微信退款
     */
    public function refundMonery( $obj, array $info)
    {
       
        if (empty($info) || ! is_array($info) || ! is_object($obj)) {
            return array();
        }
        
        // 获取支付宝配置
        $alipay_config = C('ALIPAY_REFUND_CONFIG');
        
        
        if (empty($alipay_config)) {
            $obj->error('参数错误');die();
        }
        $data = $this->getPayData();
        
        //获取流水号
        $model = BaseModel::getInstance(AlipaySerialNumberModel::class);
        
        $order_sn_id = $info['order_sn_id'];
        
        $order_sn_id = substr(strrchr($order_sn_id, '-'), 1);//主键编号 确保唯一性
        

        $serialData = $model->getSerial($order_sn_id);
        
        $monery = $this->getReturnMonery();
        if (empty($data) || empty($serialData[AlipaySerialNumberModel::$orderId_d])) { 
            $obj->showMessage('参数错误');die();
        }
        
        $alipay_config['partner'] = $data[PayModel::$payAccount_d];
        $alipay_config['seller_user_id'] = $data[PayModel::$mchid_d];
        $alipay_config['private_key'] = $data[PayModel::$privatePem_d];
        $alipay_config['alipay_public_key'] = $data[PayModel::$publicPem_d];
        
        $parameter = array(
            "service"      => $alipay_config['service'],
            "partner"      => $data[PayModel::$payAccount_d],
            "seller_user_id"    => $data[PayModel::$mchid_d],
            "refund_date"	=> trim($alipay_config['refund_date']),
            "notify_url"   =>  $alipay_config['notify_url'],
            "batch_no"      => date("YmdHis",time()).rand(10000, 1000000),
            "_input_charset"    => trim(strtolower($alipay_config['input_charset'])),
            'batch_num'         => 1,
            'detail_data'       => $serialData[AlipaySerialNumberModel::$alipayCount_d].'^'.$monery.'^'.'协商退款',
        );
        $this->setAlipay_config($alipay_config);
        //建立请求
        $html_text = $this->buildRequestForm($parameter,"get", "确认");
        echo  $html_text;die();
    }
}