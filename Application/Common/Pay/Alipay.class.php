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

use Common\Model\PayModel;
use PlugInUnit\PCAlipay\RSA\Lib\AlipaySubmit;
use Common\TraitClass\PayTrait;

class Alipay extends AlipaySubmit
{
    use PayTrait;

    /**
     * 支付宝支付
     */
    public function pay($obj)
    {
        if (! is_object($obj)) {
            throw new \Exception('参数错误');
        }
        
        $info = $obj->getInfo();
        
        if (empty($info['price_sum']) || empty($info['order_sn_id'])) {
            $obj->showMessage('参数错误');
        }
        
        // $goodsModel = $obj->getGoodsModel();
        
        // $title = $goodsModel->getUserNameById($info['goods_id'], $goodsModel::$title_d);
        
        // $obj->promptParse($title, '参数错误');die();
        
        // 获取支付宝配置
        $alipay_config = C('ALIPAY_CONFIG');
        if (empty($alipay_config)) {
            $obj->error('参数错误');
            die();
        }
        
        $data = $this->getPayData();
        
        if (empty($data)) {
            $obj->showMessage($data, '参数错误');
        }
        $alipay_config['partner'] = $data[PayModel::$payAccount_d];
        $alipay_config['seller_id'] = $data[PayModel::$mchid_d];
        $alipay_config['key'] = $data[PayModel::$payKey_d];
        
        $alipay_config['private_key'] =  $data[PayModel::$privatePem_d];
        
        $alipay_config['alipay_public_key'] = $data[PayModel::$publicPem_d];
        
        $urlNofity = $obj->getNofityURL();
        
        $urlNofity = empty($urlNofity) ? $alipay_config['return_url'] : U('RechargeNofity/nofity', [
            'callBack' => 'rechargeAl'
        ], true, true);
            
        $parameter = array(
            "service" => $alipay_config['service'],
            "partner" => $data[PayModel::$payAccount_d],
            "seller_id" => $data[PayModel::$mchid_d],
            "payment_type" => $alipay_config['payment_type'],
            "notify_url" => $alipay_config['notify_url'],
            "return_url" => $urlNofity,
            "anti_phishing_key" => $alipay_config['anti_phishing_key'],
            "exter_invoke_ip" => $alipay_config['exter_invoke_ip'],
            "out_trade_no" => $info['order_sn_id'],
            "subject" => '订单商品支付',
            "total_fee" => $info['price_sum'],
            "body" => '',
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
        )
        // 其他业务参数根据在线开发文档，添加参数.文档地址:
        // https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
        // 如"参数名"=>"参数值"
        ;
        $this->setAlipay_config($alipay_config);
        // 建立请求
        $html_text = $this->buildRequestForm($parameter, "get", "确认");
        echo $html_text;
        die();
    }
}