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
namespace Home\Controller;

use Think\Controller;
use Common\TraitClass\NoticeTrait;
use Common\TraitClass\SmsVerification;
use Common\TraitClass\InternetTopTrait;
use Think\Hook;
use Common\Behavior\WangJinTing;
use Common\TraitClass\WxNofityTrait;
use Home\Logical\Model\RechargeLogic;
use Common\TraitClass\AlipayNotifyTrait;
use Common\TraitClass\BalanceParseTrait;
use Common\Behavior\Decorate;
use Common\Behavior\AlipaySerialNumber;
use Common\Model\BaseModel;
use Home\Model\RechargeModel;
use Common\TraitClass\WxListenResTrait;

class RechargeNofityController extends Controller
{
    use NoticeTrait;
    use SmsVerification;
    use InternetTopTrait;
    use WxNofityTrait;
    use AlipayNotifyTrait;
    use BalanceParseTrait;
    use WxListenResTrait;
    
    /**
     * 余额充值相关页面
     * @var string
     */
    const RECHARGE_RELEVANT = 'Nofity/rechargeRelevant';
    
    public function __construct()
    {
        parent::__construct();
    
        Hook::add('reade', WangJinTing::class);
    
        $information = $this->getIntnetInformation();
        $this->assign('hot_words', self::keyWord());
    
        $this->assign('intnetTitle', $information['intnet_title']);
    
        $this->assign('str', $this->getFamily());
    
        $this->assign($information);
    }
    
    public function wxShow ($orderSnId, $display)
    {
        $this->promptParse($orderSnId, '充值单号错误');
        
        $orderId = substr(strrchr($orderSnId, '-'), 1); // 主键编号 确保唯一性
        
        $rechargeLogic = new RechargeLogic($orderId);
        
        $className = $rechargeLogic->getModelClass();
        
        $data = $rechargeLogic->getRechargeInfo();
        
        $this->assign('intnetTitle', '支付成功');
        $this->assign('total_fee', $data[$className::$account_d]);
        
        $this->assign('payRelated', self::RECHARGE_RELEVANT);
        
        $this->display('Nofity/'.$display);
    }
    
    /**
     * 余额支付通知
     */
    public function nofity ()
    {
        file_put_contents('./Uploads/recharge.txt', print_r($_GET, true));
        if (empty($_GET['callBack'])) {
            echo 'ERROR';die();
        }

        $fun = $_GET['callBack'];
        
        $status = $this->$fun();
    }
    
    /**
     * 执行方法处理 微信
     */
    protected function rechargeWx()
    {
        $orderId = $this->nofityWx();
        
        $this->orderId = $orderId;
        
        Hook::add('aplipaySerial', Decorate::class);
        
        $status = $this->parseByBalance();
        
        $this->msg($status);
        
        echo "SUCCESS";
        die();
        
    }
    
    /**
     * 支付宝支付通知
     */
    protected function rechargeAl()
    {

        $this->returnURL = U('RechargeNofity/nofity', ['callBack' => 'rechargeAl'], true, true);

        $data = $this->alipayResultParse();

        $url = U('Assets/balance');
        
        $this->promptParse($data, '请联系平台客服，确认是否支付', $url);
        
        $orderSnId = $data['order_sn_id'];

        $orderId = substr(strrchr($orderSnId, '-'), 1); // 主键编号 确保唯一性;
        
        $this->orderId = $orderId;
        
        $data['order_sn_id'] = $orderId;
        
        $this->tradeNo = $data['trade_no'];

        Hook::add('aplipaySerial', AlipaySerialNumber::class);
        
        $status = $this->parseByBalance();
        
        $this->promptParse($status, '充值状态更新失败', '请联系平台客服', $url);
        
        $this->wxShow($orderSnId, 'success');
    }
    
    /**
     * 余额监听支付
     */
    public function checkRechargeStatus ($orderSnId)
    {
        $this->promptPjax($orderSnId, '订单号错误');
    
        $snId = substr(strrchr($orderSnId, '-'), 1); // 主键编号 确保唯一性
    
        $status = BaseModel::getInstance(RechargeModel::class)->getUserNameById($snId, RechargeModel::$payStatus_d);
    
        $this->url = 'RechargeNofity/wxShow';
    
        $this->payNotice($status, $orderSnId);
    }
}