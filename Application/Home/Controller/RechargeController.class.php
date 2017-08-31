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

use Common\Controller\ProductController;
use Common\Tool\Tool;
use Common\Model\BaseModel;
use Home\Model\PayTypeModel;
use Common\TraitClass\SmsVerification;
use Common\TraitClass\DispatcherPayTrait;
use Home\Logical\Model\RechargeLogic;
use Common\Model\PayModel;
use Common\TraitClass\RechargeCommonTrait;
class RechargeController
{
    use SmsVerification;
    use DispatcherPayTrait;
    
    use RechargeCommonTrait;
    
    /**
     * @var ProductController
     */
    private $controllerObj;
    
    
    /**
     * 参数
     *
     * @var mixed
     */
    private $args;
    
    const RECHARGE_HTML = 'PayOrder/payOrder';

   


    public function __construct($args)
    {
        $this->args = $args;
        
        $this->controllerObj = new ProductController();
    }
    
    
    /**
     * 余额充值页面
     */
    public function recharge()
    {
        $args = $this->args;
        
        $validate = [
            'account'
        ];
        
        Tool::checkPost($args, [
            'is_numeric' => $validate
        ], true, $validate) ?: $this->error('充值金额有误 ');
        
        // 获取支付方式
        $list = BaseModel::getInstance(PayTypeModel::class)->getPay();
        
        $balanceId = C('balanceId');
        
        if (array_key_exists($balanceId, $list)) {
            unset($list[$balanceId]);
        }
        // 网站设置
        $information = $this->getIntnetInformation();
        
        $_SESSION['balance_order_sn'] = Tool::connect('Token')->toGUID();
        
        $_SESSION['account_current_user'] = $_POST['account'];
        
        $info = [
            'order_sn_id' => $_SESSION['balance_order_sn'],
            'price_sum' => $args['account'],
            'id' => 0
        ];
        
        $controllerObj = $this->controllerObj;
        
        $controllerObj->assign('pay_img', C('pay_type_img'));
        
        $controllerObj->assign('info', $info);
        
        $controllerObj->assign('list', $list);
        
        $controllerObj->assign('url', U('balanceRecharge'));
        $controllerObj->display(self::RECHARGE_HTML);
    }
    
    /**
     * 余额充值处理
     */
    public function balanceRecharge ()
    {
        $validate = ['pay_type'];
    
        $args = $this->args;
        $controllerObj = $this->controllerObj;
        
        Tool::checkPost($args, ['is_numeric' => $validate], true, $validate) ? : $controllerObj->error('支付类型错误');
    
        $args['order_sn'] = $_SESSION['balance_order_sn'];
    
        $args['account']  = $_SESSION['account_current_user'];
    
        $args['pay_code'] = $args['pay_type'];
    
        unset($args['pay_type']);
    
        $rechargeLogic = new RechargeLogic($args);
    
        $insertId = $rechargeLogic->add();
    
        $controllerObj->promptParse($insertId, '充值出错了');
    
        //获取支付信息
        $model = BaseModel::getInstance(PayModel::class);
    
        $_SESSION['order_id'] = $insertId;
    
        $data = $model->getPayInfo( $args['pay_code'], 0);
        
        $controllerObj->promptParse($data, '支付配置错误');
    
        $_SESSION['what_pay_id'] = $data[PayModel::$id_d];
    
        $this->shippingType = 1; // 余额充值
    
        $this->info = [
            'price_sum'   => $_SESSION['account_current_user'],
            'order_sn_id' => $_SESSION['balance_order_sn'].'-'.$insertId
        ];
    
        $this->checkURL = U('RechargeNofity/checkRechargeStatus');
    
        $this->nofityURL = U('RechargeNofity/nofity', '', false);
       
        $this->type = 1;
        
        $this->dispatcherPay($data);
    }
    
    public function showDisplay($pram = 'PayOrder/InertnetWxpay')
    {
        $this->controllerObj->display($pram);
    }
    
    public function __set($name, $value)
    {
        $this->controllerObj->assign($name, $value);
    }
}