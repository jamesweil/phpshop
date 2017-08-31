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

use Common\Model\BaseModel;
use Common\Model\PayModel;
use PlugInUnit\PCAlipay\RSA\Lib\AlipayNotify;

/**
 * 支付宝回调验证
 */
trait AlipayNotifyTrait
{

    protected $returnURL = '';

    public function alipayResultParse()
    {
        $alipay_config = C('ALIPAY_CONFIG');
        
        $returnURL = empty($this->returnURL) ? $alipay_config['return_url'] : $this->returnURL;
        
        $alipay_config['return_url'] = $returnURL;
        
        $alipay_config['notify_url'] = $alipay_config['notify_url'];
        
        if (isset($_GET['callBack'])) {
            unset($_GET['callBack']);
        }
        
        $payModel = BaseModel::getInstance(PayModel::class);
        
        $config = $payModel->getPayConfigByPrimarykey($_SESSION['what_pay_id']);
        
        // file_put_contents('./Uploads/v_c.txt', print_r($config, true));
        
        $this->promptParse($config, $payModel->getError());
        
        $alipay_config['partner'] = $config[PayModel::$payAccount_d];
        $alipay_config['seller_id'] = $config[PayModel::$mchid_d];
        $alipay_config['key'] = $config[PayModel::$payKey_d];
        $alipay_config['private_key'] = $config[PayModel::$privatePem_d];
        $alipay_config['alipay_public_key'] = $config[PayModel::$publicPem_d];
        
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        // file_put_contents('./Uploads/v_r.txt', $verify_result);
        $data = [];
        
        if (! $verify_result) {
            return $data;
        }
        
        if ($_GET['trade_status'] != 'TRADE_FINISHED' && $_GET['trade_status'] != 'TRADE_SUCCESS') {
            return $data;
        }
        // file_put_contents('./Uploads/v_g.txt', print_r($_GET, true));
        $out_trade_no = $_GET['out_trade_no']; // 商户订单号
        $trade_no = $_GET['trade_no']; // 支付宝交易号
        $trade_status = $_GET['trade_status']; // 交易状态
        $subject = $_GET['subject']; // 商品名称
        $total_fee = $_GET['total_fee']; // 支付总价
        $notify_time = $_GET['notify_time']; // 商品名称
        $notify_time = strtotime(urldecode($notify_time));
        
        $data = [
            'order_sn_id' => $out_trade_no,
            'pay_time' => $notify_time,
            'trade_no' => $trade_no
        ];
        // file_put_contents('./Uploads/v_d.txt', print_r($data, true));
        return $data;
    }

    /**
     * 支付宝异步通知
     */
    public function alipayNotify()
    {
        $alipay_config = C('ALIPAY_CONFIG');
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        file_put_contents('./Uploads/goods.txt', $verify_result);
        if ($verify_result) {
            $out_trade_no = $_POST['out_trade_no'];
            $trade_no = $_POST['trade_no'];
            $trade_status = $_POST['trade_status'];
            
            // 退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            if ($trade_status == 'TRADE_FINISHED') {
                
                // 成功,立马返回的信息
            } elseif ($trade_status == 'TRADE_SUCCESS') {}
            echo "success";
        } else {
            echo "fail";
        }
    }
}