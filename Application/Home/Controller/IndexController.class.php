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

use Home\Model\AdModel;
use Home\Model\GoodsClassModel;
use Home\Model\GoodsModel;
use Common\Model\BaseModel;
use Home\Model\AnnouncementModel;
use Common\Tool\Tool;
use Think\Hook;

// 前台模块
class IndexController extends BaseController
{
    private $classId = '';
    // 首页
    public function index()
    {
        $ad = AdModel::getInitnation();
        
        // 右边新品广告图
        $new = $ad->rightAdByIndex();
        
        $this->assign('newThrees', $new);
        
        // 每日推荐十二个广告图
        $shier = $ad->recommendByEveryDay();
        $count = count($shier);
        $rs = $this->split_array($shier, 4, $count);
        
        Tool::isSetDefaultValue($rs, [
            '0' => null,
            '1' => null,
            '2' => null
        ]);
        
        $this->assign('tuijian', $rs[0]);
        $this->assign('restric', $rs[1]);
        $this->assign('newTop', $rs[2]);
        
        /* --------------------------------肥胖的分割线------------------------------- */
        
        // 亿速公告
        
        $announcementModel = new AnnouncementModel();
        
        $announcements = $announcementModel->getAnnouncement();
        
        // 商品推荐模块----办公文具
        $goodsModel = M('goods'); // 实例商品对象
        $goodsImage = BaseModel::getInstance(GoodsModel::class);
        // 遍历fid==0的数据
        if (! $bigInfor = S('biginfor')) {
            $topId = BaseModel::getInstance(GoodsClassModel::class)->getRecommendParentClass();
            $i = 0;
            $goodsClass = GoodsClassModel::getInitnation();
            
            $goodsClass->setClassData($goodsClass->getClassData());
            
            foreach ($topId as $k => $v) {
                $topId[$k]['url'] = U('Product/ProductList', [
                    'cid' => $v['id']
                ]);
            }
            Hook::listen('check_money');

            //获取品牌
            $classId = '';
//             // dump($topId);exit;
            foreach ($topId as $k => $v) {
                // 属性名称
                $bigInfor[$k]['class_id'] = $v['id'];
                $bigInfor[$k]['url'] = $v['url'];
                $bigInfor[$k]['class_name'] = $v['class_name'];
                $bigInfor[$k]['class_pic_url'] = $v['pic_url'];
//                 // 左边商品品牌列表
                $bigInfor[$k]['brand'] = $ad->getIndexMiddlePicture($i);
                
                $classId = $goodsClass->getClassId($v['id']);
                
                // 查询左边的单个数据
                $leftLimiter['status'] = 2;
                $leftLimiter['pid'] = array(
                    'gt',
                    0
                );
               
                $classId = explode(',', $classId);
                array_unshift($classId, $v['id']);
                
                $this->classId = $classId;
                
                $leftLimiter['class_id'] = [
                    'in',
                    $classId
                ];
                $leftLimiter['shelves'] = 1;
                $goodsLeft = $goodsModel->order('id DESC')
                    ->where($leftLimiter)
                    ->limit(1)
                    ->select();
                $a = $goodsImage->goods_image($goodsLeft);
                $bigInfor[$k]['left'] = $a[0];
                
                // 下面广告
                $bigInfor[$k]['ad'] = $this->bottom_ad($i);
                // 右边的7个商品信息
                $f = $this->leftGoods(7);
               
                $f ? $bigInfor[$k]['one'] = $f[0] : '';
                unset($f[0]);
                $bigInfor[$k]['goods'] = $f;
                $i ++;
            }
            S('biginfor', $bigInfor, 20); // 缓存20秒
        }

        $this->assign('bigInfor', $bigInfor);
        /* -------------------性感的分割线-------------------- */
        $this->assign("announcements", $announcements);
        $this->assist();
        $this->product = S('product');
        $this->ads = S('ad_space');
        $this->display();
    }

    /**
     *
     * @param $array 数组            
     * @param $num 拆分个数            
     * @param $moung 数组元素个数            
     */
    private function split_array($array, $num, $moung)
    {
        $a = ceil($moung / $num);
        $fh = array();
        for ($i = 1; $i <= $a; $i ++) {
            foreach ($array as $k => $v) {
                if ($k < ($i * $num) && $k >= (($i - 1) * $num)) {
                    $fh[$i - 1][$k % $num] = $v;
                }
            }
        }
        return $fh;
    }

    private function bottom_ad($i)
    {
        $where['enabled'] = 1;
        $where['ad_space_id'] = 37;
        $nowtime = time();
        $where['start_time'] = array(
            'elt',
            $nowtime
        );
        $where['end_time'] = array(
            'egt',
            $nowtime
        );
        $rs = M('ad')->field('ad_link,pic_url,title')
            ->where($where)
            ->order('create_time desc')
            ->limit($i, 1)
            ->select();
        
        return $rs[0];
    }

    /**
     * 查询右边的商品信息
     */
    private function leftGoods($num = 7)
    {
        // 调用方法查询分类id
        $fids = $this->classId;
       
        if (!$fids) {
            return [];
        } 
        $where = [];
        $where['shelves'] = 1;
        $where['class_id'] = implode(',', $fids);
        $where['status'] = 0;
        $goodsParentInfo = BaseModel::getInstance(GoodsModel::class)->getGoodsByClassSon($where);
        // $goodsId =array();
        /*
         * $step =0;
         * do{
         * $goodsParentInfo =M('goods')->field('id')->order('sort DESC,id DESC')->where($where)->limit($step,1)->select();
         * $count=M('goods')->where(['p_id'=>$goodsParentInfo['id']])->count();
         * if($count){
         * array_unshift($goodsId,$goodsParentInfo['id']);
         * }
         * $step++;
         * }while(count($goodsId)<7);
         * dump($goodsId);exit;
         * $this->childGoods($goodsId);
         */
        $this->goods_image($goodsParentInfo);
        return $goodsParentInfo;
    }

    private function goods_image(&$goodsid)
    {
        foreach ($goodsid as $k => $v) {
            $goodsid[$k] = $v;
            $b = M('goods_images')->where([
                'goods_id' => $v['p_id'],
                'is_thumb' => 1
            ])
                ->limit(1)
                ->find();
            $goodsid[$k]['pic_url'] = $b['pic_url'];
        }
    }

    /**
     * 查询右边的子类商品信息
     */
    private function childGoods(&$goodsParent)
    {
        $goods = M('goods');
        
        foreach ($goodsParent as $k => $v) {
            $a = $goods->where([
                'p_id' => $v
            ])
                ->limit(1)
                ->select();
            $goodsParent[$k] = $a[0];
            $b = M('goods_images')->where([
                'goods_id' => $v
            ])
                ->limit(1)
                ->find();
            $goodsParent[$k]['pic_url'] = $b['pic_url'];
        }
    }

    /**
     * 商品模块最热爆款
     */
    private function newleast($class = 0, $num = 8)
    {
        $where['shelves'] = [
            'gt',
            0
        ];
        $where['stock'] = [
            'gt',
            0
        ];
        $where['p_id'] = [
            'gt',
            0
        ];
        // 根据最近一个月订单量查询
        $nowTime = time() - 60 * 60 * 24 * 30;
        $where['create_time'] = [
            'gt',
            $nowTime
        ];
        $orderId = M('order')->field('id')
            ->where($where)
            ->select();
        $orderIds = [];
        foreach ($orderId as $k => $v) {
            $orderIds[] = $v['id'];
        }
        
        if (! isset($orderIds)) {
            $adds['order_id'] = [
                'in',
                $orderIds
            ];
        }
        $goodsId = M('order_goods')->field('goods_id')
            ->where($adds)
            ->select();
        
        $goodsIds = [];
        $hh = [];
        foreach ($goodsId as $v) {
            $gg = M('goods')->field('p_id')
                ->where([
                'id' => $v['goods_id']
            ])
                ->find();
            if (! in_array($gg['p_id'], $hh)) {
                array_unshift($hh, $gg['p_id']);
                $goodsIds[] = $v['goods_id'];
            }
            if (count($hh) >= 9) {
                break;
            }
        }
        
        // 得到订单中商品购买出现次数
        $goodsNum = array_count_values($goodsIds);
        
        $rs = $this->numd($class, $goodsNum, $num);
        return $rs;
    }

    private function numd($class = 0, $goodsData = array(), $num = 8)
    {
        // 调用方法查询分类id
        $goodsClass = GoodsClassModel::getInitnation();
        $classData = $goodsClass->field('id ,fid')
            ->where([
            'is_show_nav' => 0
        ])
            ->select();
        $fids = $goodsClass->selectClass($classData, $class);
        if ($fids) {
            $a = substr($fids, 1);
            $arr = explode(',', $a);
        } else {
            $arr = [];
        }
        array_unshift($arr, $class);
        // 删除class_id!=$class的商品
        $newGoodsclass = [];
        $in = [];
        $step = 0;
        
        $m = BaseModel::getInstance(GoodsModel::class);
        foreach ($goodsData as $k => $v) {
            
            $rc = $m->field('p_id,id,title,price_member,class_id')
                ->where([
                'id' => $k
            ])
                ->find();
            if (in_array($rc['class_id'], $arr)) {
                $newGoodsclass[$step]['id'] = $rc['id'];
                $newGoodsclass[$step]['p_id'] = $rc['p_id'];
                $newGoodsclass[$step]['price_member'] = $rc['price_member'];
                $newGoodsclass[$step]['title'] = $rc['title'];
                $in[] = $rc['p_id'];
                ++ $step;
                
                if ($step == $num) { // 查询指定条数据后跳出循环
                    break;
                }
            }
        }
        
        if ($step < $num) {
            // 补全数据
            $addNum = $num - $step;
            $goods['shelves'] = [
                'gt',
                0
            ];
            $goods['stock'] = [
                'gt',
                0
            ];
            $goods['p_id'] = [
                'gt',
                0
            ];
            $arr ? ($goods['class_id'] = [
                'in',
                $arr
            ]) : '';
            if ($in) {
                array_unshift($in, 0);
            } else {
                $in = [
                    0
                ];
            }
            $goods['p_id'] = [
                'notIn',
                $in
            ];
            $addGoods = $m->field('id,price_member,title, p_id')
                ->where($goods)
                ->group('p_id')
                ->limit($addNum)
                ->select();
            $newGoodsclass = array_merge_recursive($newGoodsclass, $addGoods);
        }
        
        $a = $m->goods_image($newGoodsclass);
        return $a;
    }

    /**
     * 商品模块最新上架
     */
    private function shelves($class = 0, $num = 8)
    {
        // 查询对应class_id的商品
        // 调用方法查询分类id
        $rs = BaseModel::getInstance(GoodsModel::class)->getGoodsShelevs($num);
        //
        if (empty($rs)) {
            return array();
        }
        
        $rf = $this->num($class, $rs, $num);
        
        return $rf;
    }

    /**
     * 商品模块特惠促销
     */
    public function boon($class = 0, $num = 5)
    {
        $where['recommend'] = 1;
        $where['stock'] = [
            'gt',
            0
        ];
        $where['p_id'] = [
            'gt',
            0
        ];
        $rs = M('goods')->where($where)
            ->group('p_id')
            ->order('create_time DESC')
            ->limit($num)
            ->select();
        
        $rf = $this->num($class, $rs, $num);
        return $rf;
    }

    /**
     * 商品模块超值让利
     */
    public function profit($class = 0, $num = 8)
    {
        $rf = M('poop_clearance')->field('goods_id')
            ->where([
            'status' => 1
        ])
            ->order('update_time DESC')
            ->limit($num)
            ->select();
        $where['stock'] = [
            'gt',
            0
        ];
        $where['p_id'] = [
            'gt',
            0
        ];
        $rs = [];
        
        foreach ($rf as $k => $v) {
            $where['id'] = $v['goods_id'];
            $rs = M('goods')->where($where)->select();
        }
        
        $rf = $this->num($class, $rs, $num);
        
        return $rf;
    }

    /**
     * 查询对应class_id的商品
     * $goodsDate一定是一个二维数组
     */
    private function num($class = 0, $goodsData = array(), $num = 8)
    {
        // 调用方法查询分类id
        $goodsClass = GoodsClassModel::getInitnation();
        $classData = $goodsClass->field('id ,fid')
            ->where([
            'is_show_nav' => 0
        ])
            ->select();
        $fids = $goodsClass->selectClass($classData, $class);
        if ($fids) {
            $a = substr($fids, 1);
            $arr = explode(',', $a);
        } else {
            $arr = [];
        }
        array_unshift($arr, $class);
        // 删除class_id!=$class的商品
        $newGoodsclass = [];
        $in = [];
        $step = 0;
        
        $m = BaseModel::getInstance(GoodsModel::class);
        foreach ($goodsData as $k => $v) {
            
            $rc = $m->field('p_id,id,title,price_member,class_id')
                ->where([
                'id' => $v['id']
            ])
                ->find();
            if (in_array($rc['class_id'], $arr)) {
                $newGoodsclass[$step]['id'] = $rc['id'];
                $newGoodsclass[$step]['p_id'] = $rc['p_id'];
                $newGoodsclass[$step]['price_member'] = $rc['price_member'];
                $newGoodsclass[$step]['title'] = $rc['title'];
                $in[] = $rc['p_id'];
                ++ $step;
                
                if ($step == $num) { // 查询指定条数据后跳出循环
                    break;
                }
            }
        }
        
        if ($step < $num) {
            // 补全数据
            $addNum = $num - $step;
            $goods['shelves'] = [
                'gt',
                0
            ];
            $goods['stock'] = [
                'gt',
                0
            ];
            
            $arr ? ($goods['class_id'] = [
                'in',
                $arr
            ]) : '';
            if ($in) {
                array_unshift($in, 0);
            } else {
                $in = [
                    0
                ];
            }
            $goods['p_id'] = [
                'notIn',
                $in
            ];
            $addGoods = $m->field('id,price_member,title, p_id')
                ->where($goods)
                ->group('p_id')
                ->limit($addNum)
                ->select();
            $newGoodsclass = array_merge_recursive($newGoodsclass, $addGoods);
        }
        
        /*
         * if (empty($newGoodsclass)) {
         * return array();
         * }
         * //因为商品有子父级关系 所以 只选同一分类同一产品下的一件商品
         * $parseArray = [];
         * foreach ($newGoodsclass as $key => &$value) {
         * if (!isset($parseArray[$value[GoodsModel::$pId_d]])) {
         * $parseArray[$value[GoodsModel::$pId_d]] = $value;
         * }
         * }
         */
        
        $a = $m->goods_image($newGoodsclass);
        return $a;
    }

    /**
     * 获取限购商品的信息
     */
    /*
     * public function xianshi(){
     * $where['a.restrictions_status']=1;
     * $nowTime=time();
     * $where['a.restrictions_start']=['lt',$nowTime];
     * $where['a.restrictions_over']=['gt',$nowTime];
     * $where['b.p_id']=['gt',0];
     * $rs=D('goods_restrictions as a')
     * ->join('db_goods as b ON a.goods_id=b.id')
     * ->field('b.id,b.p_id')
     * ->group('b.p_id')
     * ->where($where)
     * ->order('restrictions_over desc')
     * ->limit(4)
     * ->select();
     *
     * if(!$restric = S('restric')){
     * $restric=[];
     * foreach($rs as $k=>$v){
     * $rf=M('goods_images')->where(['goods_id'=>$v['id']])->limit(1)->find();
     * $restric[$k]['pic_url']=$rf['pic_url'];
     * $restric[$k]['goods_id']=$v['id'];
     * }
     * S('restric',$restric,3600);
     * }
     *
     * // dump($restric);exit;
     * if($rs){
     * return $restric;
     * }
     * }
     */
    
    /**
     * 获取首页banner的方法
     */
    public function banner()
    {
        $banners = M("Ad")->where([
            'ad_space_id' => 3
        ])
            ->order('id desc,create_time')
            ->limit(6)
            ->select();
        $this->ajaxReturn($banners);
    }

    /**
     * 广告图
     */
    private function assist()
    {
        $ad = M('ad');
        $top_big_ad = $ad->field('id,pic_url,ad_link')
            ->where([
            'ad_space_id' => 1
        ])
            ->order('create_time desc')
            ->limit(1)
            ->select();
        $this->assign('top_big_ad', $top_big_ad);
        
        $top_small_ad = $ad->field('id,pic_url,ad_link')
            ->where([
            'ad_space_id' => 2
        ])
            ->order('create_time desc')
            ->limit(1)
            ->select();
        $this->assign('top_small_ad', $top_small_ad);
    }
    
    // 显示二维码 和他说明
    public function qr_code_show()
    {
        $this->display();
    }
    
    // 关于我们
    public function about_us()
    {
        $m = M('page');
        $result = $m->where("columu='about_us'")->find();
        $result['create_time'] = date('Y-m-d', $result['create_time']);
        $this->assign('result', $result);
        
        $info = M('Single');
        $list_1 = $info->field('id,single_title,type')
            ->where('type="新手指南"')
            ->select();
        $this->assign('list_1', $list_1);
        
        $list_2 = $info->field('id,single_title,type')
            ->where('type="购物指南"')
            ->select();
        $this->assign('list_2', $list_2);
        
        $list_3 = $info->field('id,single_title,type')
            ->where('type="支付方式"')
            ->select();
        $this->assign('list_3', $list_3);
        
        $list_4 = $info->field('id,single_title,type')
            ->where('type="客服中心"')
            ->select();
        $this->assign('list_4', $list_4);
        $this->display();
    }
    
    // 定义奖项概率
    private function sj_num()
    {
        $arr = range(0, 99); // 定义一个概率的数组
        $new_arr = array_count_values($arr); // 获取每个元素的个数
        $m = M('jiangpin');
        $this->results = $m->select();
        /*
         * 默认奖品概率
         * 一等奖 1%
         * 二等奖 5%
         * 三等奖 10%
         * 四等奖 15%
         * 参与奖 20%
         */
        foreach ($this->results as $key => $val) {
            // echo $val["jiangpin_gailv"];
            // echo $key."=>>>".$new_arr[$key];
            if ($val["jiangpin_gailv"] == 0) {
                $arr[$key] = "a" . $key + 1;
            } else 
                if ($val["jiangpin_gailv"] > $new_arr[$key]) {
                    $cha = $val["jiangpin_gailv"] - $new_arr[$key]; // 相差的概率
                    if ($key == 0) {
                        for ($i = 11; $i < $cha + 11; $i ++) {
                            $arr[$i] = $key + 1;
                        }
                    } else 
                        if ($key == 1) {
                            for ($i = 21; $i < $cha + 21; $i ++) {
                                $arr[$i] = $key + 1;
                            }
                        } else 
                            if ($key == 2) {
                                for ($i = 41; $i < $cha + 41; $i ++) {
                                    $arr[$i] = $key + 1;
                                }
                            } else 
                                if ($key == 3) {
                                    for ($i = 61; $i < $cha + 61; $i ++) {
                                        $arr[$i] = $key + 1;
                                    }
                                } else 
                                    if ($key == 4) {
                                        for ($i = 81; $i < $cha + 81; $i ++) {
                                            $arr[$i] = $key + 1;
                                        }
                                    }
                } else 
                    if ($val["jiangpin_gailv"] < $new_arr[$key]) {
                        $cha = $new_arr[$key] - $val["jiangpin_gailv"];
                        if ($key == 0) {
                            for ($i = 11; $i < $cha + 11; $i ++) {
                                $arr[$i] = "a" . $key;
                            }
                        } else 
                            if ($key == 1) {
                                for ($i = 21; $i < $cha + 21; $i ++) {
                                    $arr[$i] = "a" . $key;
                                }
                            } else 
                                if ($key == 2) {
                                    for ($i = 41; $i < $cha + 41; $i ++) {
                                        $arr[$i] = "a" . $key;
                                    }
                                } else 
                                    if ($key == 3) {
                                        for ($i = 61; $i < $cha + 61; $i ++) {
                                            $arr[$i] = "a" . $key;
                                        }
                                    } else 
                                        if ($key == 4) {
                                            for ($i = 81; $i < $cha + 81; $i ++) {
                                                $arr[$i] = "a" . $key;
                                            }
                                        }
                    }
        }
        
        $suiji = $arr[array_rand($arr)];
        /* echo $suiji; */
        return $suiji;
        /*
         * 判断概率是否为0 为0的话，在数组中删除对应的奖品值
         * 判断是否小于或大于概率 若问哦大于概率则增加对应奖励的值，小于则删除
         *
         */
    }
    // 抽奖页面
    public function choujiang()
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['mobile'])) {
            // 要回调的url
            $this->redirect('Public/login'); // 请先登录
            exit();
        }
        // 自己的中奖记录
        $where_my['user_id'] = $_SESSION['user_id'];
        $result = M('choujiang')->where($where_my)
            ->order('id DESC')
            ->limit('30')
            ->select();
        foreach ($result as $k => $v) {
            $result[$k]['mobile'] = substr_replace($v['mobile'], '****', 3, 4);
            $result[$k]['choujiang_time'] = date('H:i:s', $v['choujiang_time']);
        }
        $this->assign('result', $result);
        // 查询用户积分
        $user = M('user');
        $my = $user->field('integral,add_jf_currency,add_jf_limit')
            ->where(array(
            'id' => $_SESSION['user_id']
        ))
            ->find();
        $jfa = $my['add_jf_limit'];
        $jfb = $my['add_jf_currency'];
        // dump($my);
        $this->assign("jfa", $jfa);
        $this->assign("jfb", $jfb);
        // 全部人中奖记录
        $where_all['jiangpin_dengji'] = array(
            'neq',
            '未中奖'
        );
        $result_all = M('choujiang')->where($where_all)
            ->order('id DESC')
            ->limit('30')
            ->select();
        foreach ($result_all as $k => $v) {
            $result_all[$k]['mobile'] = substr_replace($v['mobile'], '****', 3, 4);
            $result_all[$k]['choujiang_time'] = date('H:i:s', $v['choujiang_time']);
        }
        // 获取每次抽奖扣取的积分
        $choujiang_xianzhi = M('choujiang_xianzhi');
        $low_jf = $choujiang_xianzhi->where(array(
            'id' => 1
        ))->getField('xianzhi_jifen');
        $this->assign('low_jf', $low_jf);
        $jiangpin = M('jiangpin'); // 获取奖品表中的信息
        $results = $jiangpin->select();
        $this->assign('result_all', $result_all);
        $this->assign('results', $results);
        // dump(session());
        $this->display();
    }
    
    // 抽奖
    public function choujiang_add()
    {
        if (IS_AJAX) {
            
            if (empty($_SESSION['user_id']) || empty($_SESSION['mobile'])) {
                // 要回调的url
                $this->ajaxReturn('no_login'); // 请先登录
                exit();
            }
            
            $m = M('choujiang');
            $choujiang_xianzhi = M('choujiang_xianzhi');
            $where['id'] = $this->sj_num();
            $start_time = date('Y-m-d 0:0:0', NOW_TIME);
            $end_time = date('Y-m-d 23:59:59', NOW_TIME);
            $start_time = strtotime($start_time);
            $end_time = strtotime($end_time);
            $times = $m->order('choujiang_time')
                ->where(array(
                'user_id' => $_SESSION['user_id'],
                'choujiang_time' => array(
                    array(
                        'gt',
                        $start_time
                    ),
                    array(
                        'lt',
                        $end_time
                    )
                )
            ))
                ->count();
            // file_put_contents('times.txt',$m->getlastsql());
            $set_times = $choujiang_xianzhi->where(array(
                'id' => 1
            ))->getField('times');
            if ($set_times != 0 && $times >= $set_times) {
                $this->ajaxReturn(888); // 你今日的抽奖次数已用完,明天再来吧
                exit();
            }
            $jiangpin = M('jiangpin'); // 获取奖品表中的信息
            $rs = $jiangpin->where($where)->find();
            // 获取每次抽奖扣取的积分
            
            $low_jf = $choujiang_xianzhi->where(array(
                'id' => 1
            ))->getField('xianzhi_jifen');
            
            // 查询用户积分
            $user = M('user');
            $member = M('member', 'vip_');
            $my = $user->where(array(
                'id' => $_SESSION['user_id']
            ))->find();
            $grade = $member->where(array(
                'id' => $_SESSION['user_id']
            ))->getField('grade_name');
            $jfa = $my['add_jf_limit'];
            $jfb = $my['add_jf_currency'];
            if ($jfa + $jfb < $low_jf) {
                $this->ajaxReturn(6); // 积分不足
                exit();
            }
            if ($grade == '会员') {
                if ($jfa > $low_jf) {
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('add_jf_limit', $low_jf);
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('integral', $low_jf);
                } elseif ($jfa > 0) {
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('add_jf_limit', $jfa);
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('add_jf_currency', $low_jf - $jfa);
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('integral', $low_jf);
                } elseif ($jfb > $low_jf) {
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('add_jf_currency', $low_jf);
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('integral', $low_jf);
                } else {
                    $this->ajaxReturn(6); // 积分不足
                }
            } elseif ($grade == '合伙人') {
                if ($jfb > $low_jf) {
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('add_jf_currency', $low_jf);
                    $user->where(array(
                        'id' => $_SESSION['user_id']
                    ))->setDec('integral', $low_jf);
                } else {
                    $this->ajaxReturn(6); // 积分不足
                }
            } else {
                $this->ajaxReturn(6); // 积分不足
            }
            $add['user_id'] = $_SESSION['user_id'];
            $add['choujiang_time'] = time();
            $add['mobile'] = $_SESSION['mobile'];
            if ($rs) {
                $add['jiangpin_dengji'] = $rs["jiangpin_dengji"];
                $add['jiangpin_name'] = $rs["jiangpin_name"];
                $add['is_win'] = 1;
            } else {
                $add['jiangpin_dengji'] = "未中奖";
                $add['jiangpin_name'] = "谢谢参与";
            }
            $add['use_jifen'] = $low_jf;
            $add['status'] = 0;
            $add['ip'] = get_client_ip();
            $result = $m->add($add);
            $this->ajaxReturn($where['id']);
            /*
             * if(empty($result)){
             * $this->ajaxReturn(0);
             * }else{
             * $this->ajaxReturn(1);
             * }
             */
        }
    }
}





