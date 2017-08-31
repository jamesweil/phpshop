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

namespace Common\TraitClass;

use Home\Model\FootPrintModel;
use Home\Model\PromGoodsModel;
use Common\Model\PromotionTypeModel;
use Home\Model\SpecGoodsPriceModel;
use Home\Model\GoodsSpecItemModel;
use Home\Model\GoodsCartModel;
use Home\Model\GoodsModel;
use Common\Model\BaseModel;
use Home\Model\CouponModel;
use Home\Model\GoodsSpecModel;
use Common\Tool\Event;
use Common\Tool\Tool;
use Common\Model\UserAddressModel;
use Common\Model\RegionModel;
use Home\Model\PayTypeModel;
use Home\Model\GoodsImagesModel;
use Home\Model\OrderModel;

trait FrontGoodsTrait
{
    
    private static  $useModel;
    
    /**
     * 数据表字段名
     * @var string
     */
    protected $keyByOpreator;
    /**
     * @return the $keyByOpreator
     */
    public function getKeyByOpreator()
    {
        return $this->keyByOpreator;
    }

    /**
     * 设置数据表字段名
     * @param string $keyByOpreator
     */
    public function setKeyByOpreator($keyByOpreator)
    {
        $this->keyByOpreator = $keyByOpreator;
    }

    /**
     * 验证 立即购买
     */
    private function validateBuyNow()
    {
        // 第一步 y验证数据
        $validate = array(
            'goods_id',
            'goods_num',
            'price_new'
        );
        
        $mustExits = array_merge($validate, array(
            'formId'
        ));
        
        Tool ::checkPost($_POST, array(
            'is_numeric' => $validate
        ), true, $mustExits) ? true : $this->error('操作失败', U('Index/index'));
        
        // 第二部 验证formId
        if ($_SESSION['formId'] != $_POST['formId']) {
            $this->error('系统防御开启', U('Index/index'));
        }
        
        $_SESSION['bulidOrder'] = sha1(md5(base64_encode('MyNameIsWq') . time()));
        
    }
    
    /**
     * 发票信息
     */
    public function invoiceAllow ()
    {
        // 判断用户是否添加过发票信息
        
        $status = M('invoice')->where('user_id=' . $_SESSION['user_id'])
        ->order('id DESC')
        ->limit(1)
        ->find();
        if ($status) {
            $invoice_info['invoice_title'] = $status['invoice_title'];
            $invoice_info['id'] = $status['id'];
        } else {
            $invoice_info['invoice_title'] = '尚未设置开票信息';
        }
        
        //页面加载默认选择的发票信息
        $invoice_data=M('invoice')->where(array('user_id'=>$_SESSION['user_id'],'check_status'=>1))->find();

        $this->assign('invoice_data',$invoice_data);
        
        $this->assign('invoice_info', $invoice_info);
    }
    /**
     * 支付类型及其 地址
     */
    protected function payAndAddress ()
    {
        $model = BaseModel::getInstance(UserAddressModel::class);
        // 获取收货地址
        $userData = $model->getDefaultAddress($_SESSION['user_id']);
        
        // 传递地区表
        $areaModel = BaseModel::getInstance(RegionModel::class);
        
        $data = $areaModel->getDefaultRegion($userData, $model);
        
        // 获取支付类型
        $pay = BaseModel::getInstance(PayTypeModel::class)->getPay();
        

        $this->assign('data', $data);
        
        $this->assign('pay', $pay);
        
        $this->assign('region', UserAddressModel::class);
        
        $this->assign('payModel', PayTypeModel::class);
    }
    
    
    protected function parseGoodsData (array $goodsData)
    {
       
        Tool::connect('parseString');
        // 商品图片
        $goodsData = BaseModel::getInstance(GoodsImagesModel::class)->getImageById($goodsData, GoodsModel::$pId_d);
        
        // 获取商品规格
        $spec = $this->getSpecail($goodsData, BaseModel::getInstance(GoodsSpecItemModel::class));
        
        
        BaseModel::getInstance(OrderModel::class);
        
        $this->assign('goodsSpec', $spec);
        
        $this->assign('specModel', SpecGoodsPriceModel::class);
        
        $this->assign('orderModel', OrderModel::class);
        
        $this->assign('goodsModel', GoodsModel::class);
        
        $this->assign('goodsImage', GoodsImagesModel::class);
        
        return $spec;
    }
    
    /**
     * 赠品信息 
     */
    public function gift ()
    {

        if($_POST['gift_id']==null)
        {
        
        }else{
            $where['id']=array('in',explode(',',$_POST['gift_id']));
            $where['status']=0;
            $gifts_data=M('Goods')->where($where)->select();
            if($gifts_data)
            {
                foreach($gifts_data as $k=>$v)
                {
                    $gifts_data[$k]['img_url']=M('GoodsImages')->where('goods_id='.$v['id'].' AND is_thumb=1')->find()['pic_url'];
                    $gifts_data[$k]['price']=0;
                    $gifts_data[$k]['discount']='暂无';
                    $gifts_data[$k]['Subtotal']=0;
                    $gifts_data[$k]['key']=M('SpecGoodsPrice')->where('goods_id='.$v['id'])->find()['key'];
                }
                foreach($gifts_data as $k=>$v)
                {
                    $gifts_data[$k]['item']=M('GoodsSpecItem')->field('item')->where(array('id'=>array('in',explode('_',$v['key']))))->select();
                }
                foreach($gifts_data as $k=>$v)
                {
        
                    foreach($v['item'] as $k1=>$v2)
                    {
                        $gifts_data[$k]['new_item'][]=$v2['item'];
                    }
                }
                foreach($gifts_data as $k=>$v)
                {
                    $gifts_data[$k]['new_type']='单位:'.implode(' ',$v['new_item']);
                    $gifts_data[$k]['gift_number']=M('gifts')->where('goods_id='.$v['id'].' AND parent_id='.$_POST['goods_id'])->find()['gift_number']*$_POST['goods_num'];
                }
            }
        }
        $full_of_gifts=M('CommodityGift')->where(array('expression' => array(array('neq',0),array('lt',$_POST['goods_num']*$_POST['price_new'])),'status'=>1))->select();
        foreach($full_of_gifts as $k=>$v)
        {
            $goods_type[]= M('goods')->where(array('id'=>array('in',explode(',',$v['goods_id'])),'status'=>0))->select();
            $type0[]=M('gifts')->where(array('goods_id'=>array('in',explode(',',$v['goods_id'])),'gift_id'=>$v['id']))->select();
        }
        foreach($goods_type as $k=>$v)
        {
            foreach($v as $k1=>$v2)
            {
                if(isset($type0[$k][$k1])){
                    $goods_type[$k][$k1]['gift_number']=$type0[$k][$k1]['gift_number'];
                    $goods_type[$k][$k1]['gift_id']=$type0[$k][$k1]['id'];
                }
                $goods_type[$k][$k1]['img_url']=M('GoodsImages')->where('goods_id='.$v2['id'].' AND is_thumb=1')->find()['pic_url'];
                $goods_type[$k][$k1]['key']=M('SpecGoodsPrice')->where('goods_id='.$v2['id'])->find()['key'];
                $goods_type[$k][$k1]['price']=0;
                $goods_type[$k][$k1]['discount']='暂无';
                $goods_type[$k][$k1]['Subtotal']=0;
            }
        }
        foreach($goods_type as $k=>$v)
        {
            foreach($v as $k1=>$v2){
                $goods_type[$k][$k1]['item']=M('GoodsSpecItem')->field('item')->where(array('id'=>array('in',explode('_',$v2['key']))))->select();
            }
        
        }
        foreach($goods_type as $k=>$v)
        {
            foreach($v as $k2=>$v2){
                foreach($v2['item'] as $k3=>$v3)
                {
                    $goods_type[$k][$k2]['new_item'][]=$v3['item'];
                }
            }
        }
        
        foreach($goods_type as $k=>$v)
        {
            foreach($v as $k2=>$v2) {
                $goods_type[$k][$k2]['new_type']='单位:'.implode(' ',$v2['new_item']);
            }
        }
        if($goods_type==null)
        {
            $is_gifts='没有满足条件的赠品';
        }
        
        $this->assign('no_gifts',$is_gifts);
        $this->assign('goods_type',$goods_type);
        $this->assign('gifts_data',$gifts_data);
    }
    
    /**
     * 处理规格
     * @param unknown $goods
     * @param BaseModel $model
     * @param unknown $key
     * @return string
     */
    private function getSpecail($goods, BaseModel $model)
    {
        // 获取商品规格
        $spec = BaseModel::getInstance(SpecGoodsPriceModel::class);
    
        $spec->setSplitKey($this->keyByOpreator);
     
        $goodsSpec = $spec->getSpecGoodsByCart($goods);
        $this->prompt($goodsSpec, '商品规格出错了~~~');
    
        $this->specData = $goodsSpec;
    
        // 规格项
        $specItem = $model->getSpecItemName($goodsSpec, SpecGoodsPriceModel::$key_d);
    
        // 规格组
        $specModel = BaseModel::getInstance(GoodsSpecModel::class);
    
        $specGroup = $specModel->getSpecGroup($specItem, GoodsSpecItemModel::$specId_d);
    
        $specItem = $model->parseData($specItem, $specGroup, GoodsSpecModel::$name_d);
    
        $specData = $this->parseCartBySpec($this->specData, $specItem);
    
        return $specData;
    }
    
    /**
     * @return the $useModel
     */
    public function getUseModel()
    {
        return self::$useModel;
    }

    /**
     * @param field_type $useModel
     */
    public function setUseModel($useModel)
    {
        self::$useModel = $useModel;
    }

    /**
     * 添加收藏
     */
    protected  function addCollection($result)
    {
        if (!empty($_SESSION['user_id']) && !empty($result['goods']))
        {
            //添加我的足迹
            FootPrintModel::getInitation()->add(array(
                'uid'         => $_SESSION['user_id'],
                'gid'         => $_GET['id'],
                //    'goods_pic'   => $result['goods']['pic_url'],
                'goods_price' => $result['goods']['price_member'],
                'goods_name'  => $result['goods']['title'],
                'is_type'     => 1
            ));
        }
    }
    
    /**
     * @desc 规格子父类重组 
     * @param array $spcClassData 规格父类数据
     * @param array $spcItemClassData 规格子类数据
     * @return array
     */
     
    public function recombinationSpec(array $spcClassData, array $spcItemClassData)
    {
        
        if (empty($spcClassData) || empty($spcItemClassData) ) {
            return array();
        }
        
        foreach ($spcClassData as $key => & $name) {
            foreach ($spcItemClassData as $itemKey => &$itemValue)
                if ($name[GoodsSpecModel::$id_d] === $itemValue[GoodsSpecItemModel::$specId_d]) {
                    $name['children'][] = $itemValue;
                }
        }
        
        return $spcClassData;
    }
    
    
    public function parseCartBySpec(array $cartData, array $specData)
    {
        if (empty($cartData) || empty($specData)) {
            return array();
        }
        $flag = null;
        foreach ($cartData as $key => $value) {
            foreach ($specData as $name => $data) {
               
               if (false !== strrpos($value[SpecGoodsPriceModel::$key_d], $data[GoodsSpecItemModel::$id_d])) {
                   
                   $flag .= ','.$data[GoodsSpecItemModel::$item_d];
                   $cartData[$key][SpecGoodsPriceModel::$key_d] = substr($flag, 1);
               }
            }
            $flag = null;
        }
        return $cartData;
    }
    
    
    /**
     * 统一结算键名
     * @param array $data 商品数组
     * @return array
     */
    public function unoipy(array $data)
    {
        if (empty($data)) {
            $this->error = '数据错误';
            return array();
        }
        $temp = null;
        foreach ($data as $key => &$value) {
    
            $value['cart_id'] = $value[GoodsCartModel::$id_d];
            
            $temp = $value[GoodsCartModel::$goodsId_d];
    
            $value[GoodsModel::$id_d] = $temp;
    
            unset($data[$key][GoodsCartModel::$goodsId_d]);
        }
    
        return $data;
    }
    
    
    /**
     * @desc 是否是优惠商品 
     * @param array $goods 商品数组
     * @param BaseModel $model 数据模型
     * @return array
     */
    protected static function isPromotion (array $goods, BaseModel $model)
    {
        if (empty($goods) || !($model instanceof BaseModel)) {
            return $goods;
        }
       
        $promotion = $model->getPromotionGoods($goods, $model::$goodsId_d);
        
        if (empty($promotion)) {
            return $goods;
        }
        
        $data = static::sumPromotion($promotion, $model);
      
        return $data;
        
    }
    /**
     * 统计优惠数据 
     */
    protected static function sumPromotion (array $promotion, BaseModel $model)
    {
        if (empty($promotion) || !($model instanceof BaseModel)) {
            return array();
        }
        
        //获取促销数据及其折扣
        $promotionModel = BaseModel::getInstance(PromGoodsModel::class);
        
        $data = $promotionModel->getPromotionInfo($promotion, $model::$promId_d);
       
        //获取折扣类型
        $proTypeModel = BaseModel::getInstance(PromotionTypeModel::class);
        
        $data = $proTypeModel->getTypeData($data, PromGoodsModel::$type_d);
        
        //获取代金券数据
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        $data = $couponModel->getCouponData($data, $promotionModel);
        
        //回调方法
        
        Event::listen('goods', $data);
        //计算价格
        $data = static::sumPrice($data);
        return $data;
    }
    
    
    /**
     * 处理价格 
     */
    protected static function sumPrice($data)
    {
        if (empty($data)) {
            return array();
        }
        
        $total = 0;
        
        foreach ($data as $key => &$value)
        {
            if (empty($value['poopStatus'])) {
                continue;
            }
            switch ($value['poopStatus']) {//0 打折，1,减价优惠,2,固定金额出售-1买就送代金券
                case 0:
                    $value[GoodsCartModel::$priceNew_d] = sprintf('%01.2f',$value[GoodsCartModel::$priceNew_d] * $value[PromGoodsModel::$expression_d]/100);
                    break;
                case 1:
                     $value[GoodsCartModel::$priceNew_d] = sprintf('%01.2f', $value[GoodsCartModel::$priceNew_d] - $value[PromGoodsModel::$expression_d]);
                    break;
                case 2:
                    $value[GoodsCartModel::$priceNew_d] = sprintf('%01.2f',  $value[PromGoodsModel::$expression_d]);
                    break;
            }
            $total += $value[GoodsCartModel::$priceNew_d]*$value[GoodsCartModel::$goodsNum_d];
        }
        $model = self::$useModel;
        $model->setTotalMonery($model->getTotalMonery()+$total);
        return $data;
    }
    
    /**
     * 处理字符串 
     * @param string $goodsId
     * @return NULL|NULL|string
     */
    public function parseString($goodsId)
    {
        if (empty($goodsId)) {
            return null;
        }
        
        $goodsId =  substr($goodsId, 0, -1);
        
        $array = explode(',', $goodsId);
         
        $productId = null;
        
        $where = array();
        
        foreach ($array as $key => $value) {
            if (false !== strpos($value, $_SESSION['goodsPId']) ) {
                unset($array[$key]);
            } else {
                list($id, $pId) = explode(':', $value);
                $productId .= ','.$id;
            }
        }
        
        return $productId;
    }
  
}