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

use Common\Tool\Tool;
use Common\Model\BaseModel;
use Home\Model\GoodsCartModel;
use Home\Model\OrderModel;
use Common\TraitClass\FrontGoodsTrait;

/**
 * 购物车
 */
class CartController extends BaseController
{

    use FrontGoodsTrait;
    /**
     * 过滤登陆用户
     */
    public function _initialize()
    {
        if(empty($_SESSION['user_id'])){
            if (IS_AJAX) {
                $url = U('Public/login');
                $this->ajaxReturnData(['url' =>$url], 0, '请先登录!');
            }

            $this->redirect('Public/login');
        }
        parent::_initialize();
        $this->intnetTitle = $this->intnetTitle.' - '.C('internetTitle.cart');
    }


    /**
     * 购物车列表
     * 购物车默认100限制,默认获取最近一个月的数据
     * 商品优惠活动:1.满减, 2.降价/打折 (活动只能享受一种)
     */
    public function goods()
    {
        // 获取购物车中的商品数量
        $type    = I('GET.type', 1);    // 1.显示正常商品 2.显示降价商品
        $user_id = $_SESSION['user_id'];// 商品id

        $goods_list   = D('goodsCart')->getCartGoods($user_id, 1);
        $package_list = D('goodsCart')->getCartGoods($user_id, 2);

        $cart_count   = count($goods_list);
        $cart_cuts    = 0;

        foreach ($package_list as $key => &$vo) {
            $discount = 0;
            foreach ($vo['sub'] as &$vo1) {
                $discount  += $vo1['discount'];
                $sum = $vo1['price_member'] * $vo['goods_num'];
                $vo1['sum'] = sprintf('%.2f', $sum);
            }
            $vo['discount'] = sprintf('%.2f', $discount);
            $vo['discount_sum'] = sprintf('%.2f', $discount * $vo['goods_num']);
            $cart_count    += count($vo['sub']);
            
            $down = ($vo['price_new'] > $discount);
            if ($down) {
                $cart_cuts += count($vo['sub']);
            }
            if ($type == 2 && !$down) {
                unset($package_list[$key]);
            }
        }
        unset($vo, $vo1, $key);
        foreach ($goods_list as $key => &$vo) {
            $sum = $vo['price_member'] * $vo['goods_num'];
            $vo['sum'] = sprintf('%.2f', $sum);
            $down = ($vo['price_new'] > $vo['price_member']);
            if ($down) {
                $cart_cuts++;
            }
            if ($type == 2 && !$down) {
                unset($goods_list[$key]);
            }
        }

        // 查找最后删除的五件商品
       // $delete = D('goodsCart')->getLastDelete($user_id);
        $delete=M('goodsCart')->where(array('is_del'=>1,'user_id'=>$_SESSION['user_id']))->order('id DESC')->limit(5)->select();
        foreach($delete as $k=>$v)
        {
            $delete[$k]['title']=M('Goods')->where(array('id'=>$v['goods_id']))->find()['title'];
            $delete[$k]['stock']=M('Goods')->where(array('id'=>$v['goods_id']))->find()['stock'];
            $delete[$k]['price_member']=M('Goods')->where(array('id'=>$v['goods_id']))->find()['price_member'];
        }
        //dump($delete1);exit;
        // 猜你喜欢
        $_SESSION['goodsPId'] = $goods_list[array_rand($goods_list)]['goods_id'];
        $guessLove  = $this->guessLove();

        // 我的关注(收藏夹)
        $collection = D('collection')->goodsByuser($user_id);

        // 最近浏览
        $recent     = $this->recent();

        $this->assign('cart_del', $delete);
        $this->assign('cart_count', $cart_count);
        $this->assign('cart_cuts', $cart_cuts);
        $this->assign('cart_goods', $goods_list);
        $this->assign('collection', $collection);
        $this->assign('guessLove', $guessLove);
        $this->assign('recent', $recent);
        $this->assign('package_list', $package_list);
        $this->assign('type', $type);
        $this->display();
    }


    
    /**
     * 获取指定商品促销信息
     */
    public function promotionGoods($gid)
    {
        $time = time(); 
        $sql  = 'select m.goods_id,m.activity_price,p.name,p.description,p.group '
            .' from db_promotion_goods as m,db_prom_goods as p where m.prom_id=p.id AND m.goods_id='.$gid
            .' AND p.status=1 AND p.start_time<'.$time.' AND p.end_time>'.$time.' LIMIT 1';

        $data = M()->query($sql);
        return $data;
    }


    /**
     * 获取猜你喜欢 
     */
    public function guessLove() 
    {
        $productId = cookie('productId');
        $productId = str_replace(':', ',', $productId);
        if (empty($productId)) {
            return array();
        }
        $goods_model  = D('goods');
        $spec_model   = M('specGoodsPrice');
        $classId      = $goods_model->field('class_id')->where(['id'=> ['in', $productId]])->find();
        $productGoods = $goods_model->guessLove($classId, $productId, 1, 3);
        foreach ($productGoods as $key => &$goods) {
            $goods['pic_url'] = $goods_model->image($goods['id']);
            $price            = $spec_model->field('price')->where(['goods_id'=>$goods['id']])->find();
            $goods['price']   = $price['price'];
        }

        return $productGoods;
    }


    /**
     * 最近浏览
     */
    public function recent()
    {
        $user_id = $_SESSION['user_id'];
        $field   = 'gid as id,goods_pic as pic_url,goods_name as title,goods_price as price';
        $list    = M('foot_print')->field($field)->where('uid='.$user_id)->order('id DESC')->limit('0,5')->select();
        return $list;
    }

    

    /**
     * 移入收藏夹
     * 1.添加到收藏夹
     * 2.需要删除在购物车的东西
     */
    public function move_coll()
    {
        $id_list = I('GET.goods_id', 0);
        $user_id = $_SESSION['user_id'];

        $ret = 0;
        if (is_string($id_list) && intval($id_list) > 0) {
            $model   = D('goodsCart');
            $id_list = explode(',', $id_list);
            foreach ($id_list as $goods_id) {
                $ret = $model->moveCollection($goods_id, $user_id);
                if ($ret == false) {
                    break;
                }
            }
            $ret = intval($ret > 0);
        }
        if (IS_AJAX) {
            $this->ajaxReturn($ret);
        }
        $this->redirect('goods');
    }


    /**
     * 删除购物车里面的商品
     */
    public function cart_del()
    {
        $id_list = I('GET.cart_id', 0);
        $model   = D('goodsCart');
        if (empty($id_list)) {
            $this->ajaxReturn(0);
        }
        $result = $model->where(['id'=>['in', $id_list]])->save(['is_del' => 1]);
        if (IS_AJAX) {
            $this->ajaxReturn(intval($result>0));
        }
        $this->redirect('goods');
    }

    
    /**
     * 添加到购物车
     */
    public function cart_add() 
    {
        $valid = Tool::checkPost($_POST, array(
           'is_numeric' => array( 'goods_id', 'goods_num'),
           'buy_type'
        ), true,  array( 'goods_id', 'goods_num'));

        if ($valid == false) {
            $this->ajaxReturnData(null, 0, '数据有误');
        }
        
       $model    = BaseModel::getInstance(GoodsCartModel::class);
       $isSucess = $model->addCart($_POST);
       $status   =($isSucess === true) ? 1 : 0;
       $message  = $isSucess === true ? '添加成功' : '添加失败';
       if (IS_AJAX) {
           $this->ajaxReturnData(null, $status, $message);
       }
       $this->redirect('goods');
    }

    /**
     * 重新写的添加到购物车
     */
    public function new_cart_add()
    {
        $valid = Tool::checkPost($_POST, array(
            'is_numeric' => array( 'goods_id', 'goods_num'),
            'buy_type'
        ), true,  array( 'goods_id', 'goods_num'));

        if ($valid == false) {
            $this->ajaxReturnData(null, 0, '数据有误');
        }

        $model    = BaseModel::getInstance(GoodsCartModel::class);
        $isSucess = $model->addCart($_POST);
        $status   =($isSucess === true) ? 1 : 0;
        $message  = $isSucess === true ? '添加成功' : '添加失败';
        if (IS_AJAX) {
            $data=$this->get_cart_data();
            $count=M('GoodsCart')->where(array('user_id'=>$_SESSION['user_id'],'is_del'=>0))->count();
            $data['new_cart_count']=$count;
            $this->ajaxReturnData($data, $status, $message);
        }
        $this->redirect('goods');
    }

    /**
     * 购物车数据
     */
    public function get_cart_data()
    {
        $cartdata=M('GoodsCart')->where(array('user_id'=>$_SESSION['user_id'],'is_del'=>0))->order('buy_type ASC')->limit(5)->select();
        foreach($cartdata as $k=>$v)
        {
            $cartdata[$k]['goods_data']=M('goods')->field('id,p_id,title')->where(array('id'=>$v['goods_id'],'status'=>array('lt',3)))->find();
            if($v['buy_type']==1) {
                $cartdata[$k]['cart_url'] = U('Goods/goodsDetails', ['id' => $v['goods_id'], 'goods_num' => $v['goods_num']]);
            }else if($v['buy_type']==2){
                $cartdata[$k]['cart_url'] =U('Cart/goods');
            }
        }
        foreach($cartdata as $k=>$v)
        {
                $cartdata[$k]['pic_url']=M('GoodsImages')->where(array('goods_id'=>$v['goods_data']['p_id'],'is_thumb'=>'1'))->find()['pic_url'];
                $cartdata[$k]['title']=$v['goods_data']['title'];
        }
        return $cartdata;
    }

    /**
     * 修改购物车中的商品数量
     */
    public function update_num()
    {
        $cart_id   = I('POST.cart_id', 0, 'intval');
        $goods_num = I('POST.goods_num', 1, 'intval');
        $ret = D('goodsCart')->update_num($cart_id, $goods_num);
        $this->ajaxReturnData(intval($ret));
    }


    /**
     * 结算
     */
    public function confirm()
    {
        $user_id = $_SESSION['user_id'];

        // 获取购物车ID list
        $id_str = I('POST.cart_id', -1);
        if ($id_str == -1 || empty(trim($id_str))) {
            $this->ajaxReturnData(0, 0, '参数错误');
        }

        // 获取收货人信息
        $addr_list    = D('userAddress')->getAddrByUser($_SESSION['user_id'], false);
        $addr_default = $addr_list[0];

        // 物流确认订单
        $carry    = M('carry')->field('id,carry_title,carry_param')->select();

        // 商品信息
        // TODO:需要区分套餐购买
        $total      = '0.00';
        $model      = D('goods');
        $goods_list = D('goodsCart')->getCartGoodsById(explode(',' , $id_str));
        if (!is_array($goods_list) || count($goods_list)<1) {
            $this->ajaxReturn('购物车没有商品');
        }
        // 普通商品
        foreach ($goods_list['common'] as &$goods) {
            $goods['pic_url'] = $model->image($goods['goods_id']);
            $goods['spec']    = $model->spec($goods['goods_id']);
            $goods['sual']    = number_format($goods['goods_num'] * $goods['price_member'], 2);

            $prom  = $this->goods_promotion($goods['goods_id']); // 优惠信息
            if (count($prom) > 1) {
                $goods['province'] = ($goods['price_member']-$prom['activity_price']) * $goods['goods_num'];
                $total += $prom['activity_price'] * $goods['goods_num'];
            } else {
                $total += $goods['price_member'] * $goods['goods_num'];
            }
            $goods['prom'] = $prom;
        }

        // 套餐
        $package_count = 0;
        foreach ($goods_list['package'] as &$list) {
            foreach ($list['sub'] as &$goods) {
                $goods['pic_url'] = $model->image($goods['goods_id']);
                $goods['spec']    = $model->spec($goods['goods_id']);
                $goods['sual']    = number_format($list['goods_num'] * $goods['discount'], 2);
                $package_count++;
            }
            $total += $list['discount'] * $list['goods_num'];
        }

        $goods_info['count'] = count($goods_list['common']) + $package_count;
        $goods_info['total'] = sprintf('%.2f', $total);

        // TODO:邮费
        $goods_carry = D('goodsCart')->postage($id_str);
        $goods_carry = sprintf('%.2f', $goods_carry);

        // 获取优惠券列表
        $coupon_list = D('coupon')->getCouponByUser($user_id);

        // 获取有效积分
        $integral = D('integralUse')->valid($user_id);

        // 应该支付
        $pay_total = $goods_carry + $goods_info['total'];
        $pay_total = sprintf('%.2f', $pay_total);

        $this->assign('addr_list', $addr_list);
        $this->assign('addr_default', $addr_default);
        $this->assign('carry', $carry);
        $this->assign('goods_list', $goods_list['common']);
        $this->assign('package_list', $goods_list['package']);
        $this->assign('goods_info', $goods_info);
        $this->assign('goods_carry', $goods_carry);
        $this->assign('coupon_list', $coupon_list);
        $this->assign('integral', $integral);
        $this->assign('pay_total', $pay_total);
        $this->display();
    }


    /**
     * 计算用户的优惠情况
     */
    public function goods_promotion($goods_id)
    {
        $where = [
            'g.goods_id'   => $goods_id,
            'p.status'     => 1,
            'g.start_time' => ['lt', time()],
            'g.end_time'   => ['gt', time()]
        ];
        $field = 'g.prom_id,g.activity_price,p.name,p.description,p.expression';
        $prom = M('promotionGoods')->alias('g')->join('__PROM_GOODS__ as p ON g.prom_id=p.id')
            ->field($field)->where($where)->find();
        return $prom;
    }

  
    /**
     * 提交订单
     * 校对信息,生产订单,转向到选择支付页面
     */
    public function order_form()
    {
        $user_id = $_SESSION['user_id'];

        // 获取收货地址
        $address_id = I('POST.address_id', -1, 'intval');
        if ($address_id == -1 || empty($address_id)) {
            $this->ajaxReturn('收货地址不能为空');
        }

        // 检测地址是否有效
        $ret = M('UserAddress')->where(['id'=>$address_id, 'user_id'=>$user_id])->count();
        if (empty($ret)) {
            $this->ajaxReturn('收货地址无效');   
        }

        // 运输方式
        // $carry_type = I('POST.carry_type', -1);
        // if ($carry_type == -1 || empty($carry_type)) {
        //     $this->ajaxReturn('运输方式不能为空');
        // }

        // 商品ID
        $cart_id = I('POST.cart_id', -1);
        if ($cart_id == -1 || empty($cart_id)) {
            $this->ajaxReturn('商品不能为空');
        }

        // 买家留言
        $message  = I('POST.message', '', 'trim');

        // 获取订单商品列表
        $list  = D('goodsCart')->getCartGoodsById($cart_id);
        if (count($list['common']) < 1 || count($list['package']) < 1) {
            $this->ajaxReturn('购物车没有该商品!');
        }
        // 普通商品
        $total = 0;
        $goods_list = [];
        foreach ($list['common'] as $goods) {
            $total += $goods['goods_num'] * $goods['price_member'];
            $goods_list[] = $goods;
        }

        // 套餐商品
        foreach ($list['package'] as $package) {
            foreach ($package['sub'] as $goods) {
                $total               += $package['goods_num'] * $goods['discount'];
                $goods['goods_num']   = $package['goods_num'];
                $goods['goods_price'] = $package['discount'];
                $goods_list[]         = $goods;
            }
        }

        // 优惠券
        $discount = 0;
        $promo_id = I('POST.promo_id', -1);
        if ($promo_id > 0) {
            $prom     = D('coupon')->getCouponValidById($promo_id);
            $discount = $prom['money'];
        }

        $trans = M();
        $trans->startTrans();

        // 积分抵扣,积分作为抵扣不能为某一个商品指定信息
        $integral = I('POST.integral');
        if ($integral > 0) {
            $ret = D('integralUse')->used($user_id, $integral, 0, ['remarks'=>'抵扣支付']);
            if (intval($ret) <= 0) {
                $integral = 0;
            }
        }

        // TODO:邮费
        $freight = D('goodsCart')->postage($cart_id);

        // 商品数量*商品单价 + 运费 - 购物券
        $total = $total + $freight - $discount - ($integral/100);

        $data  = [
            'price_sum'      => $total, // 总价
            'address_id'     => $address_id, // 收货地址
            'user_id'        => $user_id, // 用户
            'create_time'    => time(),
            'order_status'   => 0,  // 未支付
            'comment_status' => 0,  // 未评论
            'pay_type'       => 0, // 支付类型
            'remarks'        => $message, // 订单备注
            'status'         => 0,  // 订单正常
            'translate'      => 0,  // 0,不需要发票   1,需要发票
        ];

        $model = BaseModel::getInstance(OrderModel::class);
        $retID = $model->add($data);
        if ($retID < 1) {
            $trans->rollback();
            $this->ajaxReturn('订单失败');
        }

        // 添加商品到 db_order_goods
        foreach ($goods_list as $goods) {
            $data = [
              'order_id'    => $retID,
              'goods_id'    => $goods['goods_id'],
              'goods_num'   => $goods['goods_num'],
              'goods_price' => $goods['goods_price'],
              'status'      => 0,
              'user_id'     => $user_id,
              'ware_id'     => $goods['ware_id']
            ];
            $ret = M('orderGoods')->add($data);
            if ($ret < 1) {
                $trans->rollback();
                $this->ajaxReturn('订单失败');
            }
        }

        // 使用优惠券
        if ($promo_id > 0) {
            $ret = D('coupon')->used($promo_id, $retID);
            if (!$ret) {
                $trans->rollback();
                $this->ajaxReturn('优惠券使用失败');
            }
        }

        $trans->commit();

        $this->redirect('/Home/payOrder/payOrder', ['order_id'=>$retID]);
    }

    
    /**
     * 订单前操作 
     */
    public function before_order()
    {
        //验证是否登录
        if( empty($_SESSION['user_id']) ) {
            $this->redirect('Public/login');
        }
        
        
        //检测传值
        \Common\Tool\Tool::checkPost($_POST, array(
            'is_numeric' => array('goods_id', 'goods_num')
        ), true, array(
            'goods_id', 'goods_num', 'form'
        )) === false ? $this->error('灌水机制已经打开') : true;
        
        if ($_SESSION['form'] !== $_POST['form']) {
            $this->error('恶意攻击将追究法律责任');
        }
      
        // 查询商品信息
        
        $goods_model = new \Home\Model\GoodsModel();
        
        $where = is_array($_POST['goods_id']) ? array('id' => array('in', implode(',', $_POST['goods_id']))) : array('id' => $_POST['goods_id']);
        
        $goods_data  = $goods_model->getGoods(array(
            'where' => $where,
            'field' => array('id,title,price_market,pic_url,fanli_jifen,taocan,min_yunfei,min_yunfei,max_yunfei,add_yunfei,chufa_address,chufa_date')
        ), $_POST['goods_num']);
        
       //获取 当前登录用户信息
        $address_model = new \Home\Model\UserAddressModel();
        
        $address_data   = $address_model->getUserAddressInfo(array(
            'where' => array('user_id' => $_SESSION['user_id']),
            'field' => array('id,status,realname,mobile,prov,city,dist,address'),
        ));
        
        //获取默认地址
        $address_default = $address_model->getDefaultAddress($_SESSION['user_id']);
        //获取运费计算的相关信息
        $freight = new \Home\Model\FreightModel();
        
        $freight_info = $freight->getFreight(array(
            'where' => array('name' => $address_default['res_ad']['prov']),
            'field' => array('areaid')
        ), new \Think\Model('region'));
        //计算运费
        $sum_freight = $goods_model->countFreight($goods_data, $address_default['res_ad']['prov'],$freight_info);
        
        //查询用户积分
        $user_model = new \Home\Model\UserModel();
        
        $integral   = $user_model->getIntegral($_SESSION['user_id']);
        $goods_info = array('count_goods' => count($_POST['goods_num']), 'zong' => $goods_data['total_monery']);
        
        //订单号生成
        $_SESSION['orders_number'] = time().sprintf("%06s",$_SESSION['user_id']);
        
        $this->res_addr    = $address_data;
        $this->list        = $goods_data;
        //商品数量
        $this->goods_info = $goods_info;
        //默认地址
        $this->res_ad     = $address_default;   
        //商品总金额
        $this->price_sum  = $goods_data['total_monery'] + $sum_freight;
        //积分
        $this->integral   = $integral;
        $this->display();
    }

	
	/*下单成功后的页面*/
    public  function make_pay_button(){
        require_once("./alipay/alipay.config.php");
        require_once("./alipay/lib/alipay_submit.class.php");

        /**************************请求参数**************************/
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = I('get.orders_num');
        $status=substr($out_trade_no,0,3);
        if($status=='cgw'){
            //订单名称，必填
            $subject = I('get.orders_num');
            $incomeordermodel=M('IncomeOrder');
            $row=$incomeordermodel->where(array('sn'=>$out_trade_no))->find();
            //付款金额，必填
            $total_fee =$row['total']; 
            //$total_fee =0.01;
            //商品描述，可空
            $body = '';
        }else{
            //订单名称，必填
            $subject = I('get.orders_num');
            /* $goods_orders = M('Goods_orders');
             $res = $goods_orders->field('price_sum')->where('orders_num='.$subject)->find();
             //付款金额，必填
             $total_fee = $res['price_sum'];*/
            //这个位置要更具订单号判断到底是商品旅游支付 还是会员充值

            $info_a = strpos($out_trade_no,'u');

            if($info_a==1){
                $info = M('User_huifei')->field('hf_money')->where(array(
                    'orders_num'=>$out_trade_no
                ))->find();
                $price_sum = $info['hf_money'];
            }else{
                $goods_orders = M('Goods_orders');
                $info = $goods_orders->field('price_sum')->where(array(
                    'orders_num'=>$out_trade_no
                ))->find();
                $price_sum = $info['price_sum'];
            }


            //付款金额，必填
            $total_fee =$price_sum;
            // $total_fee =0.01;
            //商品描述，可空
            $body = '';
        }


        /************************************************************/
//构造要请求的参数数组，无需改动
        $parameter = array(
            "service"       => $alipay_config['service'],
            "partner"       => $alipay_config['partner'],
            "seller_id"  => $alipay_config['seller_id'],
            "payment_type"	=> $alipay_config['payment_type'],
            "notify_url"	=> $alipay_config['notify_url'],
            "return_url"	=> $alipay_config['return_url'],

            "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
            "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "body"	=> $body,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
            //如"参数名"=>"参数值"

        );
//建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;

    }
/*服务器异步通知页面路径 */
    public function pay_responsed(){
        require_once("./alipay/alipay.config.php");
        require_once("./alipay/lib/alipay_notify.class.php");

//计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {//验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号
            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];

            if($_POST['trade_status'] == 'TRADE_FINISHED') {

            }else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $status=substr($out_trade_no,0,3);
                if($status=='cgw'){
                    $incomeordermodel=M('IncomeOrder');
                    $row=$incomeordermodel->where(array('sn'=>$out_trade_no))->save(array('status'=>1));

                }else{

                //进行自己的数据库的状态的修改
                /*$data = array();
                $data['pay_status'] = 1;
                $data['pay_time'] = time();
                M('Goods_orders')->where('orders_num='.$out_trade_no)->save($data);*/
                $orders_num =$out_trade_no;
                $info_a = strpos($orders_num,'u');
                if($info_a==1){
                    $data = array();
                    $data['pay_status'] =1;
                    $data['pay_time'] = time();
                    $user_huifei = M('User_huifei');
                    $user_huifei->where(array('orders_num'=>$orders_num))->save($data);
                    $user_huifei->where(array('orders_num'=>$orders_num))->setInc('use_times',1);
                    $res = $user_huifei->field('user_id,hf_money,use_times')->where(array(
                        'orders_num'=>$orders_num
                    ))->find();
                    $user_id = $res['user_id'];
                    $huifei_sum = $res['hf_money'];
                    /************/
                    $admin=M('admin','vip_');
                    $user=M('user');
                    $my=$user->where(array('id'=>$user_id))->find();
                    $arr['account']=$my['mobile'];
                    $arr['password']=$my['password'];
                    $arr['create_time']=NOW_TIME;
                    $arr['status']=1;
                    $admin_id=$admin->add($arr);
                    $auth_group_access=M('auth_group_access','vip_');
                    $auth_group_access->add(array('uid'=>$admin_id,'group_id'=>51));
                    $user->where(array('id'=>$user_id))->save(array('admin_id'=>$admin_id));
                    $map = array();
                    $year=date("Y",NOW_TIME);
                    $month=date("m",NOW_TIME);
                    if($res['use_times']==1){
                        if($huifei_sum==365){
                            $map['grade_name'] ='会员';
//                             R('Award/vipLogic',array($user_id,$year,$month));//取消分成
                            $map['vip_end'] =NOW_TIME-0+31536000;
                        }else if($huifei_sum==30000){
//                             R('Award/sVipLogic',array($user_id,array($year,$month)));
                            $map['grade_name'] ='合伙人';
                        }
                        $map['admin_id'] =$admin_id;
                        $map['status'] =1;
                        $m = M('member','vip_');
                        $m->where(array('user_id'=>$user_id))->save($map);
                    }
                    $this->send_sms($my['mobile']);

                    /*************/
                }else{
                    $data = array();
                    $data['pay_status'] = 1;
                    $data['pay_time'] = time();
                    M('Goods_orders')->where(array(
                        'orders_num'=>$orders_num
                    ))->save($data);
                }

                }


            }



            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "success";		//请不要修改或删除
        }
        else {
            //验证失败
            echo "fail";
        }
    }
       

    /*
     *页面跳转同步通知页面路径
     * 支付宝支付成功后跳到那个页面
     */
    public function pay_success_to(){
		 if($this->isMobile()==false){
           $this->display();
        }else{
			  $this->redirect('Mobile/Index/index');
		}
        
    }
//>>>判断是电脑还是手机
    function isMobile() {
        $mobile = array();
        static $mobilebrowser_list ='Mobile|iPhone|Android|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|SonyEricsson|Sony|BlackBerry|Cellphone|dopod|Nokia|samsung|PalmSource|Xphone|Xda|Smartphone|PIEPlus|MEIZU|MIDP|CLDC';
    if(preg_match("/$mobilebrowser_list/i", $_SERVER['HTTP_USER_AGENT'], $mobile)) {
        return true;
    }else{
        if(preg_match('/(mozilla|chrome|safari|opera|m3gate|winwap|openwave)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }else{
            if($_GET['mobile'] === 'yes') {
                return true;
            }else{
                return false;
            }
        }
    }
}

    
	
	
	//收藏商品
	public function shoucang_add(){
		$m = M('goods_shoucang');
		$where['goods_id'] = $_POST['goods_id'];
		$where['user_id'] = $_SESSION['user_id'];
		$result = $m->where($where)->find();
		if(!empty($result)){
			$this->ajaxReturn(false);
		}
		$_POST['user_id'] = $_SESSION['user_id'];
		$_POST['create_time'] = time();
		$res = $m->add($_POST);
		if($res){
			$this->ajaxReturn(true);
		}else{
			$this->ajaxReturn(false);
		}
	}

	
	//结算
	public function goods_jiesuan(){
		if(empty($_COOKIE['user_id'])){
			$this->redirect('User/user_login');
		}
		include 'area_code.php';
		$goods = M('goods');
		//直接购买
		if($_POST['now_buy'] == 1){
			$price_sum = $_POST['goods_num'] * $_POST['price_market'];
			$goods_data[] = $_POST;			
		}else{
			//购物车中购买，过滤提交的数据
			foreach ($_POST['hidden_xuanze'] as $k=>$v){
				if($v == 1){	//过滤选中的商品ID
					$goods_data[$k]['goods_id'] = $_POST['goods_id'][$k];	//商品ID
					$goods_data[$k]['goods_num'] = $_POST['goods_num'][$k];	//商品数量
					$goods_data[$k]['pic_url'] = $_POST['pic_url'][$k];	//商品图片
					$goods_data[$k]['goods_title'] = $_POST['goods_title'][$k];	//商品图片
					$goods_data[$k]['price_market'] = $_POST['price_market'];  	//商品的单价
					$goods_result = $goods->field('price_market')->where('id='.$_POST['goods_id'][$k])->find();
					$goods_data[$k]['price_market'] = $goods_result['price_market'];	//商品图片
					$goods_data[$k]['price_xiaoji'] = $goods_result['price_market'] * $_POST['goods_num'][$k];	//商品图片
					$price_arr[] = $goods_data[$k]['price_xiaoji'];
				}
			}
			$price_sum = array_sum($price_arr);		//计算价格之和			
		}

		if(!empty($goods_data)){
			$_SESSION['price_sum'] = $price_sum;
			$_SESSION['goods_data'] = $goods_data;	//选中的商品数据临时存储在session中
		}
		$this->assign('price_sum',$_SESSION['price_sum']);	//合计金额
		$this->assign('goods_data',$_SESSION['goods_data']);	//选择的商品
		
		
		//获取默认地址
		$user_address = M('user_address');
		$where['user_id'] = $_COOKIE['user_id'];
		if(empty($_GET['address_id'])){
			$where['status'] = 1;				
		}else{
			$where['id'] = $_GET['address_id'];
		}
		$result_address = $user_address->where($where)->find();
		$result_address['province'] = $arrMArea[$result_address['province']];
		$result_address['city'] = $arrMArea[$result_address['city']];
		$result_address['area'] = $arrMArea[$result_address['area']];
		$this->assign('result_address',$result_address);
		
		
		//优惠券
		$coupons = M('user_coupons');	    	
    	if(!empty($_GET['coupons_id'])){
    		//重新选择优惠券
    		$where_choose['id'] = $_GET['coupons_id'];
    		$result_coupons = $coupons->where($where_choose)->find();
    	}else{
			//查询用户的优惠券			
			$where_coupons['mobile'] = $_COOKIE['mobile'];	//用户手机号
			$where_coupons['use_status'] = array('eq',0);		//查找未使用的
			
			$where_coupons['begin_date'] = array('lt',date('Y-m-d',time()));	//开始时间小于当前时间
			$where_coupons['end_date'] = array('gt',date('Y-m-d',time()));	//结束时间大于当前时间
			$result_coupons = $coupons->where($where_coupons)->order('end_date ASC')->find();		//按照优先使用快过期的	
		}
		//优惠券限制金额，面额都小于总金额才可以使用
		if($result_coupons['money_youhui'] < $_SESSION['price_sum'] && $result_coupons['money_xianzhi'] < $_SESSION['price_sum']){
			$this->assign('result_coupons',$result_coupons);
		}else{
			$result_coupons['id'] = '';
			$result_coupons['money_youhui'] = 0;
		}
		$price_shiji = $_SESSION['price_sum'] - $result_coupons['money_youhui'];	//实际金额   = 总计金额 - 优惠券金额
		$this->assign('price_shiji',$price_shiji);	//实际金额
		
		$this->display();
	}
}