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
namespace Home\Controller;

use Think\Controller;
use Common\TraitClass\InternetTopTrait;
use Common\Tool\Tool;
use Common\Model\BaseModel;
use Common\Model\UserAddressModel;
use Common\Model\RegionModel;
use Common\TraitClass\AddressTrait;
use Common\TraitClass\NoticeTrait;
use Common\Model\ExpressModel;
use Home\Model\GoodsModel;
use Home\Model\GoodsImagesModel;
use Home\Model\SpecGoodsPriceModel;
use Home\Model\FreightModeModel;
use Home\Model\OrderModel;
use Home\Model\FreightsModel;
use Home\Model\FreightConditionModel;
use Home\Model\FreightAreaModel;
use Home\Model\OrderGoodsModel;
use Home\Model\CouponListModel;
use Home\Model\CouponModel;
use Home\Model\GoodsCartModel;
use Common\TraitClass\FrontGoodsTrait;
use Home\Model\PromotionGoodsModel;
use Common\Model\PromotionTypeModel;
use Home\Model\GoodsPackageSubModel;
use Common\Tool\Extend\ArrayChildren;
use Common\Tool\Event;
use Home\Model\PromGoodsModel;
use Home\Model\InvoiceCompanyModel;
use Home\Model\InvoiceTypeModel;
use Common\TraitClass\SmsVerification;
use Home\Logical\Content;
use Think\Hook;
use Common\Behavior\WangJinTing;
use Home\Logical\Model\FreightSendAreaLogic;
use Home\Logical\Model\FreightAreaLogic;
use Common\Strategy\FreightContent;
use Common\Tool\Extend\ArrayParse;
use Home\Logical\AmountLogic;
use Home\Model\BalanceModel;
use Common\TraitClass\FreightTrait;


/**
 * 结算
 */
class SettlementController extends Controller
{
    use InternetTopTrait;
    use AddressTrait;
    use NoticeTrait;
    use FrontGoodsTrait;
    use SmsVerification;
    use FreightTrait;

    private $specData = array();

    private $validate = [
        'pay_type',
        'address_id',
        'price_sum',
        'freight_id',
    ];
    // 0没有活动，1尾货清仓，2，最新促销，3积分商城,4打印耗材
    const NO_ACTIVITY = 0;
    
    // 无活动
    const DISPLAY_HTML = 'buyNow';

    const POOP_GOODS_HTML = 'poopClearByGoodsInformation';
    
    // 尾货清仓结算商品页
    const NORMAL_GOODS_HTML = 'poopClearByGoodsInformation';

    /**
     * 结算
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->isLogin();
        // 初始化数据
        $this->hot_words = self::keyWord();
        
        $name = self::userDataExits();
        
        $information = $this->getIntnetInformation();
        
        Hook::add('reade', WangJinTing::class);
        $str = $this->getFamily();
        
        $this->assign("article_lists", $this->arctile());

        $this->assign($information);
        
        $this->assign('intnetTitle', $this->getConfig('intnet_title') . C('internetTitle.settlement'));
        
        $this->assign('str', $str);
        
        $this->assign('userId', $name);
    }
    
    // +------------------------------------------------
    // | [逻辑重构]
    // +------------------------------------------------
    public function shopping()
    {
        // 验证立即购买商品信息
        $this->validateBuyNow();
        
        // 获取商品信息
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $goodsData = $goodsModel->getGoodsContentById($_POST['goods_id']);
        
        $this->prompt($goodsData);
        
        // 活动处理
        $activityType = Content::parseCall($goodsData[GoodsModel::$status_d]);
        
        $contentObj = new Content($activityType, $goodsData);
        
        $detailAlgMoneryObj = $contentObj->newInstance();
        
        $algMoneyType = $detailAlgMoneryObj->getResult(); // 获取计算类型
        
        $this->promptParse($algMoneyType !== false, '商品活动错误');
        
        $goods = $detailAlgMoneryObj->getGoods();
        
        $goods['goods_num'] = $_POST['goods_num'];
        
        // 活动价格处理
        Content::setActivityType(C('promotion_type')); // 设置 收费处里对象
        
        $promotionType = Content::parseCall($algMoneyType);
        
        $contentObj->setActivityObj($promotionType);
        
        $contentObj->setConstructParam($goods);
        
        $goodsData = $contentObj->newInstance()->acceptCash();
        
        // +----------------------------
        $this->payAndAddress(); // 地址及其支付信息
        // |-----------------------------
        
        $this->setKeyByOpreator(GoodsModel::$id_d);
        $this->parseGoodsData(array(
            $goodsData
        )); // 商品信息
        
        $this->invoiceAllow(); // 发票信息
        
        $this->gift(); // 赠品信息
        
        $this->assign('goods_html', self::POOP_GOODS_HTML);
        
        $this->assign('totalMonery', $goodsData['totalMoney']);
        
        $this->assign('activityType', C('activity_type'));
        
        $this->assign('activityModel', PromotionTypeModel::class);
        
        $this->display(self::DISPLAY_HTML);
    }
    
    /**
     * 获取余额
     */
    public function getBalaceMoney ()
    {
        $this->ajaxReturnData([
            'money' => BaseModel::getInstance(BalanceModel::class)->getBalanceMoney(),
        ]);
    }
    
    /**
     * 立即购买
     * $this->setUseModel($goodsModel);
     * // // 是否是 促销产品
     * // $dataPromo = $this->getPromotionDataByGoods($_POST);
     * // $goodsModel->setSplit(PromotionGoodsModel::$goodsId_d); // 设置分割键
     */
    public function buyNow()
    {
        // 验证立即购买商品信息
        $this->validateBuyNow();
        // +----------------------------
        $this->payAndAddress(); // 地址及其支付信息
        
        Tool::connect('parseString');
        
        // 获取商品信息
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $goodsData = $goodsModel->getGoodsById($_POST['goods_id'], []);
        
        $this->promptParse($goodsData, '商品数据错误');
        
        $this->setKeyByOpreator(GoodsModel::$id_d);
        
        $this->parseGoodsData($goodsData);
       
        // 判断用户是否添加过发票信息
        $this->invoiceAllow(); // 发票信息
        
//         $this->gift(); // 赠品信息
        
        $this->assign('goods_html', self::NORMAL_GOODS_HTML);
        
        $this->assign('totalMonery', $goodsModel->getTotalMonery());
        
        $this->display();
    }

    /**
     * 优惠消息 立即购买
     */
    public function getPromotionDataByGoods(array $post)
    {
        if (empty($post) || ! is_array($post)) {
            return array();
        }
        
        $model = BaseModel::getInstance(PromotionGoodsModel::class);
        
        $data = $model->getPromotionData($post, 'goods_id');
        if (empty($data)) {
            return array();
        }
        
        Event::insetListen('goods', function (array &$param) use ($post) { // 监听事件
            foreach ($param as &$value) {
                $value['goods_num'] = $post['goods_num'];
                $value['price_new'] = $post['price_new'];
            }
        });
        
        $data = self::sumPromotion($data, $model);
        
        return $data;
    }

    /**
     * 计算运费
     */


    /**
     * 物流信息
     */
    public function shipping()
    {
        $expressModel = BaseModel::getInstance(ExpressModel::class);
        
        $expressData  = $expressModel->getDefaultOpen(false);
       
        $this->assign('expressData', $expressData);
        
        $this->assign('expressModel', ExpressModel::class);
        
        $this->display();
    }

    /**
     * 获取地区
     */
    public function getAreaListByUserId()
    {
        $model = BaseModel::getInstance(UserAddressModel::class);
        
        $model->setSelectColums([
            UserAddressModel::$createTime_d,
            UserAddressModel::$updateTime_d,
            UserAddressModel::$zipcode_d
        ]);
        $userAreaList = $model->getAreaListByUserId($_SESSION['user_id'], true);
        
        $this->promptPjax($userAreaList);
        
        $regionModel = BaseModel::getInstance(RegionModel::class);
        
        $userAreaList = $regionModel->getRegionByUserAddress($userAreaList, $model);
        
        $this->assign('userAddress', $userAreaList);
        
        $this->assign('model', UserAddressModel::class);
        
        $this->display();
    }

    /**
     * 生成订单
     * <pre>// 此处需要验证的 太多 为避免 浪费资源 免去验证
     * // $isPuss = $goodsModel->validatePrice($_POST['goods_id'], 'goods_id', $_POST['price_sum']);
     *
     * // $this->promptParse($isPuss, '数据验证失败');
     * // 运费 验证
     * // 获取 订单 总价
     *
     * // $total = sprintf('%01.2f', $goodsModel->getTotalMonery() * $_SESSION['discount']/100);
     *
     * // 获取运费
     * // $express = BaseModel::getInstance(FreightModeModel::class)->getShipping($_POST['freight_id']);
     *
     * // $expressMonery = $express[FreightModeModel::$fristMoney_d] * count($_POST['goods_id']);//默认 按件数 计算运费 后续 优化
     * </pre>
     *
     * @return boolean
     */
    public function BuliderOrder()
    {
        // 验证数据
        $this->validate();
        
        // 比较价格
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        // 分发
        
        Tool::connect('parseString');
        
        $useCouponMonery = 0; // 优惠券
        
        $conpouModel = null;
        
        if (! empty($_POST['couponListId']) && ! empty($_SESSION['couponId'])) { // 验证优惠券
            
            $conpouModel = BaseModel::getInstance(CouponModel::class);
            
            $useCouponMonery = $conpouModel->getDataByField($_SESSION['couponId'], CouponModel::$money_d);
        }
        
        $validateExpress = $_POST['price_sum'] - (float) $_POST[OrderModel::$shippingMonery_d] - $useCouponMonery < 0 ? false : true;
        
        $this->promptParse($validateExpress, '数据错误');
        
        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        Tool::connect('Token');
        
        $insertId = $orderModel->addOrder($_POST);
        
        $this->promptParse($insertId, '数据出错');
        
        $orderGoodsModel = BaseModel::getInstance(OrderGoodsModel::class);
        
        $status = $orderGoodsModel->addOrderGoods($_POST['goods_id'], $insertId);
        
        $this->promptParse($status, '数据出错');
        
        // 处理库存
        $amountModel = new AmountLogic($_POST['goods_id'], $orderModel);
        
        
        $status = $amountModel->checkAmountDel();
        
        $this->promptParse($status, $amountModel->getError());
        
        if ($conpouModel instanceof CouponModel) { // 更新优惠券使用信息
            $status = BaseModel::getInstance(CouponListModel::class)->updateData($_POST['couponListId'], $insertId);
        } else 
            if (! empty($_POST['cart_id'])) {
                
                $cartModel = BaseModel::getInstance(GoodsCartModel::class);
                $status = $cartModel->delCart($_POST['cart_id']);
            } else {
                $status = $goodsModel->commit();
            }
        $this->promptParse($status, '创建失败');
        $_SESSION['order_id'] = $insertId;
        $_SESSION['total'] = $_POST['price_sum'];
        
        if (! empty($_POST['prom_id'])) {
            $_SESSION['prom_id'] = $_POST['prom_id']['prom_id'];
            $_SESSION['type'] = $_POST['prom_id']['type'];
        }
        // 未选择新的发票类型插入最后一次的发票类型
        if ($_POST['invoice_id'] == null) {
            $this->redirect('PayOrder/InertnetWxpay');
        } else {
            $end_invoice_data = M('invoice')->where('id=' . $_POST['invoice_id'])->find();
            $end_order_id = M('order')->field('id,price_sum')
                ->where('user_id=' . $_SESSION['user_id'])
                ->order('id DESC')
                ->limit(1)
                ->find();
            $end_invoice_data['order_id'] = $end_order_id['id'];
            // 该价格有问题，确定之后再做修改
            $end_invoice_data['price'] = $end_order_id['price_sum'];
            $end_invoice_data['billing_date'] = time();
            array_shift($end_invoice_data);
            ($end_invoice_data);
            $addStatus = M('invoice')->add($end_invoice_data);
            if ($addStatus) {
                $this->redirect('PayOrder/InertnetWxpay');
            } else {
                return false;
            }
        }
    }

    /**
     * 数据验证
     */
    private function validate()
    {
        unset($_POST['id'], $_POST['c_id']);
        $muster = array_merge($this->validate, array(
            'goods_id',
            'formWhat'
        ));
        
        Tool::checkPost($_POST, array(
            'is_numeric' => $this->validate,
            'remarks',
            'couponListId'
        ), true, $this->validate) ? true : $this->error('操作失败');
       
        if ($_SESSION['bulidOrder'] !== $_POST['formWhat']) {
            $this->error('恶意攻击 将负法律责任');
        }
        
        if ($_POST['price_sum'] <= 0) {
            $this->error('数据错误');
        }
    }

    /**
     * 获取 代金券
     */
    public function coupon()
    {
        $model = BaseModel::getInstance(CouponListModel::class);
        
        $model->setCouponByUserId((int) $_SESSION['user_id']);
        
        // 获取用户优惠券数据
        $data = $model->getUserCouponByUserId();
        
        // 是否有效
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        Tool::connect('parseString');
        
        $data = $couponModel->validateCoupon($data, $model);
        
        $this->data = $data;
        
        $this->model = CouponModel::class;
        
        $this->mCouponList = CouponListModel::class;
        $this->display();
    }

    /**
     * 编辑收货地址
     */
    public function editAddress()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => 'id'
        ), true, array(
            'id'
        )) ?: $this->ajaxReturnData(null, 0, '操作失败');
        
        $model = BaseModel::getInstance(UserAddressModel::class);
        
        $region = $model->find($_POST['id']);
        
        $regionModel = BaseModel::getInstance(RegionModel::class);
        
        $region = $regionModel->getEditAddressData($region, $model);
        
        $this->region = UserAddressModel::class;
        
        $this->data = $region;
        
        $this->json = json_encode($region);
        $this->display();
    }

    /**
     * 购物车生成订单
     */
    public function cartSettlement()
    {
        // 获取购物车ID list
        Tool::checkPost($_POST, array(), false, [
            'cart_id'
        ]) ? true : $this->promptParse(null, '数据错误');
        
        $cartModel = BaseModel::getInstance(GoodsCartModel::class);
        
        $cartModel->setSelectColums([
            GoodsCartModel::$createTime_d,
            GoodsCartModel::$updateTime_d
        ]);
        $cartData = $cartModel->getCartDataByUserId($_POST['cart_id'], true);
        
        $this->promptParse($cartData, $cartModel->getError());
        
        // 获取商品信息
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $this->setUseModel($goodsModel);
        
        Tool::connect('parseString');
        
        $goodsModel->setGoodsNumKey(GoodsCartModel::$goodsNum_d);
        $goodsModel->setPriceNewKey(GoodsCartModel::$priceNew_d);
        
        $goodsData = $goodsModel->getGoodsByCartArray($cartData, GoodsCartModel::$goodsId_d);
        
        $arrayObj = Tool::connect('ArrayChildren' ,$goodsData);
        
        $goodsData = $arrayObj->inTheSameState(GoodsModel::$status_d);
        
        //分发给活动处理
        // 活动处理
        $activityObj = new Content(null, $goodsData);
        
        $goodsData = $activityObj->parseForeachActivity();
       
        $arrayObj->setData($goodsData);
        
        $goodsData = $arrayObj->d3ToD2();
       
        $this->promptParse($goodsData, '商品活动错误');

        $arrayObj->setData($goodsData);
        
        $goodsData = $arrayObj->inTheSameState('poopStatus');
        // +--------------------------------------------------
        
        // 活动价格处理
        Content::setActivityType(C('promotion_type')); // 设置 收费处里对象
        
        $activityObj->setConstructParam($goodsData);
        
        $goodsData = $activityObj->parseForeachActivity();;
        
        $arrayObj->setData($goodsData);
        
        $goodsData = $arrayObj->d3ToD2();
        
        $goodsData = $this->goodsPackGoods($cartModel->getPackageArray(), $goodsData);
        
        $this->promptParse($goodsData, '商品数据错误');
        
        $this->setKeyByOpreator(GoodsCartModel::$goodsId_d);
        
        $specItem = $this->parseGoodsData($goodsData); // 商品信息
        
        //支付类型及其地址
        $this->payAndAddress();
        
        $_SESSION['bulidOrder'] = sha1(md5(base64_encode('MyNameIsWq') . time())); // formId
                                                                                   
        // 统一键名
        $specItem = $this->unoipy($specItem, GoodsCartModel::$id_d);
        
        

        $this->assign('cartModel', GoodsCartModel::class);
        
        $this->assign('activityType', C('activity_type'));
        
        $this->assign('activityModel', PromotionTypeModel::class);
        
        $this->assign('goods_html', self::POOP_GOODS_HTML);
        
        $this->assign('goodsImage', GoodsImagesModel::class);
        
        $this->assign('specModel', SpecGoodsPriceModel::class);
        
        $this->assign('goodsModel', GoodsModel::class);
        
        $this->assign('orderModel', OrderModel::class);
        
        $this->assign('goodsSpec', $specItem);
        
        $this->assign('numberTotal', $cartModel->getNumber());
        $this->assign('promGoods', PromGoodsModel::class);
        $this->assign('proModel', PromotionTypeModel::class);
        
        $this->assign('totalMonery', $cartModel->getTotalMonery());
        
        $this->display(self::DISPLAY_HTML);
    }

    private function goodsPackGoods(array $package, array $goodsData)
    {
        if (empty($package) || empty($goodsData)) {
            return $goodsData;
        }
        
        $specSubModel = BaseModel::getInstance(GoodsPackageSubModel::class);
        
        $goodsModel = $this->getUseModel();
        
        // $specSubModel->setFieldAs(GoodsCartModel::$priceNew_d);//设置字段别名
        
        $goodsPack = $specSubModel->getPackageByCart($package, GoodsCartModel::$goodsId_d);
        
        if (empty($goodsPack)) {
            return $goodsData;
        }
        
        $goodsDataByPack = $goodsModel->getGoodsByCartArray($goodsPack, GoodsPackageSubModel::$goodsId_d);
        
        // 合并数组【购物车数据与套餐数据】
        $goodsDataByPack = (new ArrayChildren(array()))->mergeArray($goodsDataByPack, $package);
        
        return array_merge($goodsDataByPack, $goodsData);
    }

    /**
     * 验证是否可满足条件使用
     */
    public function validateCouponUse()
    {
        $validate = [
            'c_id',
            'totalMonery'
        ];
        
        Tool::checkPost($_POST, [
            'is_number' => $validate
        ], true, $validate) ?: $this->ajaxReturnData(null, 0, '操作失败');
        $model = BaseModel::getInstance(CouponModel::class);
        
        // 验证是否符合条件
        $isUse = $model->isUse($_POST['c_id'], $_POST['totalMonery']);
        
        $this->promptPjax($isUse, '未满足条件不能使用');
        $_SESSION['couponId'] = $_POST['c_id'];
        $this->updateClient($isUse, '验证');
    }

    /**
     * 获取发票类型
     */
    public function invoice()
    {
        $data = BaseModel::getInstance(InvoiceTypeModel::class)->getOpenInvoice('INVOICE_TYPE_CONTENTENT');
        
        $company = BaseModel::getInstance(InvoiceCompanyModel::class)->getOpenInvoice('INVOICE_COMPANY');
        // 弹出窗显示默认选择
        // 查找是否有选择发票抬头的默认值
        $check_invoice = M('invoice')->where(array(
            'user_id' => $_SESSION['user_id'],
            'invoice_header' => array(
                'neq',
                '个人'
            ),
            'check_status' => 1
        ))->find();
        $this->assign('check_invoice', $check_invoice);
        $this->assign('company', $company);
        $this->assign('type', InvoiceTypeModel::class);
        $this->assign('companyModel', InvoiceCompanyModel::class);
        $this->assign('invoiceType', $data);
        $this->display();
    }

    /**
     * 添加发票
     */
    public function invoice_add()
    {
        if (IS_POST) {
            // 先去判断该抬头是否存在
            $where['invoice_header'] = $_POST['invoice_header'];
            $where['user_id'] = $_SESSION['user_id'];
            $where['check_status'] = 1;
            if (! M('invoice')->where($where)->select()) {
                $data['pay_taxes_code'] = (I('post.pay_taxes_code') == '') ? 1 : I('post.pay_taxes_code');
                $data['invoice_type'] = trim(I('post.type'));
                $data['invoice_header'] = trim(I('post.invoice_header'));
                // $check_header = trim(I('post.invoice_header'));
                $data['invoice_title'] = trim(I('post.content'));
                $data['mobile'] = I('post.mobile');
                $data['email'] = I('post.email');
                $data['type'] = 2;
                $data['user_id'] = $_SESSION['user_id'];
                $data['create_time'] = time();
                $data['check_status'] = 1;
                // $length = count($_POST['invoice_array']);
                // 更新之前的默认选择
                M('invoice')->where(array(
                    'user_id' => $_SESSION['user_id']
                ))->save(array(
                    'check_status' => 0
                ));
                $res = M('invoice')->add($data);
                if (! $res) {
                    $this->ajaxReturn(array(
                        'code' => 0
                    ));
                } else {
                    // 返回选择的发票数据
                    $data = M('Invoice')->where(array(
                        'user_id' => $_SESSION['user_id'],
                        'check_status' => 1
                    ))->find();
                    // dump($data);exit;
                    $this->ajaxReturn(array(
                        'code' => 1,
                        'invoice_data' => $data
                    ));
                }
            } else {
                $this->ajaxReturn(array(
                    'code' => 2
                ));
            }
        }
    }
}