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
use Admin\Model\OrderModel;
use Common\Tool\Tool;
use Common\Model\UserAddressModel;
use Admin\Model\UserModel;
use Common\Model\OrderGoodsModel;
use Admin\Model\GoodsModel;
use Common\TraitClass\CancelOrder;
use Common\Model\BaseModel;
use Common\Model\ExpressModel;
use Common\Model\RegionModel;
use Admin\Model\CouponModel;
use Admin\Model\CouponListModel;
use Admin\Model\OrderReturnGoodsModel as org;
use Home\Model\PayTypeModel;
use Admin\Model\OrderReturnGoodsModel;
//短信工厂类->发货提示
use Common\Controller\MsmFactory;

/**
 * 订单控制器
 * @author 王强
 * @copyright 亿速网络
 * @version  v1.1.2
 * @link http://yisu.cn
 */
class OrderController extends AuthController
{
    use CancelOrder;
    //订单列表 - 全部订单
    public function orderList()
    {
       $this->condition = $this->getOrderStatus();   
       $this->display();
    }
    
    /**
     * ajax 获取数据 
     */
    public function ajaxGetData()
    {
        // 获取订单
        //初始化页数排序
        
        $addressModel = BaseModel::getInstance(UserAddressModel::class);
        
        $userArray = array();
        if (!empty($_POST['realname'])) {
            $userArray = $addressModel->getOrderByRealName($_POST);
            $this->prompt($userArray, null);
        }
        $model = BaseModel::getInstance(OrderModel::class);
        
        Tool::connect('ArrayChildren');
       
        $userModel = BaseModel::getInstance(UserModel::class);
        $userIdArray = $userModel->getSearchByData($_POST, OrderModel::$userId_d);
        
        Tool::isSetDefaultValue($_POST, array('orderBy' => 'id', 'sort' => 'DESC'));
        
        $orderWhere = $model->parseOrderCondition($userArray);
        
        $where = array_merge($userIdArray, $orderWhere);
        
        // 获取订单数据
        $data = $model->getOrderData($_POST, $where);
        
        $this->promptPjax($data['data'], '暂无数据');
        
        $this->addressModel = clone $addressModel;
        
        $data['data'] = $this->getExpress($data['data'], $model, OrderModel::$addressId_d);
        
       
        $this->expressModel = ExpressModel::class;
        $this->model        = $model;
        $this->order        = $data;  
        $this->assign('orderStatus', S('order'));
        $this->display();
    }
    
    /**
     * 订单详情
     */
    public function orderDetail()
    {
       
        $data = $this->getOrder();
       
        $userModel = BaseModel::getInstance(UserModel::class);
        //传递给用户模型
        $userData =$userModel->userInfoByOrder($data, array(UserModel::$userName_d, UserModel::$email_d, UserModel::$mobile_d, UserModel::$memberDiscount_d));
        
        $this->prompt($userData, null, '未知错误', false);
        
        //收货人信息
        $model = BaseModel::getInstance(UserAddressModel::class);
        
        $receive = $model->receiveManByOrder($data,  array($model::$id_d, $model::$userId_d, $model::$status_d), true);
        
        $this->promptParse($receive, null, '未知错误', false);
        
        //传递地区表
        $regionModel = BaseModel::getInstance(RegionModel::class);
        
        $receive     = $regionModel->getDefaultRegion($receive, $model);
        
        //传递给商品模型
        $goodsDatail = $this->getOrderGoodsInfo($data, true);
       
       
        $this->userModel  = $userModel;
        $this->goods      = $goodsDatail;
        $this->userAddressModel = $model;
        $this->receive    = $receive;
        $this->order      = array_merge($data, $userData);
        $this->orderStatus  = S('order');
        $this->display();
    }
    
    
    /**
     * 积分等优惠券等费用信息 
     */
    public function couponInformation ()
    {
        Tool::checkPost($_POST, ['is_numeric' => array('id', 'monery')], true, ['id', 'monery']) ? : $this->ajaxReturnData(null, 0, '操作失败');
        
        $conpouListModel = BaseModel::getInstance(CouponListModel::class);
        
        $userCoupon = $conpouListModel->getUserByOrder($_POST['id']);
        
        $conpouModel = BaseModel::getInstance(CouponModel::class);
        
        $monery = $conpouModel->getCouponById($userCoupon[CouponListModel::$cId_d]);
        
        $this->conpouListModel = CouponListModel::class;
        
        $this->couponMonery      = $monery; 
        
        $this->display();
    }
    
    /**
     * 发货
     */
    public function sendGoods()
    {
        $order = $this->getOrder();
        
        //收货人信息
        
        $userModel = BaseModel::getInstance(UserAddressModel::class);
        
        $receive = $userModel->receiveManByOrder($order,  array($userModel::$id_d, $userModel::$userId_d, $userModel::$status_d), true);
        
        $receive = BaseModel::getInstance(RegionModel::class)->getDefaultRegion($receive, $userModel);
        
        $this->prompt($receive, null, '未知错误', false);
        
        $goodsInfo = $this->getOrderGoodsInfo($order, true);
        
        $this->prompt($goodsInfo, null, '未知错误', false);
        
        $this->order   =   array_merge($order, $receive);
        $this->goodsInfo = $goodsInfo;
        $this->userAddressModel = $userModel;
        
        $this->display();
    }
    
    
    /**
     * 填写快递单号 
     */
    
    public function delivery()
    {
       // dump($_POST);exit;
        Tool::checkPost($_POST, array('is_numeric' => array('id', 'express_id')), true, array('id', 'express_id')) ? true : $this->error('参数错误');
        
        $model = BaseModel::getInstance(OrderModel::class);
        
        $orderStatus = $model->getOrderStatusByUser($_POST['id']);
        
        if ($orderStatus != $model::YesPaid && $orderStatus > $model::AlreadyShipped)
        {
            $this->error('订单状态有误');
        }
        
        
        $_POST[$model::$orderStatus_d] = $model::AlreadyShipped;
        $status = $model->save($_POST);
        //加上发货提示功能
        $order_data=M('order')->where(array('id'=>$_POST['id']))->find();
        //获取发送短信数据
        $address_data=M('UserAddress')->where(array('id'=>$order_data['address_id']))->find();
        //快递名称
        $name=M('express')->where(array('id'=>$order_data['exp_id']))->find()['name'];
        $id['id']=4;
        $id['real_name']=$address_data['realname'];
        $id['order_sn_id']=$order_data['order_sn_id'];
        $id['express_id']=$order_data['express_id'];
        $id['delivery_time']=$order_data['delivery_time'];
        $id['express_name']=$name;
        $this->delivery_message_send($address_data['mobile'],$id);
        $status ? $this->success('成功',U('orderList')) : $this->error('失败');
        
    }

    /**
     * 短信发货提示
     */
    public function delivery_message_send($mobile,$type_id)
    {
        $is_start=unserialize(M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->find()['config_value'])['IS_START_CONFIG'];
        //判断总开关
        if($is_start==1)
        {
            $is_start=M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->find()['id'];
            //单独的开关
            $is_start=M('TemplateCategory')->where('template_category_id='.$is_start.' AND id=4')->find()['status'];
            if($is_start==1)
            {
                //实例化短信类
                $SMS=MsmFactory::factory('huaxin');
                $SMS->send_msg($mobile, $type_id);
            }
        }
    }
    
    /**
     * 公共方法 
     */
    private function getOrder()
    {
        // 检测传值
        Tool::checkPost($_GET, array('is_numeric' => array('order_id')), true, array('order_id')) ? true : $this->error('参数错误');
        $model = BaseModel::getInstance(OrderModel::class);
        //获取订单信息
        $data = $model->getOrderById($_GET['order_id']);
        $_SESSION['shippingMonery'] = $data[OrderModel::$shippingMonery_d];
        $this->prompt($data, null, '订单有误', false);
        
        //获取运送方式
        $shippingModel = BaseModel::getInstance(ExpressModel::class);
        
        $data[OrderModel::$expId_d] = $shippingModel->getExpressTitle($data[OrderModel::$expId_d ]);
        //获取支付方式
        $payType = BaseModel::getInstance(PayTypeModel::class);
        
        $data[OrderModel::$payType_d] = $payType->getUserNameById($data[OrderModel::$payType_d], PayTypeModel::$typeName_d);
        $this->orderModel = $model;
        return $data;
    }
    
    private function getOrderGoodsInfo(array $data, $open = FALSE)
    {
        if (!is_array($data) || empty($data))
        {
            return array();
        }
      
        //传递给商品订单模型
        
        $orderGoodsModel = BaseModel::getInstance(OrderGoodsModel::class);
        
        //去除不查询的字段
        $noSelect = $orderGoodsModel->deleteFields(array($orderGoodsModel::$id_d));
       
        $orderGoods  = $orderGoodsModel->getGoodsId($data, $noSelect);
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
       
        Tool::connect('parseString');
        //传递给商品模型
        $goodsDatail = $goodsModel->getOrderInfo($orderGoods, array($goodsModel::$id_d.' as '. $orderGoodsModel::$goodsId_d, $goodsModel::$title_d));
        
        empty($open) ? : $this->goodsModel = $goodsModel;
        
        empty($open) ? : $this->orderGoodsModel = $orderGoodsModel;
        return $goodsDatail;
    }
    
    /**
     * 退货 
     */
    public function returnOrder()
    {
         $orderStatus = $this->flagOption(OrderModel::ReturnAudit, OrderModel::AuditSuccess);
         
         if (empty($orderStatus)) {
             $this->updateClient(null, '失败');
         }
        
         $url = U('cancelOrderMonery', array('idsaw' => $_POST['id']));
         
         $this->updateClient($url, '操作');
         
    }
    /**
     * 退款 
     */
    public function cancelOrderMonery()
    {
        $status = $this->cancelOrder();
      
        $update = false;
        if (!empty($status)) {
            
            $model = BaseModel::getInstance(OrderModel::class);
            
            $update = $model->save(array(
               $model::$orderStatus_d => $model::ReturnMonerySucess
            ),array(
               'where' => array($model::$id_d => $status['id'])
            ));
        }
        $this->prompt($update, null, '系统异常', false);
        
        $this->status = $status;
        $this->display();       
    }
    
    /**
     * 不予退款 
     */
    public function noReturn()
    {
        $orderStatus = $this->flagOption(OrderModel::ReturnAudit, OrderModel::AuditFalse);
         
        $this->updateClient($orderStatus, '操作');
    }
    
    /**
     * @copyright 版权所有©亿速网络
     * 退货，不退货操作 
     */
    private  function flagOption($status, $editStatus)
    {
        if (!is_numeric($status) || !is_numeric($editStatus)) {
            return false;
        }
        Tool::checkPost($_POST, array('is_numeric' => array('id')), true, array('id')) ? true : $this->error('参数错误');
        
        //获取订单
        
        $model = BaseModel::getInstance(OrderModel::class);
        
        $orderStatus = $model->find(array(
            'field' => array($model::$id_d, $model::$priceSum_d, $model::$orderStatus_d),
            'where' => array($model::$id_d => $_POST['id'])
        ));
        
      
        if ( empty($orderStatus) ||$orderStatus[$model::$orderStatus_d] != $status ) {
           return false;
        }
         
        $_POST[$model::$orderStatus_d] = $editStatus;
         
        $status = $model->save($_POST);
        
        return $status ? $status : false;
    }
    
    /**
     * 单商品退货 
     * @copyright 版权所有©亿速网络
     */
    public function returnGoods ()
    {
        $model = BaseModel::getInstance(org::class);
        
        Tool::isSetDefaultValue($_POST, [
            org::$orderId_d => null,
        ]);
        
        
        $this->returnGoodsType = C('returnGoods');
        
        $this->model = org::class;
        $this->display();
    }
    
    /**
     * ajax 获取 退货单 
     * @copyright 版权所有©亿速网络
     */
    public function ajaxGetReturnGoods ()
    {
        $model = BaseModel::getInstance(org::class);
        $listTitle = $model->getListTitle([
            org::$orderId_d,
            org::$type_d,
            org::$createTime_d,
        ]);
        
        
        Tool::isSetDefaultValue($_POST, array(//设置默认值 版权所有©亿速网络
            'orderBy' => org::$createTime_d,
            'sort'    => BaseModel::DESC
        ));
       
        $where = array();
        Tool::connect('ArrayChildren');
        
        $model->setNoValidate([//不检测的搜索键
            org::$orderId_d
        ]);
        
        $where = $model->buildSearch($_POST);
       
        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        Tool::connect('parseString');
     
        $where[org::$orderId_d] = $orderModel->getSearch($_POST);
        
        
        $data = $model->getContent($_POST, $where);
        
        $data['data'] = $orderModel->getOrderByOrderReturn($data['data'], org::$orderId_d);
        
        $goodsModel   = BaseModel::getInstance(GoodsModel::class);
        
        $data['data'] = $goodsModel->getGoodsByOrderReturn($data['data'], org::$goodsId_d);
        //@copyright 版权所有©亿速网络
        $this->assign('orderModel', OrderModel::class);
        
        $this->assign('title', $listTitle);
        
        $this->assign('goodsModel', GoodsModel::class);
        
        $this->assign('typeData', C('returnGoods'));
        $this->assign('refund', C('refund'));
        $this->assign('isReceive', C('is_receive'));
        $this->model = org::class;
      
        $this->data = $data;
        
        $this->display();
    }
    
    /**
     * 获取退货单详情 
     */
    public function getReturnGoodsInfo($id)
    {
        //检测传值
        $this->errorNotice($id);
        
        $model = BaseModel::getInstance(org::class);
        
        //退货信息
        $detail = $model->getReturnDetail($id);
        
        $this->prompt($detail);
        
        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        //订单信息
        $detail[OrderModel::$orderSn_id_d] = $orderModel->getUserNameById($detail[org::$orderId_d], OrderModel::$orderSn_id_d);
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        //商品信息
        $detail[GoodsModel::$title_d] = $goodsModel->getUserNameById($detail[org::$goodsId_d], GoodsModel::$title_d);
        
        //用户信息
        $userModel = BaseModel::getInstance(UserModel::class); 
        
        $detail[UserModel::$userName_d] = $userModel->getUserNameById($detail[org::$userId_d], UserModel::$userName_d);
        //是否退货成功
        $status = BaseModel::getInstance(OrderGoodsModel::class)->getStatus($detail[OrderReturnGoodsModel::$orderId_d], $detail[OrderReturnGoodsModel::$goodsId_d]);
        $this->assign('org', org::class);
        $this->assign('refund', C('refund'));
        $this->assign('returnGoods', C('returnGoods'));
        $this->assign('order', OrderModel::class);
        $this->assign('status', $status);
        $this->assign('goods', GoodsModel::class);
        $this->assign('user', UserModel::class);
        $this->data = $detail;
        
        $this->display();
    }
    /**
     * 收货状态更改 
     */
    public function isReceive ()
    {
        $validate = ['id', 'is_receive'];
        Tool::checkPost($_POST, array('is_numeric' => $validate), true, $validate) ? true : $this->ajaxReturnData(null, 0, '操作失败');
        
        $_POST['is_receive'] =  intval($_POST['is_receive']) > 2 ? 1 :$_POST['is_receive'];
        
        $model = BaseModel::getInstance(org::class);
        
        $status = $model->save($_POST);
        
        $this->updateClient($status, '操作');
        
    }
    /**
     * 退款
     */
    public function cancelReturnOrder($type, $id)
    { 
        $this->errorNotice($id);
        $this->errorNotice($type);
        
        $model = BaseModel::getInstance(org::class);
        
        $data = $model->getReturnData($id, $type);
      
        $this->promptParse($data, $model->getError());
        $orderModel = BaseModel::getInstance(OrderModel::class);
        //是否已支付
        $isAlipay = (int)$orderModel->getUserNameById($data[org::$orderId_d], OrderModel::$orderStatus_d);
       
        $this->promptParse($isAlipay >= OrderModel::YesPaid || $isAlipay < OrderModel::ReturnMonerySucess , '未支付');
        
        //检测状态 是否正常
        $orderGoodsModel = BaseModel::getInstance(OrderGoodsModel::class);
        
        $status = $orderGoodsModel->getIsStatus($data[org::$orderId_d], $data[org::$goodsId_d], OrderModel::ReturnAudit);
        
        $this->promptParse($status, '订单状态有误');
        
        $orderModel->setSColums([
            OrderModel::$payType_d,
            OrderModel::$platform_d
        ]);
        
        $_SESSION['org'] = $id; 
        $_SESSION['RETURN_GOODS_ID'] = $data[org::$goodsId_d];
        $payType = $orderModel->getOrderById($data[org::$orderId_d]);
        
        //获取退款金额
        $orderGoodsModel = BaseModel::getInstance(OrderGoodsModel::class);
       
        $monery = $orderGoodsModel->getMonery($data[org::$orderId_d], $data[org::$goodsId_d]);
        
        $this->currtModel = clone $orderModel;
       
        $status = $this->cancelOrder($monery, $data[org::$orderId_d], $payType);
        
       !empty($status) ? $this->success('退款成功，请查收') : $this->error('退款失败');
    }
    
    /**
     * 修改 退货状态 
     */
    public function editReturnGoods ()
    {
        $colum = ['id', 'status'];
        Tool::checkPost($_POST, ['is_numeric' => ['id', 'status']], true, $colum) ? : $this->ajaxReturnData(null, 0, '操作失败');
       
        $model = BaseModel::getInstance(OrderReturnGoodsModel::class);
        
        $status = $model->save($_POST);
        $this->promptPjax($status, '保存错误');
        $this->ajaxReturnData(['url' => U('returnGoods')]);
    }
}