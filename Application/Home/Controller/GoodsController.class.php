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
use Home\Model\GoodsClassModel;
use Home\Model\GoodsModel;
use Common\Model\BaseModel;
use Home\Model\GoodsSpecItemModel;
use Home\Model\SpecGoodsPriceModel;
use Home\Model\GoodsSpecModel;
use Common\TraitClass\FrontGoodsTrait;
use Home\Model\GoodsImagesModel;
use Home\Model\BrandModel;
use Common\Model\GoodsDetailModel;
use Home\Model\OrderGoodsModel;
use Home\Model\SendAddressModel;
use Common\Model\GoodsConsultationModel;
use Think\Controller;
use Home\Model\PoopClearanceModel;
use Common\Model\PromotionTypeModel;

/**
 * 商品控制器
 */
class GoodsController extends BaseController
{
    use FrontGoodsTrait;

    private static $model;
    
    private $goodsModel;
    
    private $specDataByGoods = [];
    
    //商品详情页面
    const GOODS_DETAIL_HTML = 'goodsDetails';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    // 商品列表
    public function goods()
    {
        
        // 检测传值
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'class_id'
            ),
            'class_sub_id',
            'price'
        ), true) === false ? $this->error('灌水机制已打开') : true;
        
        // 大分类及其当前分类的相同父级
        $data = GoodsClassModel::getInitnation()->classTop(array(
            'where' => array(
                'fid' => 0,
                'type',
                'hide_status' => 0,
                'type' => 1
            ),
            'field' => array(
                'id',
                'class_name'
            )
        ), $_GET['class_id']);
        
        $result = GoodsModel::getInitation()->screenData($_GET);
        if ($_GET['class_id']) {
            $this->assign('class_id', $_GET['class_id']);
        }
        $this->assign('result', $result);
        $this->assign('post', $_POST);
        
        $this->classData = $data;
        $this->display();
    }
    
    /**
     * 尾货清仓到商品详情
     * @param int $id 商品编号
     */
    public function poopByGoodsDetail ($id)
    {
        $this->errorNotice($id);
    
        $poopClearModel = BaseModel::getInstance(PoopClearanceModel::class);
        
        //获取尾货清仓数据
        $poopData = $poopClearModel->getPoopClearData($id);
        
        $this->promptParse($poopData);
        
        //处理促销类型
        $promotionTypeModel = BaseModel::getInstance(PromotionTypeModel::class);
        
        $promotionType = $promotionTypeModel->getPromotionType($poopData[PoopClearanceModel::$typeId_d]);
        
        
        $this->promptParse($promotionType);
        
        //获取商品信息
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $goodsModel->setSetGoodsId($_GET['id']);
   
        // 商品数据
        $result = $goodsModel->parseGoodsByPoopClear($promotionType[PromotionTypeModel::$status_d], $poopData[PoopClearanceModel::$expression_d]);
        
        //是否为空
        $this->promptParse($result);
        
        //商品详情
        $this->goodsFlag($result);
        
        $specData = $this->getSpecialByGoods([$result]);
        
        unset($promotionType[PromotionTypeModel::$status_d], $promotionType[PromotionTypeModel::$id_d]);
        
        $promotionType['promotion'] = $promotionType[PromotionTypeModel::$promationName_d];
        
        $promotionType['discount'] = $poopData[PoopClearanceModel::$expression_d];
        
        $this->assign('result', $result);
        
        $this->assign('spcClassData', $specData);
        
        $this->assign('promotionInformation', $promotionType);
        
        $this->assign('requstURL', U('Settlement/shopping'));
        
        $this->assign('specParseArray', $this->specDataByGoods);
        
        $this->display(self::GOODS_DETAIL_HTML);
        
    }
    
    // 商品详情
    public function goodsDetails($id, $promotion = null)
    {
        // 检测传值
        $this->errorNotice($id);
        
        // 如果有多个用户登这台pc,将删除上一个用户保存的足迹信息。只保存一个用户的信息
        // 当用户存在时，把商品id报存到我的足迹里面，方便猜一猜使用
        if (!empty($_COOKIE['user_id'])) {
            $key = C('MY_TRACKS_COOKIE_KEY');
            $my_tracks = cookie($key);
            // 如果有多个用户登这台pc,将删除上一个用户保存的足迹信息。只保存一个用户的信息
            if ($my_tracks['user_id'] != $_COOKIE['user_id']) {
                unset($my_tracks);
            }
            $my_tracks['user_id'] = $_COOKIE['user_id'];
            $my_tracks[] = I('get.id');
            cookie($key, $my_tracks, 7 * 24 * 3600); // 保存一周
        }
        
        $model = BaseModel::getInstance(GoodsModel::class);
        
        
        
        $result = $model->getShelve($_GET['id']);
        
        $this->goodsModel = $model;
        
        //商品详情
        $this->goodsFlag($result);
       
        //规格商品
        $spcClassData = $this->getSpecDataByGoodsId($result);
        
        $specDataByGoods = $this->specDataByGoods;
       
        // 猜你喜欢[根据商品 编号 及其同属的商品分类]
        $id = $id . ':' . $result[GoodsModel::$pId_d];
        
        if (false === strpos(cookie('productId'), $_GET['id'])) {
            $id = $id . ',' . cookie('productId');
            cookie('productId', $id, 86400);
        }
        
        $this->assign('spcClassData', $spcClassData);
        
        $this->assign('specParseArray', $specDataByGoods);
        
        $this->assign('result', $result);
        
        $this->assign('requstURL', U('Settlement/buyNow'));
        
        $this->display();
    }
    
    /**
     * 获取规格项数据
     * @param array $result
     * @throws \Exception
     * @return array
     */
    private function getSpecDataByGoodsId (array $result)
    {
        if (! ($this->goodsModel instanceof GoodsModel)) {
            throw new \Exception('类类型不正确');
        }
         
        // 获取规格项商品
        $specData = $this->goodsModel->getChildrenGoods($result[GoodsModel::$pId_d]);
    
        return $this->getSpecialByGoods($specData);
    }
    
    /**
     * 处理规格产品
     */
    private function getSpecialByGoods (array $goodsResult)
    {
        Tool::connect('parseString');
        // 规格数据
        $specDataByGoods = BaseModel::getInstance(SpecGoodsPriceModel::class)->getSpecByGoods($goodsResult, GoodsModel::$id_d);
        
        $this->promptParse($specDataByGoods);
        
        $this->specDataByGoods = $specDataByGoods;
        
        // 获取规格项数据及其处理
        $spcItemModel = BaseModel::getInstance(GoodsSpecItemModel::class);
        
        $spcItemClassData = $spcItemModel->getSpecItemName($specDataByGoods, SpecGoodsPriceModel::$key_d);
        
        $spcModel = BaseModel::getInstance(GoodsSpecModel::class);
        
        $spcClassData = $spcModel->getSpecItemName($spcItemClassData, GoodsSpecItemModel::$specId_d);
        
        // 重组数据
        $spcClassData = $this->recombinationSpec($spcClassData, $spcItemClassData);
        return $spcClassData;
        
    }
    
    
    /**
     * ajax 获取猜你喜欢
     */
    public function ajaxGetGuessLove()
    {
        Tool::isSetDefaultValue($_POST, array(
            'p' => 1
        ));
        
        $productId = $this->parseString(cookie('productId'));
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $classId = $goodsModel->getAttribute(array(
            'field' => array(
                GoodsModel::$id_d,
                GoodsModel::$classId_d
            ),
            'where' => array(
                GoodsModel::$id_d => $_SESSION['goodsPId']
            )
        ), false, 'find');
        
        $productGoods = $goodsModel->guessLove($classId, $productId, $_POST['p']);
        
        // 获取价格
        $specGoodsModel = BaseModel::getInstance(SpecGoodsPriceModel::class);
        
        Tool::connect('parseString');
        $productGoods = $specGoodsModel->getSpecByGoods($productGoods, GoodsModel::$id_d);
        
        // 获取图片
        $goodsImagsModel = BaseModel::getInstance(GoodsImagesModel::class);
        
        $productGoods = $goodsImagsModel->getImageById($productGoods, GoodsModel::$pId_d);
        
        $this->data = $productGoods;
        
        $this->goodsImages = GoodsImagesModel::class;
        
        $this->specModel = SpecGoodsPriceModel::class;
        
        $this->assign('goodsModel', GoodsModel::class);
        
        $this->page = $goodsModel->pageCount;
        $this->display();
    }

    /**
     * ajax 获取畅销排行
     */
    public function bestSelling()
    {
        // 根据定单获取购买商品数量最多的前10个
        $model = BaseModel::getInstance(OrderGoodsModel::class);
        
        $data = $model->getAttribute(array(
            'field' => array(
                OrderGoodsModel::$goodsId_d,
                'count(*) as count'
            ),
            'group' => OrderGoodsModel::$goodsId_d,
            'order' => ' count ' . BaseModel::DESC,
            'limit' => 10
        ));
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        Tool::connect('parseString');
        
        $data = $goodsModel->getGoodsByOrderCount($data, OrderGoodsModel::$goodsId_d);
        
        // 获取图片
        $haveImages = BaseModel::getInstance(GoodsImagesModel::class)->hotRecommendation($data, GoodsModel::$pId_d);
        
        $this->assign('recGoods', $haveImages);
        
        $this->assign('goodsModel', GoodsModel::class);
        
        $this->assign('goodsImages', GoodsImagesModel::class);
        
        $this->display();
    }

    /**
     * 获取推荐配件,优惠套餐,最佳组合
     */
    public function ajaxGetGoodsRecommend()
    {
        $goods_id = I('goods_id', - 1, 'intval');
        
        $parent = M('goods')->field('p_id')->find($goods_id);
        $parent = empty($parent['p_id']) ? $goods_id : $parent['p_id'];
        $this->promptPjax(!empty($goods_id) || $goods_id !== - 1, '参数错误');
        
        // 推荐配件
        $accessories = D('goods')->accessories($parent);
        
        // 最佳组合
        $combo = D('goods')->combo($parent);
        // 优惠套餐
        $package = D('goods')->package($goods_id);
        $package = array_pop($package);
        $size = count($package['sub']);
        
        $this->assign('accessories', $accessories);
        $this->assign('combo', $combo);
        $this->assign('package', $package);
        $this->assign('package_size', $size);
        $this->display();
    }

    /**
     * 获取评论
     */
    public function ajaxGetGoodsComment()
    {
        $gid = I('goods_id', - 1, 'intval');
        if ($gid == - 1) {
            $this->ajaxReturn(0);
        }
        
        // 0,全部评价 1,晒图 2,好评 3,中评 4,差评 5,只看当前评论
        $type = I('type', 0, 'intval');
        switch ($type) {
            case 0:
                $this->getAllComment($gid);
                exit();
                break;
            case 1:
                $list = $this->getGoodsShowImages($gid);
                break;
            case 2:
            case 3:
            case 4:
                $model = M('goods');
                $pid = $model->field('p_id')->find($gid);
                $pid = $pid['p_id'] ? $pid['p_id'] : $gid;
                $data = $model->field('id')
                    ->where([
                    "p_id" => $pid
                ])
                    ->select();
                $ids = [];
                foreach ($data as $vo) {
                    $ids[] = $vo['id'];
                }
                $order = D('orderComment');
                $list = $order->commentByGoodsId($ids, $type);
                break;
            case 5:
                $order = D('orderComment');
                // $gid 需要装换为string,thinkphp 的 in封装 不认int
                $list = $order->commentByGoodsId($gid . '', $type);
                break;
            default:
                // code...
                break;
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取所有的评论view
     */
    public function getAllComment($gid)
    {
        // 清空数据显示行为,防止被thinkphp分页函数记住
        $show = I('GET.show', 'json');
        unset($_GET['show']);
        
        $model = M('goods');
        $pid = $model->field('p_id')->find($gid);
        $pid = $pid['p_id'] ? $pid['p_id'] : $gid;
        if (empty($pid)) {
            $this->ajaxReturn(0);
        }
        $data = $model->field('id')
            ->where([
            "p_id" => $pid
        ])
            ->select();
        // 获取商品评论
        if (is_array($data)) {
            $ids = [];
            foreach ($data as $vo) {
                $ids[] = $vo['id'];
            }
            $order_model = D('orderComment');
            // 评论列表
            $list = $order_model->commentByGoodsId($ids, $type);
            // 统计好评
            $level = $order_model->commentPraise($ids);
            // 统计印象
            $feel = $order_model->statistical($ids);
        }
        $list = is_array($list) ? $list : [];
        $level = is_array($level) ? $level : [];
        
        if ($show == 'json') {
            $this->ajaxReturn($list);
        } elseif ($show == 'view') {
            $this->assign('list', $list['list']);
            $this->assign('page', $list['page']);
            $this->assign('level', $level);
            $this->assign('feel', $feel);
            $this->display('comment');
        }
    }

    /**
     * 晒图
     * 一次性最多返回最近500张
     */
    public function getGoodsShowImages($gid)
    {
        $model = M('goods');
        $pid = $model->field('p_id')->find($gid);
        $pid = $pid['p_id'] ? $pid['p_id'] : $gid;
        if (empty($pid)) {
            $this->ajaxReturn(0);
        }
        // 同级商品id
        $data = $model->field('id')
            ->where([
            "p_id" => $pid
        ])
            ->select();
        if (is_array($data)) {
            $ids = [];
            foreach ($data as $vo) {
                $ids[] = $vo['id'];
            }
            $data = D('orderComment')->picByGoods($ids);
        }
        
        return is_array($data) ? $data : [];
    }

    /**
     * ajax 获取商品咨询
     */
    public function ajaxGetGoodsConsulation()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->ajaxReturnData(null, 0, '系统防御启动');
        
        empty($_POST['p']) ?: $_GET['p'] = $_POST['p'];
        
        $model = BaseModel::getInstance(GoodsConsultationModel::class);
        
        // 当前问题 包含当前回答
        $data = $model->getConsulation($_POST['id']);
        
        $this->data = $data;
        
        $this->model = GoodsConsultationModel::class;
        
        $this->display();
    }

    /**
     * 提交咨询
     */
    public function consulationSubmit()
    {
        Tool::checkPost($_POST, (array) null, false, array(
            'content'
        )) ? true : $this->ajaxReturnData(null, 0, '系统防御预热启动');
        $model = BaseModel::getInstance(GoodsConsultationModel::class);
        
        $status = $model->addConsulation($_POST);
        
        $flag = sha1(md5('IsExitsConsultion') . time()); // 标记是否已经提问过
        
        $this->promptPjax($status, '添加失败');
        
        $this->ajaxReturnData(array(
            'flag' => $flag
        ));
    }
    
    // 限购商品列表
    public function xiangou()
    {
        
        // 设置一下默认值防止报错
        Tool::isSetDefaultValue($_GET, array(
            'class_sub_id' => 0,
            'class_id' => 222,
            'price' => 0
        ));
        // 大分类及其当前分类的相同父级
        $data = GoodsClassModel::getInitnation()->classTop(array(
            'where' => array(
                'fid' => 0,
                'type',
                'hide_status' => 0,
                'type' => 1
            ),
            'field' => array(
                'id',
                'class_name'
            )
        ), $_GET['class_id']);
        
        $result = GoodsModel::getInitation()->screenData($_GET, 1);
        
        if ($_GET['class_id']) {
            $this->assign('class_id', $_GET['class_id']);
        }
        $this->assign('result', $result);
        $this->assign('post', $_POST);
        
        $this->classData = $data;
        
        $this->display('goods');
    }
    
    // ajax查找商品
    public function search()
    {
        $title = I('post.title');
        if (! $title) {
            $this->ajaxReturnData('', 2, '');
            exit();
        }
        $where['title'] = [
            'like',
            "%$title%"
        ];
        $where['p_id'] = [
            'gt',
            0
        ];
        $where['shelves'] = [
            'eq',
            1
        ];
        $data = M('goods')->where($where)
            ->limit(0, 10)
            ->select();
        if ($data) {
            $this->ajaxReturnData($data, 1, '');
            exit();
        } else {
            $this->ajaxReturnData('', 0, '没有此项商品');
            exit();
        }
    }
    // ajax删除商品信息;
    public function dels()
    {
        $id = I('post.id') - 0;
        $cart = M('goods_cart');
        $rs = $cart->where([
            'user_id' => $_SESSION['user_id'],
            'id' => $id
        ])->save([
            'is_del' => 1
        ]);
        if (! $rs) {
            $this->ajaxReturnData('', 0, '你要干啥子(!^_^)');
        } else {
            $this->ajaxReturnData('', 1, '操作成功(!^_^)');
        }
    }
    
    public function searchOne()
    {
        $title = I('post.title');
        $where['title'] = [
            'like',
            "%$title%"
        ];
        $data = M('goods')->where($where)
            ->limit(1)
            ->find();
        if (! $data) {
            $this->ajaxReturnData('', 0, '该商品不存在');
        } else {
            $this->ajaxReturnData($data['id'], 1, '');
        }
    }
    
    
    /**
     * 获取商品数据
     * @param array $result
     * @param GoodsModel $model
     */
    private function goodsFlag (array & $result)
    {
        $this->promptParse($result);
        
        // 品牌数据
        $brandName = BaseModel::getInstance(BrandModel::class)->getUserNameById($result[GoodsModel::$brandId_d], BrandModel::$brandName_d);
        
        $result[GoodsModel::$brandId_d] = $brandName;
        
        $detailModel =  BaseModel::getInstance(GoodsDetailModel::class);
        
        $detailModel->setSearchDbKey(GoodsDetailModel::$goodsId_d);
        
        // 商品详情
        $detail = $detailModel->getUserNameById($result[GoodsModel::$pId_d], GoodsDetailModel::$detail_d);
        
        $result[GoodsDetailModel::$detail_d] = htmlspecialchars_decode($detail);
        
        // 获取标题索引
        $goodsClassModel = BaseModel::getInstance(GoodsClassModel::class);
        
        // 面包屑链接
        $title = $goodsClassModel->getTitleByClassId($result[GoodsModel::$classId_d], 'span');
        
        // 收藏
       // $this->addCollection($result);
      
        $_SESSION['goodsPId'] = $result[GoodsModel::$pId_d];
        
        $_SESSION['formId'] = sha1(md5('WhatAreYouDoing?') . time());
        
        $images = BaseModel::getInstance(GoodsImagesModel::class)->getGoodsPictureAlbum($result[GoodsModel::$pId_d]);
        
        $first = empty($images[0]) ?: $images[0];
      
        // 发货地地点【暂且注释】
        
        // $sendModel = BaseModel::getInstance(SendAddressModel::class);
        
        // $address = $sendModel->getStock();
        
        // 商品评价数量
        $ids = D('goods')->classGoods($_GET['id']);
        $after_days = null;
        
        $giftHtml   = null;
        
        $comment_number = D('orderComment')->sum($ids);
        if ($result['stock'] == 0 && $result['advance_date'] < time()) {
            $goods_data['advance_date'] = $result['advance_date'] + time();
            M('goods')->where('id=' . $result['id'])->save($goods_data);
            $date = M('goods')->field('advance_date')
            ->where('id=' . $result['id'])
            ->find();
            if (($after_date = $date['advance_date'] - time()) >= 86400) {
                $after_days = floor($after_date / 86400) - 1;
            }
        } else
            if ($result['stock'] == 0 && $result['advance_date'] > time()) {
                if (($after_date = $result['advance_date'] - time()) >= 86400) {
                    $after_days = floor($after_date / 86400);
                }
            }
        
        $result['advance_date'] = ($after_days == null) ? $result['advance_date'] = '该商品已售完，敬请期待' : '件<span><i>离预售日期还有: ' . $after_days . '</i>天</span>';
        $result['stock'] = ($result['stock'] == 0) ? $result['stock'] = $result['advance_date'] : $result['stock'] = '件<span>货存:<i> ' . $result['stock'] . '</i>件</span>';
        // 促销
        $is_gifts = M('gifts')->field('goods_id,gift_number,gift_id')
        ->where('parent_id=' . $_GET['id'])
        ->select();
        $gift_id['id'] = $is_gifts[0]['gift_id'];
        $gift_id['status'] = 1;
        $group = M('CommodityGift')->where($gift_id)->find();
        if ($_SESSION['user_id']) {
            $user_level = M('User')->where('id=' . $_SESSION['user_id'])->find()['level_id'];
        }
        if ($is_gifts) {
            if ($group['start_time'] < time() && $group['end_time'] > time() && strpos('!==' . $group['group'], $user_level)) {
                $imgHtml = '';
                $gift_id = array();
                foreach ($is_gifts as $k => $v) {
                    $gift_id[] = $v['goods_id'];
                    $is_gifts[$k]['pid'] = M('Goods')->where('id=' . $v['goods_id'] . ' AND status=0')->find()['p_id'];
                }
                foreach ($is_gifts as $k => $v) {
                    $is_gifts[$k]['goods_image'] = M('GoodsImages')->where('goods_id=' . $v['pid'] . ' AND is_thumb=1')->find()['pic_url'];
                }
                foreach ($is_gifts as $k => $v) {
                    $imgHtml .= '<img style="width:25px;height:25px;" src="' . $v['goods_image'] . '"> ×  ' . $v['gift_number'] . ' ';
                }
                $giftHtml = '<p style="padding-left:20px;">促销: <span style="color: #df3033; background: 0 0;border: 1px solid #df3033;padding: 2px 3px;margin-right: 5px; display: inline-block; line-height: 16px;">赠品</span>  ' . $imgHtml . '</p>';
            }
        }
        // 满赠显示
        $full_of_gifts = M('CommodityGift')->where('type=0 AND status=1')->select();
        $count_gift = '';
        
        $countHtml = null;
        
        foreach ($full_of_gifts as $k => $v) {
            if ($v['start_time'] < time() && $v['end_time'] > time() && strpos('!==' . $v['group'], $user_level)) {
                $count_gift .= '<span style="color: #df3033; background: 0 0;border: 1px solid #df3033;padding: 2px 3px;margin-right: 5px; display: inline-block; line-height: 16px;">满赠</span><em class="gift_hidden">满' . $v['expression'] . '.00元即赠热销商品，赠完即止 <br/></em>';
            }
        
            if($_SESSION['user_id']!=null)
            {
                $countHtml='<p class="gift_price" style="margin-left:50px;">'.$count_gift.'</p>';
            }
        }
        // 默认地点【暂且注释】
        // $defaultAddress = $sendModel->getDefault();
        // $this->address = $address;
        
        // $this->default = $defaultAddress;
        
        $this->assign('first', $first);
        
        $this->assign('detailModel', GoodsDetailModel::class);
        
        $this->assign('title', $title);
        
        $this->assign('goodsSpecItemModel',  GoodsSpecItemModel::class);
        
        $this->goodsImages = $images;
        
        $this->goodsImagesModel = GoodsImagesModel::class;
        
      
        $this->goodsSpecModel = GoodsSpecModel::class;
        
        $this->specModel = SpecGoodsPriceModel::class;
        
        
        $this->assign('intnetTitle', $result[GoodsModel::$title_d] . ' - ' . $this->intnetTitle);
        
        $this->model = GoodsModel::class;
        
        $this->noLoad = false;
        
        $this->comment_number = $comment_number;
        
        $this->sendModel = SendAddressModel::class;
        $this->assign('giftHtml', $giftHtml);
        $this->assign('countHtml', $countHtml);
        $this->assign('gift_id', implode(',', $gift_id));
        $this->assign("goods_title", $result[GoodsModel::$title_d]);
    }
}
