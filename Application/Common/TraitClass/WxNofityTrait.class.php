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

use PlugInUnit\Wxpay\Notify\NotifyCommonPub;
use Home\Logical\Model\WxPayAssignKeyLogic;

/**
 * 微信处理
 */
trait WxNofityTrait
{
    /**
     * 通知
     */
    public function nofityWx()
    {
        // 使用通用通知接口
        $notify = new NotifyCommonPub();
        
        // 存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];

        file_put_contents('./Uploads/resd.xml', $xml);
        
        $notify->saveData($xml);
        
        $data = $notify->getData();

        $payLogic = new WxPayAssignKeyLogic($data);
        
        $payConfig = $payLogic->assignKey();


        if (!is_object($payConfig)) {
            echo 'ERROR';
            die();
        }

        $isSign = $notify->checkSign();
        file_put_contents('./Uploads/isSign.txt', (int) $isSign);
        if ($isSign == FALSE) {
            $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
            echo 'ERROR';
            die();
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
        }
        $returnXml = $notify->returnXml();

        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        
        file_put_contents('./Uploads/re.xml', $returnXml);
        if ($data["return_code"] == "FAIL") {
            echo 'ERROR';
            die();
        }


        // 进行自己的数据库操作
        $orders_num = $data["out_trade_no"];
        $orderId = substr($orders_num, strpos($orders_num, '-') + 1);
        return $orderId;
    }
    
    private function msg($status)
    {
        if (empty($status)) {
            echo 'ERROR';
            die();
        }
    }
}

