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

use Common\Model\BaseModel;
use Common\Tool\Tool;
use Home\Model\BrandModel;
use Home\Model\GoodsModel;
use Home\Model\OrderModel;
use Home\Model\GoodsImagesModel;
use Think\Page;
use Home\Model\PayTypeModel;
use Common\Model\ExpressModel;
use Common\TraitClass\FreightTrait;


/**
 * 积分商城
 * space_id = 10 banner
 * space_id = 12 中部广告
 */
class IntegralMallController extends BaseController
{
    use FreightTrait;
    private $total_price;//需要支付的总金额

    public function _initialize()
    {
        parent::_initialize();
        $this->intnetTitle .= ' - 积分商城';
    }


    /**
     * 显示积分商城首页
     */
    public function index()
    {
        $user_id = $_SESSION['user_id'];
        if ($user_id > 0) {
            $info                = D('user')->getUserByUserId($user_id);
            $info['integral']    = D('user')->getIntegral($user_id);
            $header              = M('userHeader')->field('user_header')->where(['user_id'=>$user_id])->find();
            $info['user_header'] = $header['user_header'];
            $this->assign('user', $info);
        }

        // 积分商城banner
        $ads = $this->getAdBySpace(10);

        // 积分商城中广告
        $adm = $this->getAdBySpace(12);

        // 热门商品
        $hot = $this->hotGoods();

        // 我能兑换
        $can = $this->canGoods();

        // 热门分类
        $class = $this->hotClass();
        $this->assign('ads', $ads);
        $this->assign('adm', $adm[0]);
        $this->assign('hot', $hot);
        $this->assign('class', $class);
        $this->assign('can', $can);
        $this->display();
    }


    /**
     * 积分商品列表
     * 1.分类查询
     * 2.积分查询
     * 3.排序
     */
    public function goods()
    {

        // 分类id
        $class_id = I('class', -1, 'intval');

        // 是否可兑换
        $exchange = I('exchange', -1, 'intval');

        // 积分最小
        $min   = I('min', -1, 'intval');

        // 积分最大
        $max   = I('max', -1, 'intval');

        // 当前页数
        $page  = I('page', -1, 'intval');

        // 1:默认排序 2:积分值 3:上架时间
        $order_type = I('order', -1, 'intval');

        // 兑换
        if ($exchange != -1 && $exchange != 0) {
            $user_id = $_SESSION['user_id'];
            if (empty($user_id)) {
                $this->redirect('public/login');
            }
            $info = M('user')->field('integral')->find($user_id);
            $max  = $info['integral'];
        }

        // 在查积分区间商品
        if ($min != -1 && $max != -1) {
            $where['integral'] = ['between', [$min, $max]];
        } elseif ($min != -1) {
            $where['integral'] = ['gt', $min];
        } elseif ($max != -1) {
            $where['integral'] = ['lt', $max];
        }
        if ($class_id != -1) {
            $where['class_id'] = $class_id;
        }

        if ($page < 1) {
            $page = 1;
        }
        $page_size = PAGE_SIZE;

        switch ($order_type) {
            case 1:
                $order = 'g.sort DESC';
                break;
            case 2:
                $order = 'i.integral DESC';
                break;
            case 3:
                $order = 'g.update_time DESC';
                break;
            
            default:
                $order_type = 1;
                $order      = 'g.sort DESC';
                break;
        }

        // 查询顶级分类
        $class_model = M('goodsClass');
        $field  = 'id,class_name,pic_url,fid,description,hide_status';
        $parent = $class_model->field($field)->where(['hide_status'=>1, 'fid'=>0])->select();
        $parent = is_array($parent) ? $parent : [];

        // 查询二/三级分类
        foreach ($parent as &$value) {
            $second =  $class_model->field($field)->where(['hide_status'=>1, 'fid'=>$value['id']])->select();
            $second = is_array($second) ? $second : [];
            foreach ($second as &$vo) {
                $three = $class_model->field($field)->where(['hide_status'=>1, 'fid'=>$vo['id']])->select();
                $three = is_array($three) ? $three : [];
                $vo['child'] = $three;
            }
            $value['child'] = $second;
        }

        // 获取该分类的顶级分类
        if ($class_id != -1) {
            $fid = $class_id;
            while (true) {
                $class = $class_model->field($field)->find($fid);
                $fid   = $class['fid'];
                if (empty($class) || $fid == 0) {
                    break;
                }
            }
        }


        $model = M('integralGoods');
        $where['i.status'] = 1;
        $count = $model->alias('i')->join('db_goods as g ON i.goods_id=g.id')->where($where)->count();
        $goods = $model->alias('i')->join('db_goods as g ON i.goods_id=g.id')->field('i.goods_id, g.title, i.integral')
            ->where($where)->limit(($page-1).','.$page_size)->order($order)->select();

        if (!empty($goods) && is_array($goods)) {
            $model = D('goods');
            foreach ($goods as &$value) {
                $value['pic_url']  = $model->image($value['goods_id']);
            }
        }
        if (!is_array($goods)) {
            $goods = [];
        }

        $this->assign('class_list', $parent);
        $this->assign('class', $class);
        $this->assign('class_id', $class_id);
        $this->assign('page', $page);
        $this->assign('total', ceil($count/$page_size));
        $this->assign('count', $count);
        $this->assign('order', $order_type);
        $this->assign('goods', $goods);
        $this->assign('exchange', $exchange);
        $this->display();
    }


    /**
     * 积分商品, 积分商品没有购物车的概念
     * 只能立即兑换,下次进入购物车移除老的历史.所以购物车永远只有一个积分商品
     */
    public function cart()
    {
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            $this->redirect('Public/login');
        }

        $goods_id = I('goods_id', -1, 'intval');
        if (!is_numeric($goods_id) || $goods_id < 1) {
            $this->ajaxReturn('参数错误');
        }

        // 移除积分商品列表
        $sql = 'delete c from db_goods_cart as c, db_integral_goods as g where '
            .'g.goods_id=c.goods_id and c.user_id='.$user_id;

        $ret = M()->execute($sql);

        // 添加到购物车
        $info = M('goods')->field('status')->find($goods_id);
        if ($info['status'] == 3) {
            M('goodsCart')->add(['user_id'=>$user_id, 'goods_id'=>$goods_id, 'goods_num'=>1,'create_time'=>time()]);
        }

        // 购物车列表
        $field             = 'c.id as cart_id, c.goods_id, c.goods_num, g.title, g.description';
        $where             = 'c.user_id='.$user_id.' AND g.status=3 AND c.is_del=0';
        $goods             = M('goodsCart')->alias('c')->join('db_goods as g ON g.id=c.goods_id')->field($field)->where($where)->find();
        $goods['pic_url']  = D('goods')->image($goods['goods_id']);
        $info              = M('integralGoods')->field('integral,delayed,money')->where(['goods_id'=>$goods['goods_id']])->find();

        $goods['integral'] = $info['integral'];
        $goods['delayed']  = $info['delayed'];
        $goods['money']  = $info['money'];


        // 获取用户在时间限制下有效积分
        $integral = D('IntegralUse')->valid($user_id, $goods['delayed']);

        $this->assign('list', [$goods]);
        $this->assign('integral', $integral);
        $this->assign('count', 1);
        $this->display();
    }


    /**
     * 修改购物车数量
     */
    public function update()
    {
        $cart_id = I('POST.cart_id', -1, 'intval');
        $number  = I('POST.goods_num', -1, 'intval');
        if ($cart_id == -1 || $number < 1) {
            $this->ajaxReturn('参数错误');
        }
        if ($_SESSION['user_id'] < 1) {
            $this->ajaxReturn('用户未登陆');
        }
        $ret  = M('goodsCart')->save(['goods_num'=>$number, 'id'=>$cart_id]);
        $this->ajaxReturnData($ret, intval($ret));
    }


    /**
     * 删除购物车中的积分商品
     */
    public function del()
    {
        $cart_id = I('cart_id', -1);
        if ($cart_id == -1) {
            $this->ajaxReturn('参数错误');
        }
        if ($_SESSION['user_id'] < 1) {
            $this->ajaxReturn('用户未登陆');
        }

        if (strpos($cart_id, ',') !== false) {
            $ret = M('goodsCart')->where(['id' => ['in', $cart_id]])->delete();
        } else {
            $ret = M('goodsCart')->where(['id'=>$cart_id])->delete();
        }
        if (IS_AJAX) {
            $this->ajaxReturn(intval($ret>0));
        } 
        $this->redirect('index');
    }


    /**
     * 移动到收藏夹
     */
    public function move()
    {
        $goods_id = I('goods_id', -1);
        if ($goods_id == -1) {
            $this->ajaxReturn('参数错误');
        }
        $user_id = $_SESSION['user_id'];
        if ($user_id < 1) {
            $this->ajaxReturn('用户未登陆');
        }

        // 包装成数组
        if (strpos($goods_id, ',') !== false) {
            $ids = explode(',', $goods_id);
        } else {
            $ids = [$goods_id];
        }

        // 遍历数组
        M()->startTrans();
        $goods      = M('goods');
        $collection = M('collection');
        $time       = time();
        foreach ($ids as $gid) {
            $info = $collection->field('id as collect_id')->where(['goods_id'=>$gid, 'user_id'=>$user_id])->find();
            if (is_array($info) === false) {
                $goods = $goods->field('title,class_id')->where('id='.$gid)->find();
                $data = [
                    'goods_id'   => $gid,
                    'user_id'    => $user_id,
                    'add_time'   => $time,
                    'goods_name' => $goods['title'],
                    'class_id'   => $goods['class_id']
                ];
                $ret = $collection->add($data);

            } else {
                $ret = $info['collect_id'];
            }
            if ($ret < 1) {
                M()->rollback();
                break;
            }
        }

        // 移除购物车中的商品
        $where['user_id'] = $user_id;
        if (strpos($goods_id, ',') !== false) {
            $where['goods_id'] = ['in', $goods_id];
        } else {
            $where['goods_id'] = $goods_id;
        }
        $ret = M('goodsCart')->where($where)->delete();
        M()->commit();
        if (IS_AJAX) {
            $this->ajaxReturn(intval($ret>0));
        }

        $this->redirect('cart');
    }


    /**
     * 积分商品结算页面
     * 只有一个积分商品
     */
    public function confirm()
    {
        C('title', '结算');
        $user_id = $_SESSION['user_id'];

        // 获取购物车ID list
        $id_str   = I('POST.cart_id', -1);
        if ($id_str == -1 || empty(trim($id_str))) {
            $this->ajaxReturn('参数错误');
        }

        // 获取收货人信息
        $addr_list    = D('userAddress')->getAddrByUser($user_id, false);
        $addr_default = $addr_list[0];

        // 物流确认订单
        //$carry    = M('carry')->field('id,carry_title,carry_param')->select();
        $Express = new ExpressModel();
        $carry = $Express->getDefaultOpen(false);
        // 商品信息
        $total      = 0;
        $model      = D('goods');
        $integral   = M('integralGoods');
        $goods_list = D('goodsCart')->getCartGoodsById($id_str);

        $goods = array_pop($goods_list['common']);
        $goods['pic_url']  = $model->image($goods['goods_id']);
        $goods['spec']     = $model->spec($goods['goods_id']);
        $info              = $integral->field('integral,delayed,money')->where('goods_id='.$goods['goods_id'])->find();

        $goods['integral'] = $info['integral'];
        $total            += $goods['goods_num'] * $info['integral'];

        $goods_info['count'] = 1;
        $goods_info['total'] = $total;
        $goods_info['total_money'] = $goods['goods_num'] * $info['money'];

        // TODO:邮费
        $goods_carry = D('goodsCart')->postage($id_str);

        // 获取有效积分
        $integral = D('IntegralUse')->valid($user_id, $info['delayed']);
        if ($total > $integral) {
            $this->ajaxReturn('积分不够, 不能进行兑换');
        }

        // 应付金额
        $this->total_price =  $goods_info['total_money'] + $goods_carry;

        $this->assign('addr_list', $addr_list);
        $this->assign('payModel', PayTypeModel::class);
        $this->assign('addr_default', $addr_default);
        $this->assign('carry', $carry);
        $this->assign('goods_list', [$goods]);
        $this->assign('goods_info', $goods_info);
        $this->assign('goods_carry', $goods_carry);
        $this->assign('integral', $integral);
        $this->assign('pay_nubmer', $this->total_price);
        $this->display();
    }


    /**
     * 生成订单
     * 需要支付的话调到支付页面
     */
    public function pay()
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
        $carry_type = I('POST.carry_type', -1);
        if ($address_id == -1 || empty($address_id)) {
            $this->ajaxReturn('运输方式不能为空');
        }

        // 购物车id
        $cart_id = I('POST.cart_id', -1);
        if ($cart_id == -1 || empty($cart_id)) {
            $this->ajaxReturn('商品不能为空');
        }
        
        // 获取积分商品
        $total      = 0;
        $model      = D('goods');
        $goods_list = D('goodsCart')->getCartGoodsById($cart_id);
        $goods = array_pop($goods_list['common']);
        $goods['pic_url']  = $model->image($goods['goods_id']);
        $goods['spec']     = $model->spec($goods['goods_id']);
        $info              = M('integralGoods')->field('integral,delayed,money')->where('goods_id='.$goods['goods_id'])->find();
        $goods['integral'] = $info['integral'];
        $need             += $goods['goods_num'] * $info['integral'];

        // 获取用户有效积分
        $integral = D('IntegralUse')->valid($user_id, $info['delayed']);
        if ($need > $integral) {
            $this->ajaxReturn('积分不够');
        }
        // 支付的费用主要是邮费,如果免邮的话就不需要支付
        $total = $_SESSION['FreightMoney'];
        $total = $total + $info['money'] * $goods['goods_num'];
        // 买家留言
        $message = I('POST.message');
        $data  = [
            'price_sum'      => $total, // 总价
            'address_id'     => $address_id, // 收货地址
            'user_id'        => $user_id, // 用户
            'create_time'    => time(),
            'order_status'   => 0,      // 未支付
            'comment_status' => 0,      // 未评论
            'pay_type'       => 0,      // 支付类型
            'remarks'        => $message, // 订单备注
            'status'         => 0,      // 订单正常
            'translate'      => 0,      // 0,不需要发票   1,需要发票
        ];


        $trans = M();
        $trans->startTrans();
        $model = BaseModel::getInstance(OrderModel::class);
        $retID = $model->add($data);
        if ($retID < 1) {
            $trans->rollback();
            $this->ajaxReturn('订单失败');
        }

        // 添加商品到 db_order_goods
        $data = [
            'order_id'    => $retID,
            'goods_id'    => $goods['goods_id'],
            'goods_num'   => $goods['goods_num'],
            'goods_price' => $info['money'],
            'status'      => 0,
            'user_id'     => $user_id,
            'ware_id'     => $goods['ware_id']
        ];

        $ret = M('orderGoods')->add($data);
        if ($ret < 1) {
            $trans->rollback();
            $this->ajaxReturn('订单失败');
        }

        // 移除库存相应的数量
        $goods_info = M('goods')->field('stock')->find($goods['goods_id']);
        $goods_num  = $goods_info['stock'] - $goods['goods_num'];
        if ($goods < 0) {
            $trans->rollback();
            $this->ajaxReturn('库存不足');
        }
        $ret = M('goods')->save(['id' => $goods['goods_id'], 'stock' => $goods_num]);
        if ($ret < 1) {
            $trans->rollback();
            $this->ajaxReturn('订单失败');
        }
        $ret = M('specGoodsPrice')->where(['goods_id' => $goods['goods_id']])->save(['store_count'=>$goods_num]);
        if ($ret < 1) {
            $trans->rollback();
            $this->ajaxReturn('订单失败');
        }

        // 减去处理积分
        $ret = D('integralUse')->used($user_id, $need, $info['delayed'], ['goods_id'=>$goods['goods_id'], 'remarks'=>'积分商品']);

        $trans->commit();
        //总费用等于0,则不需要支付
        if($total != 0){
           //生成数组方便增加积分
            $_SESSION['integral_data'] = [
                'user_id' => $_SESSION['user_id'],
                'integral' => $this->add_integral($total),
                'goods_id'  =>$goods['goods_id'],
                'trading_time' => time(),
                'remarks' => '商品购买',
                'type'  => 1
            ];

            $this->redirect('/Home/payOrder/payOrder', ['order_id'=>$retID]);
        }else{
            $this->success('兑换成功,礼品请注意查收',U('Home/Order/order_myorder'));
        }
    }


    /**
     * 兑换须知
     */
    public function aubot()
    {
        $this->display();
    }


    /**
     * 根据广告位置获取广告
     * @param  integer $space_i 根据广告位id获取banner(广告)
     * @param  integer $limit   最多多少张
     * @param  integer $platform默认是PC端
     * @param  integer $enabled 是否启用:1.启用 0.不启用 2.全部
     * @param  boolean $valid   是否有效
     * @return array
     */
    private function getAdBySpace($space_id, $limit = '5', $platform = 1, $enabled = 1, $valid = true)
    {
        if (!is_numeric($space_id)) {
            return [];
        }
        $field = 'id,title,ad_link,pic_url,ad_space_id,platform,color_val,type,enabled,start_time,end_time';
        $where['ad_space_id'] = $space_id;
        $where['platform']    = $platform;
        if ($enabled != 2) {
            $where['enabled'] = $enabled;
        }
        if ($valid) {
            $where['start_time'] = ['lt' ,time()];
            $where['end_time']   = ['gt' ,time()];
        }

        $data = M('ad')->field($field)->where($where)->limit($limit)->order('sort_num DESC')->select();
        return $data;
    }


    /**
     * 热销商品
     * 从订单列表获取最热的商品列表,且是积分商品
     * 如果订单列表中没有积分商品,就从积分商品中取
     */
    public function hotGoods()
    {
        // 获取最近一个月的积分商品订单,前15单
        $sql = 'select g.goods_id, sum(g.goods_num) as sort from db_order as o,db_order_goods as g,db_integral_goods as i '
            .'where o.id=g.order_id AND i.goods_id=g.goods_id AND o.create_time > '
            .strtotime('-1 month').' Group by g.goods_id order by sort DESC LIMIT 15';
        $data = M()->query($sql);

        if (is_array($data)) {
            $ids = '';
            foreach ($data as $goods) {
                $ids .= ','.$goods['goods_id'];
            }
            $ids = substr($ids, 1);
        }
        if (!empty($ids)) {

            // 获取商品
            $where = 'select goods_id,title,integral,i.money from db_goods as g,db_integral_goods as i '
                .'where g.id=i.goods_id AND g.status=3 AND i.status=1';
            $goods = M()->query($where." and goods_id in ($ids)");

            // 数量不够15,添加积分表的积分商品
            $need = 15 - count($goods);
            if ($need > 0) {
                $where .= " and goods_id not in ($ids) LIMIT $need ";
                $temp   = M()->query($where);
                $goods  = array_merge($temp, $goods);
            }
            $model = D('goods');
            foreach ($goods as &$value) {
                $value['pic_url'] = $model->image($value['goods_id']);
            }
        }

        if (!is_array($goods)) {
            $goods = [];
        }
        return $goods;
    }


    /**
     * 获取热卖分类列表
     * 获取最近添加的商品
     */
    public function hotClass()
    {
        $sql = 'select o.goods_id, sum(goods_num) as sort from db_order_goods as o,db_goods as g '
            . ' where o.goods_id=g.id and g.status=3 group by o.goods_id order by sort DESC';
        $data = M()->query($sql);
        if (is_array($data)) {
            $ids = '';
            foreach ($data as $value) {
                $ids .= $value['goods_id'].',';
            }
            $ids = rtrim($ids, ',');
        }

        if (!empty($ids)) {
            $sql = 'select c.id, c.class_name, count(1) as sort  from db_goods_class as c,db_goods as g '
                .'where g.class_id=c.id and g.id in ('.$ids.') group by c.id order by sort DESC';
            $list = M()->query($sql);
        }

        if (!is_array($list)) {
            $list = [];
        }

        return $list;
    }


    /**
     * 我能兑换
     * 根据分类获取所有的商品
     */
    public function canGoods()
    {
        $class = I('GET.class', 0);
        if (!empty($class)) {
            $where['g.class_id'] = $class;
        }
        $where['g.status'] = 3;
        $where['g.p_id']   = ['neq', 0];
        $where['i.status'] = 1;
        $field = 'i.goods_id,g.title,i.integral,i.money';
        $list  = M('goods')->alias('g')->join('__INTEGRAL_GOODS__ as i ON g.id=i.goods_id')->field($field)->where($where)->select();

        // 积分商品信息
        $model = D('goods');
        foreach ($list as &$goods) {
            $goods['pic_url'] = $model->image($goods['goods_id']);
        }

        $list = is_array($list) ? $list : [];

        if (IS_AJAX) {
            $this->ajaxReturn($list);
        }
        return array_values($list);
    }

    //支付完成之后添加积分
    public function add_integral($price_sum)
    {
        //查询当前积分比例
        $this->key = 'integral';
        $pay_integral = $this->getGroupConfig()['pay_integral'];
        return  $price_sum * $pay_integral;

    }

}