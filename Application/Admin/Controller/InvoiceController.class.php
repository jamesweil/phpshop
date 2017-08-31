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

use Common\Controller\AuthController;
use Common\Model\BaseModel;
use Admin\Model\OrderModel;
use Common\Tool\Tool;
use Common\Model\ExpressModel;
use Common\Model\UserAddressModel;
use Common\TraitClass\SmsVerification;
use Admin\Model\PayTypeModel;
use Common\Model\RegionModel;
use Common\Model\OrderGoodsModel;
use Admin\Model\GoodsModel;

/**
 * 发货单 
 */
class InvoiceController extends AuthController
{
    use SmsVerification;
    public function index ()
    {
        $this->condition = $this->getAppointData();
        $this->display();
    }
    
    /**
     * 获取配货单 
     */
    public function ajaxGetData ()
    {
        $addressModel = BaseModel::getInstance(UserAddressModel::class);
        
        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        Tool::connect('ArrayChildren');
        
        $where = array();
        
        $where[OrderModel::$orderStatus_d] = ['between', OrderModel::InDelivery.','.OrderModel::ReceivedGoods];
        
        $addressIdArray = $addressModel->getSearchByData($_POST, OrderModel::$addressId_d);
        
        $orderIdArray  = $orderModel->getSearchByData($_POST, OrderModel::$id_d);
        
        $where = array_merge($where, $addressIdArray, $orderIdArray);
        
        Tool::isSetDefaultValue($_POST, array('orderBy' => 'id', 'sort' => 'DESC'));
        
        $orderData  = $orderModel->getOrderData($_POST, $where, 'INVOICE_CACHE_KEY_SEDFX');
        $this->promptPjax($orderData['data'], '暂无数据');
        
        $this->cackeKey = 'INVOICE_WHAT_KEY';//设置缓存key
        
        $this->addressModel = clone $addressModel;
        //获取运送方式
        $orderData['data'] = $this->getExpress($orderData['data'], $orderModel, OrderModel::$addressId_d);
        $this->expressModel = ExpressModel::class;
        $this->model        = OrderModel::class;
        $this->order        = $orderData;
        
        $this->display();
        
    }
    
    /**
     * 配货单详情 
     */
    public function picking ($id)
    {
        $this->errorNotice($id);
        
        //订单信息
        $model = BaseModel::getInstance(OrderModel::class);
        
        $data = $model->getDataByColum($id);
        
        $this->promptParse($data);
        
        $payTypeModel = BaseModel::getInstance(PayTypeModel::class);
        
        //获取支付数据
        $data[OrderModel::$payType_d] = $payTypeModel->getUserNameById($data[OrderModel::$payType_d], PayTypeModel::$typeName_d);
        
        //获取物流信息
        $data[OrderModel::$expId_d]   = BaseModel::getInstance(ExpressModel::class)->getUserNameById($data[OrderModel::$expId_d], ExpressModel::$name_d);
        
        $userAddressModel = BaseModel::getInstance(UserAddressModel::class);
        
        // 地址数据
        $addressData = $userAddressModel->getAddressById($data[OrderModel::$addressId_d]);
       
        $areaModel = BaseModel::getInstance(RegionModel::class);
        
        $addressData = $areaModel->getDefaultRegion($addressData, $userAddressModel);
        
        $data = array_merge($addressData, $data);
        
        //商品信息
        $goods = $this->getGoodsInfoByOrder($data[OrderModel::$id_d]);
        $this->key = 'intnetConfig';
        //获取网站配置
        $this->assign('intnetConfig', $this->getGroupConfig());
        $this->assign('model', $model);
        $this->assign('addressModel', UserAddressModel::class);
        $this->assign('orderData', $data);
        $this->display();
    }
    
    /**
     * 获取订单信息 
     */
    private function getGoodsInfoByOrder ($orderId)
    {
        if (empty($orderId)) {
            return array();
        }
        Tool::connect('parseString');
        
        $orderGoodsModel = BaseModel::getInstance(OrderGoodsModel::class);
        
        $field = OrderGoodsModel::$goodsId_d.','.OrderGoodsModel::$goodsNum_d.','.OrderGoodsModel::$goodsPrice_d;
        
        $orderGoodsData  = $orderGoodsModel->getGoodsIdByOrderId($orderId, $field);
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $goodsData = $goodsModel->getGoodsData($orderGoodsData, OrderGoodsModel::$goodsId_d);
       
        return $goodsData;
        
    }
}