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
// |简单与丰富！让外表简单一点，内涵就会更丰富一点。
// +----------------------------------------------------------------------
// |让需求简单一点，心灵就会更丰富一点。
// +----------------------------------------------------------------------
// |让言语简单一点，沟通就会更丰富一点。
// +----------------------------------------------------------------------
// |让私心简单一点，友情就会更丰富一点。
// +----------------------------------------------------------------------
// |让情绪简单一点，人生就会更丰富一点。
// +----------------------------------------------------------------------
// |让环境简单一点，空间就会更丰富一点。
// +----------------------------------------------------------------------
// |让爱情简单一点，幸福就会更丰富一点。
// +----------------------------------------------------------------------
namespace Home\Controller;

use Common\Tool\Tool;
use Common\UpacpApp\SDK\AcpService;
use Common\UpacpApp\SDK\LogUtil;
use Common\Model\BaseModel;
use Common\Model\OrderWxpayModel;
use Think\Controller;
use Common\TraitClass\NoticeTrait;
use Common\TraitClass\InternetTopTrait;
use Home\Model\OrderModel;
use Common\Model\PayModel;
use Home\Model\GoodsModel;
use Common\TraitClass\SmsVerification;
use Think\Hook;
use Common\Behavior\WangJinTing;
use Home\Logical\Model\UserLogic;
use Common\TraitClass\DispatcherPayTrait;
use Common\TraitClass\RechargeCommonTrait;

/**
 * 支付控制器 
 */
class PayOrderController extends Controller 
{
    use NoticeTrait;
    use SmsVerification;
    use InternetTopTrait;
    use DispatcherPayTrait;
    use RechargeCommonTrait;
    private $modelObj;
    
    private $payType = 0;
    
    
    private $shippingType = 0; //余额充值相关
    
    
    /**
     * @return the $shippingType
     */
    public function getShippingType()
    {
        return $this->shippingType;
    }
    /**
     * @param number $shippingType
     */
    public function setShippingType($shippingType)
    {
        $this->shippingType = $shippingType;
    }

    /**
     * @return the $modelObj
     */
    public function getModelObj()
    {
        return $this->modelObj;
    }

    /**
     * @param field_type $modelObj
     */
    public function setModelObj($modelObj)
    {
        $this->modelObj = $modelObj;
    }

    // 支付结果回调页面
    
    public function __construct()
    {
        parent::__construct();
        
        header("Content-type:text/html;charset=utf-8");
        $information = $this->getIntnetInformation();
        
        $this->assign($information);
        
        Hook::add('reade', WangJinTing::class);
        
        $this->assign('str', $this->getFamily());
        
        $this->assign('intnetTitle', $information['intnet_title'].' - 支付结果');
        $this->isLogin();
    }
    
    public function InertnetWxpay ($orderData = null)
    {
        //统计商品销量
        $order_id = $_SESSION['order_id'];
        $orderGoodsModel = M("OrderGoods");
        $goodsModel = M("Goods");
        $goods_ids = $orderGoodsModel->where(['order_id'=>$order_id])->getField("goods_id",true);
        if(!empty($goods_ids)){
            foreach($goods_ids as $goods_id){
                $sales_sum = $goodsModel->where(['id'=>$goods_id])->getField('sales_sum');
                if($sales_sum){
                    $sales_sum++;
                    $goodsModel->where(['id'=>$goods_id])->setField("sales_sum",$sales_sum);
                }else{
                    $sales_sum = 1;
                    $goodsModel->where(['id'=>$goods_id])->setField("sales_sum",$sales_sum);
                }
            }
        }

        $count = Tool::checkPost($_SESSION, ['is_numeric' => ['order_id', 'total'], ''], true, ['order_id', 'total']);
        // +------------------------------------


        if (empty($orderData)) {
            $goods_orders = BaseModel::getInstance(OrderModel::class);
            
            $info = $goods_orders->getOrderInfoById($_SESSION['order_id']);
        } else {
            $info = $orderData;
        }
        
       
        $url = U('Order/order_myorder');
        $this->promptParse($info, '参数有误', $url);
        
        $info[OrderModel::$orderSn_id_d] = $info[OrderModel::$orderSn_id_d].'-'.$info[OrderModel::$id_d];
         
        $data = $this->getPayConfigByDataBase($info);
        
        $this->promptParse($data, '参数有误', $url);
        $_SESSION['what_pay_id'] = $data[PayModel::$id_d];
        
        $this->info = $info;
        
        $this->checkURL = U('Nofity/checkOrderStatus');
        
        $this->type = 0;
        
        $this->dispatcherPay($data);
    }
    
    
    
    /**
     * 获取配置数据
     */
    public function getPayConfigByDataBase (array $info)
    {
        if (empty($info)) {
            return array();
        }
        $payModel = BaseModel::getInstance(PayModel::class);
         
        $data = $payModel->getPayInfo($info[OrderModel::$payType_d], $info[OrderModel::$platform_d]);
         
        if (empty($data)) {
            return array();
        }
        $_SESSION['what_pay_id'] = $data[PayModel::$id_d];
        return $data;
    }
    
    
    // 微信支付接口【手机端】
    public function wxPay()
    {
        Tool::checkPost($_POST, array('is_numeric' => array('out_trade_no','total_fee')),true,array( 'body', 'total_fee','out_trade_no')) ?true :$this->ajaxReturnData(null, '400', '参数错误');
        
        self::validateOrder($_POST['out_trade_no']);
        
        $wxPay = 'wx_'.Tool::connect('Token')->toGUID();
        
        $status = OrderWxpayModel::getInitation()->add(array(
            'order_id'  => $_POST['out_trade_no'],
            'wx_pay_id' => $wxPay
        ));
        $this->prompt($status, null, '支付失败', false);
        
        // 支付结果回调页面
        $NOTIFY_URL = C('domain').'/API/Nofity/wxNotify';
        
        // STEP 1. 构造一个订单。
        $order = array(
            "body"          => $_POST['body'],
            "appid"         => self::APP_ID,
            "mch_id"        => self::MCH_ID,
            "nonce_str"     => mt_rand(),
            "notify_url"    => $NOTIFY_URL,
            "out_trade_no"  => $wxPay.'-'.$_POST['out_trade_no'],
            "spbill_create_ip" => $_SERVER['REMOTE_ADDR'],
            "total_fee" => ($_POST['total_fee'] * 100), // 坑！！！这里的最小单位时分，跟支付宝不一样。1就是1分钱。只能是整形。
            "trade_type" => "APP"
        );
        
        Tool::connect('Token');
        
        $result = Tool::wx($order, self::QQAPI, self::PARTNER_ID);
        
        
        // 使用$result->nonce_str和$result->prepay_id。再次签名返回app可以直接打开的链接。
        $data = array(
            "noncestr" => "" . $result->nonce_str,
            "prepayid" => "" . $result->prepay_id, // 上一步请求微信服务器得到nonce_str和prepay_id参数。
            "appid" => self::APP_ID,
            "package" => "Sign=WXPay",
            "partnerid" => self::MCH_ID,
            "timestamp" => time()
        );
        ksort($data);
        $sign = "";
        foreach ($data as $key => $value) {
            if ($value && $key != "sign" && $key != "key") {
                $sign .= $key . "=" . $value . "&";
            }
        }
        $sign .= "key=" . self::PARTNER_ID;
        $sign = strtoupper(md5($sign));
        $data['sign'] = $sign;
    
       $this->updateClient($data, '操作');
    }
   
   
    private  function validateOrder($order)
    {
        $order = OrderModel::getInitation()->getGoodsByOrderSn($order);
        $this->prompt($order, null, '没有该订单或商品价格错误');
    }
    
    // 银联支付
    public function  ylPay()
    {
        Tool::checkPost($_POST, array('is_numeric' => array('orders_num', 'price_shiji')),true,array( 'price_shiji', 'orders_num')) ?true :$this->ajaxReturnData(null, '400', '参数错误');
        
        // 判断是否存在该订单
        self::validateOrder($_POST['orders_num']);
        $domin = C('domain');
        $params = array(
            'merId' => self::MERID, // 商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => $_POST['orders_num'], // 商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => date("YmdHis"), // 订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => $_POST['price_shiji'] * 100, // 交易金额，单位分，此处默认取demo演示页面传递的参数
            // 'reqReserved' =>'透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据
            // 以下信息非特殊情况不需要改动
            'version' => '5.0.0', // 版本号
            'encoding' => 'utf-8', // 编码方式
            'txnType' => '01', // 交易类型
            'txnSubType' => '01', // 交易子类
            'bizType' => '000201', // 业务类型
            'frontUrl' => $domin.'/API/PayOrder/ylNotify', // 前台通知地址
            'backUrl' =>  $domin.'/API/PayOrder/ylNotify', // 后台通知地址
            'signMethod' => '01', // 签名方法
            'channelType' => '08', // 渠道类型，07-PC，08-手机
            'accessType' => '0', // 接入类型
            'currencyCode' => '156'// 交易币种，境内商户固定156
        ) ;
    
        AcpService::sign($params); // 签名
      
        
        $data = AcpService::post($params, self::SDK_App_Request_Url);
        
        if (count($data) <= 0) { // 没收到200应答的情况
           LogUtil::printResult(self::SDK_App_Request_Url, $params, "");
           $this->ajaxReturnData(null,'400','支付失败');
        }
        $this->updateClient($data,'支付');
    }
    
    /**
     * 银联返回通知 
     */
    public function ylNotify()
    {
        if (empty($_POST['signature']))
        {
            echo '签名为空';
        }
        
        echo AcpService::validate($_POST) ? '验签成功' : '验签失败';
        $orders_num = $_POST['orderId']; // 其他字段也可用类似方式获取
        $respCode = $_POST['respCode']; // 判断respCode=00或A6即可认为交易成功
    
        if ($respCode == '00' || $respCode == 'A6') 
        {
             $status =  OrderModel::getInitation()->save(array(
                'order_status' => OrderModel::YesPaid
                ), array(
                    'where' => array('order_sn_id' => $orders_num)
               ));
        }
    }


    /**
     * 选择支付订单样式
     */
    public function payOrder()
    {
        $order_id = I('order_id', '-1');

        // 检测订单是否存在
        $field = 'id,order_sn_id,price_sum,user_id,pay_time,order_status,pay_type,status';
        $info = M('order')->field($field)->where(['id'=>$order_id])->find();

        if (!is_array($info) || count($info) < 1 || $info['status'] != 0) {
            $this->success('该定单不存在');
        }

        // 检测订单是否已支付
        if ($info['order_status'] == 1) {
            $this->success('该定单已经支付');
        }

        // 检测需要支付的金额,积分商品
        if ($info['price_sum'] <= 0) {
            $this->noNeedPay($info['order_sn_id']);
        }

        // 显示余额
        $user_id = $_SESSION['user_id'];
        $field   = 'id,user_id,account_balance,lock_balance,status';
        $balance = M('balance')->field($field)->where(['user_id' => $user_id])->find();


        // 获取支付方式
        $list = M('payType')->select();

        //网站设置        
        $information = $this->getIntnetInformation();

        
        $this->assign('pay_img', C('pay_type_img'));
        
        $this->assign('url', U('needPay'));
        
        $this->assign($information);
        $this->assign('balance', $balance);
        $this->assign('info', $info);
        $this->assign('list', $list);
        $this->display('payOrder');
    }


    /**
     * 不需要支付
     */
    public function noNeedPay($sn_id)
    {
        $data = [
            'order_sn_id' => $sn_id,
            'pay_time'    => time()
        ];
        $this->redirect('nofity/noNeedPay', $data);
    }


    /**
     * 需要支付
     */
    public function needPay()
    {
        $validata = ['order_id', 'pay_type'];
        Tool::checkPost($_POST, ['is_numeric' => $validata], true, $validata) ? : $this->error('参数错误');

        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        $field = [
            OrderModel::$payTime_d,
            OrderModel::$createTime_d,
            OrderModel::$deliveryTime_d,
        ];
        
        $orderData  = $orderModel->getData($_POST['order_id'], $field);
        $this->promptParse(($orderData) || $orderData[OrderModel::$status_d] !=0, '订单状态有误');
        
        //更新支付方式
        $status = $orderModel->save([
            OrderModel::$id_d => $_POST['order_id'],
            OrderModel::$payType_d => $_POST['pay_type']
        ]);
        
        $this->prompt($status!==false, '支付错误');
        
        $orderData[OrderModel::$payType_d] = $_POST['pay_type'];
        $_SESSION['order_id'] = $orderData[OrderModel::$id_d];
        $_SESSION['total']    = $orderData[OrderModel::$priceSum_d];
        
        return $this->InertnetWxpay($orderData);
    }
    
    /**
     * 余额支付
     */
    public function blanceResult ()
    {
        $validate = ['password', 'price_sum'];
        
        Tool::checkPost($_POST, [], false, $validate) ? : $this->error('参数错误');
        
        // 验证密码
        $userLogic = new UserLogic($_SESSION['user_id'], $_POST);
        
        $userData  = $userLogic->getUsersBlanace();
       
        //验证是否存在该用户
        $this->prompt($userData, $userLogic->getError());
       
        //验证是密码是否正确
        $status = $userLogic->vaildatePassWord();
       
        $this->promptParse($status, $userLogic->getError());
       
        //验证是密码是否正确验证余额是否足够
        $status = $userLogic->validateBalance();
       
        $this->promptParse($status, $userLogic->getError());
        
        $status = $userLogic->balancePayParse();
        
        $this->promptParse($status, $userLogic->getError());
        
        unset($_POST['password']);
        $this->redirect('Nofity/balanceNofty', $_POST);
        
    }
    /**
     * 获取商品模型对象 
     */
    public function getGoodsModel()
    {
        return BaseModel::getInstance(GoodsModel::class);
    }
    public function showDisplay($pram = 'InertnetWxpay')
    {
        $this->display($pram);
    }
    public function showMessage($message, $url = '')
    {
        $this->error($message, $url);
    }
    
    public function assignValue($name, $value)
    {
        return $this->assign($name, $value);
    }
}