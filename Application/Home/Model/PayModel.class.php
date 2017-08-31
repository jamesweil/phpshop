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

namespace Home\Model;

use Think\Model;
use Common\PCAlipay\MD5\Lib\AlipaySubmit;

/**
 * 支付模型 
 */
class PayModel extends Model
{

    /**
     * 支付宝支付
     * 必选三个参数:
     * out_trade_no  商户订单
     * subject       订单名称
     * total_fee     付款金额
     * body          商品描述,可选
     */
    public function alipaySubmit(array $info) 
    {
        // 商户订单号,唯一
        if (empty($info['out_trade_no'])) {
            return false;
        }
        // 订单名称
        if (empty($info['subject'])) {
            return false;
        }
        // 付款金额
        if (!is_numeric($info['total_fee'])) {
            return false;
        }

        // 获取支付宝配置
        $alipay_config = C('ALIPAY_CONFIG');
        if (empty($alipay_config)) {
            return false;
        }

        $parameter = array(
            "service"      => $alipay_config['service'],
            "partner"      => $alipay_config['partner'],
            "seller_id"    => $alipay_config['seller_id'],
            "payment_type" => $alipay_config['payment_type'],
            "notify_url"   => $alipay_config['notify_url'],
            "return_url"   => $alipay_config['return_url'],
            
            "anti_phishing_key" => $alipay_config['anti_phishing_key'],
            "exter_invoke_ip"   => $alipay_config['exter_invoke_ip'],
            "out_trade_no"      => $info['out_trade_no'],
            "subject"           => $info['subject'],
            "total_fee"         => $info['total_fee'],
            "body"              => $info['body'],
            "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
            //其他业务参数根据在线开发文档，添加参数.文档地址:
            //https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
            //如"参数名"=>"参数值"
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        return $html_text;
    }
}