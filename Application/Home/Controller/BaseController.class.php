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

use Home\Model\GoodsModel;
use Think\Controller;
use Common\Tool\Tool;
use Common\TraitClass\NoticeTrait;
use Common\TraitClass\InternetTopTrait;
use Common\TraitClass\WhoIsShe;
use Common\TraitClass\SmsVerification;

/**
 * 前台控制器基类
 * 
 * @author shopsn团队
 */
class BaseController extends Controller
{
    use WhoIsShe;
    use NoticeTrait;
    use InternetTopTrait;
    use SmsVerification;

    public function _initialize()
    {
        // 网站是否维护
        $isOpen = $this->getConfig('is_open');
        if ($isOpen == 1) {
            echo file_get_contents('./ErrorFiles/index.html');
            die();
        }
        
        // 公用数据（带测试查不多，在打开缓存后，在删除下面的测试公用数据）
        // 商品分类
        if (! $goods_categories = S('goods_categories')) {
            $goods_class_model = D("GoodsClass");
            
            $goods_categories = $goods_class_model->getList('id,class_name,fid,css_class');
            S('goods_categories', $goods_categories, 5);
        }
        
        // 底部文章分类
        $article_lists = $this->arctile();
        
        $str = $this->getFamily();
        
        $navigatData = S('navigatData');
        
        if (! $navigatData) {
            $navigatData = M("Nav")->field("id,nav_titile,link,type")
                ->where([
                'status' => 1
            ])
                ->order('sort')
                ->limit(11)
                ->select();
            S('navigatData', $navigatData, 15);
        }
        
        // 购物车数量
        $cartCount = 0;
        
        // 未使用代金券数量
        $UsableCoupon = 0;
        
        // 已使用代金券数量
        $UsedCoupon = 0;
        
        // 已过期代金券
        $OverdueCoupon = 0;
        
        $carts = [];
        // 判断是否有session值
        if (! empty($_SESSION['user_id'])) {
            
            $this->assign('userId', [
                'user_name' => $_SESSION['user_name']
            ]);
            $where = [];
            // 查询购物车条数以及相关信息
            $where['a.user_id'] = $_SESSION['user_id'];
            $where['a.is_del'] = 0;
            $where['b.status'] = array(
                'lt',
                3
            );
            $cartCount = M('goods_cart as a')->field('a.id,a.goods_num,a.price_new,b.title,a.goods_id,a.buy_type,b.p_id')
                ->join('db_goods as b ON a.goods_id=b.id')
                ->where($where)
                ->count();
            
            // dump($cartCount);exit;
            $carts = M('goods_cart as a')->field('a.id,a.goods_num,a.price_new,b.title,a.goods_id,a.buy_type,b.p_id')
                ->join('db_goods as b ON a.goods_id=b.id')
                ->where($where)
                ->order('a.buy_type ASC')
                ->limit(5)
                ->select();
            $a = GoodsModel::getInitnation();
            $carts = $a->goods_image($carts);
            // dump($carts);exit;
            // 查询可用优惠券数量
            $Usable['user_id'] = $_SESSION['user_id'];
            $Usable['use_time'] = '';
            $Usable['use_end_time'] = array(
                'GT',
                time()
            );
            $UsableCoupon = M('coupon_list as a')->join('left join db_coupon as b on a.c_id=b.id')
                ->where($Usable)
                ->count();
            // 查询已用优惠券数量
            $Used['user_id'] = $_SESSION['user_id'];
            $Used['use_time'] = array(
                'neq',
                0
            );
            $UsedCoupon = M('coupon_list')->where($Used)->count();
            // 查询已过期优惠券数量
            $Over['user_id'] = $_SESSION['user_id'];
            $Over['use_time'] = '';
            $Over['use_end_time'] = array(
                'LT',
                time()
            );
            $OverdueCoupon = M('coupon_list as a')->join('left join db_coupon as b on a.c_id=b.id')
                ->where($Over)
                ->count();
            
            $member_status = $_SESSION['member_status'];
            $this->assign('member_status', $member_status);
            $mes['user_id'] = $_SESSION['user_id'];
            $mes['status'] = 0;
            $this->mes_count = M('order_logistics_message')->where($mes)->count();
        }
        
        // 代金券总数
        $z_count = $UsedCoupon + $OverdueCoupon + $UsableCoupon;
        
        $nav_data = S('nav_data');
        
        if (! $nav_data) { // 导航菜单
            $nav_data = M("Nav")->field("id,nav_titile,link,type")
                ->where([
                'status' => 1
            ])
                ->order('sort')
                ->limit(11)
                ->select();
            ;
            S('nav_data', $nav_data, 30);
        }
        
        // 判断是否需要展示商品分类,首页展示,其它页面折叠
        if (CONTROLLER_NAME == 'Index') {
            $show_categroy = true;
        } else {
            $show_categroy = false;
        }
        // 获取组配置
        
        $information = $this->getIntnetInformation();
        // 底部公司名称
        $company_name = $this->get_intnetConfig()['company_name'];
        
        $this->assign("goods_categories", $goods_categories);
        
        $this->assign('z_count', $z_count);
        
        $this->assign('OverdueCoupon', $OverdueCoupon);
        
        $urls = "/index.php/Home/" . CONTROLLER_NAME . "/" . ACTION_NAME;
        
        $this->assign('UsableCoupon', $UsableCoupon);
        
        $this->assign('UsedCoupon', $UsedCoupon);
        
        $this->assign('nowurl', $urls);
        
        $this->assign('carts', $carts);
        
        $this->assign('show_category', $show_categroy);
        
        $this->assign('areaLocation', $this->getLocationArea());
        
        $this->assign("navs", $navigatData);
        
        $this->assign("article_lists", $article_lists);
        
        $this->assign($information); // 网站设置
        
        $this->assign('company_name', $company_name);
        
        $this->assign('str', $str);
        
        $this->assign('hot_words', self::keyWord());
        
        $this->assign('navs', $nav_data);
        
        $this->assign('cartCount', $cartCount);
        
        $this->assign('cart_goods', S('cart_goods'));
        
        $this->assign('intnetTitle', $information['intnet_title']);
    }

    protected function alreadyInData($data, $message = '更新成功')
    {
        if (! empty($data)) {
            $this->error($message);
        }
        return true;
    }

    /**
     * 导航标题
     */
    public function getNavTitle()
    {
        $title = S('navigatData');
        Tool::isSetDefaultValue($_GET, [
            'id' => 0
        ]);
        
        $thisTitle = null;
        
        if (! empty($title)) {
            foreach ($title as $key => $value) {
                if (empty($value['id']) || $_GET['id'] !== $value['id']) {
                    continue;
                }
                $thisTitle = $value['nav_titile'];
            }
        }
        $this->intnetTitle = $this->intnetTitle . ' - ' . $thisTitle;
    }

/**
 * 导航banner图
 */
    /*
     * public function navBanner()
     * {
     * $navBanners = M("Ad")->field("id,ad_link,pic_url")
     * ->where([
     * 'ad_space_id' => 6
     * ])
     * ->order('id,create_time desc')
     * ->limit(4)
     * ->select();
     * if (! empty($navBanners)) {
     * $this->ajaxReturn($navBanners);
     * } else {
     * $this->ajaxReturn([
     * 'msg' => "error"
     * ]);
     * }
     * }
     */
}