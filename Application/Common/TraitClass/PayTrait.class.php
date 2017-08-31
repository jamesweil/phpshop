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

use PlugInUnit\Wxpay\WxPayConfPub;
use Common\Model\PayModel;
use Common\Tool\Event;

/**
 * 支付数据处理
 * @version 1.0.1
 */
trait PayTrait
{
    private $payData; //支付数据配置
    
    private $returnMonery = 0;//退款
    
    /**
     * @return the $returnMonery
     */
    public function getReturnMonery()
    {
        return $this->returnMonery;
    }

    /**
     * @param number $returnMonery
     */
    public function setReturnMonery($returnMonery)
    {
        $this->returnMonery = $returnMonery;
    }

    protected function payConfig (array $config)
    {
        
        if (empty($config) || !is_array($config)) {
            return null ;
        }
        
        $wxConfig = new \ReflectionClass(WxPayConfPub::class);
        
        $configObj = $wxConfig->newInstance();
        
        $jsAPIURL  = $_SERVER['HTTP_HOST'].U('Mobile/Wxpay/newPay');
        
        $notify    = $_SERVER['HTTP_HOST'].U('Home/Nofity/pcWxNofity');
        
        $wxConfigConst = $wxConfig->getStaticProperties();
        
        //添加监控触发方法
        Event::listen('payConfig', $wxConfigConst);//后台 退款时触发
        
        $config[] = $jsAPIURL;
        $config[] = $notify;
        $count = count($config);
        $i = 0;
        foreach ($wxConfigConst as $key => $value) {
            if (empty($config[$i])) {
               throw new \Exception('支付数据错误');die();
            }
            if ($i > $count) {
                break;
            }
            $configObj::$$key = $config[$i];
            $i++;
        }
        return $configObj;
    }
    
    /**
     * @return the $payData
     */
    public function getPayData()
    {
        return $this->payData;
    }
    
    /**
     * @param field_type $payData
     */
    public function setPayData($payData)
    {
        $this->payData = $payData;
    }
    
    /**
     * 输出错误
     */
    protected function getPayConfig ( array $data)
    {
        try {
            $config = [
                $data[PayModel::$payAccount_d],
                $data[PayModel::$mchid_d],
                $data[PayModel::$payKey_d],
                $data[PayModel::$openId_d],
                $data[PayModel::$publicPem_d],
                $data[PayModel::$privatePem_d],
            ];
            $payConfig = $this->payConfig($config);
            return $payConfig;
        }catch (\Exception $e) {
            echo $e->getMessage();die();
        }
    }
}