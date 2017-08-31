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

namespace Home\Model;
use Think\Page;
use Common\TraitClass\callBackClass;
use Common\Tool\Tool;
use Common\Model\BaseModel;
use Common\TraitClass\ParsePromotionTrait;

class GoodsModel extends BaseModel
{
    use callBackClass;
    use ParsePromotionTrait;
    
    private static $obj ;

	public $pageCount;

	public static $id_d;	//主键编号

	public static $brandId_d;	//品牌编号

	public static $title_d;	//商品标题

	public static $priceMarket_d;	//市场价

	public static $priceMember_d;	//会员价

	public static $stock_d;	//库存

	public static $selling_d;	//是否是热销   0 不是   1 是

	public static $shelves_d;	//0下架，1表示选择上架

	public static $classId_d;	//商品分类ID

	public static $recommend_d;	//1推荐 0不推荐

	public static $dIntegral_d;	//赠送积分

	public static $code_d;	//商品货号

	public static $top_d;	//顶部推荐

	public static $seasonHot_d;	//当季热卖

	public static $restrictions_d;	//是否限购:    1 限购  0 不限购

	public static $description_d;	//商品简介

	public static $groupBuy_d;	//是否团购 默认0 不团购 1是

	public static $updateTime_d;	//最后一次编辑时间

	public static $createTime_d;	//创建时间

	public static $goodsType_d;	//商品类型

	public static $latestPromotion_d;	//最新促销：1表示热卖促销，2表示热卖精选，3表示人气特卖

	public static $sort_d;	//排序

	public static $pId_d;	//父级产品 SPU

	public static $status_d;	//0没有活动，1尾货清仓，2，最新促销，3积分商城,4打印耗材,5优惠套餐

	public static $commentMember_d;	//评论次数

	public static $salesSum_d;	//商品销量

	public static $attrType_d;	//商品属性编号【为goods_type表中数据】

	public static $extend_d;	//扩展分类

	public static $advanceDate_d;	//预售日期

	public static $weight_d;	//重量


    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    /**
     * @param number $goodsNumKey
     */
    public function setGoodsNumKey($goodsNumKey)
    {
        $this->goodsNumKey = $goodsNumKey;
    }
    
    /**
     * @param number $priceNewKey
     */
    public function setPriceNewKey($priceNewKey)
    {
        $this->priceNewKey = $priceNewKey;
    }
    
    
    /**
     * 筛选数据
     */
    public function screenData(array $array, $xianGou = 0)
    {
        if (empty($array) || !is_array($array))
        {
            return array();
        }

        $array['title'] = isset($_POST['title']) ? $_POST['title'] : null;


        if($array['class_sub_id']) {
            $where['class_id'] = $_GET['class_sub_id'];
        }
        if($array['class_id']){
            $where['class_fid'] = $_GET['class_id'];
        }
        if($array['price'] == 1){
            $where['price_new'] = array('elt',100);
        }
        if($array['price'] == 2){
            $where['price_new'] = array('between','100,500');
        }
        if($array['price'] == 3){
            $where['price_new'] = array('between','500,1000');
        }
        if($array['price'] == 4){
            $where['price_new'] = array('egt',1000);
        }
        if (isset($array['xiangou']))
        {
            $where['xiangou'] = 1;
        }
        if(!empty($array['title'])){
            $where['title'] = array('like',"%".$array['title']."%");
        }

        $where['shangjia'] = array('eq',1);
        $where['xiangou']  = $xianGou;
        $nowPage = isset($_GET['p'])?$_GET['p']:1;
        // page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
        $result = $this->field('id,title,pic_url,price_old, description')->where($where)->order('sort_num ASC')->page($nowPage.','.PAGE_SIZE)->select();
//         foreach ($result as $k => &$v){
//             $v['create_time'] = date('Y-m-d',$v['create_time']);
//         }
        //分页
        $count = $this->where($where)->count('id');		// 查询满足要求的总记录数

        $page = new \Think\Page($count,PAGE_SIZE);		// 实例化分页类 传入总记录数和每页显示的记录数

        $show = $page->show();		// 分页显示输出

        return array('result' =>$result, 'page' => $show);
    }

    /**
     * 查询要购买的商品
     */
    public function getGoods(array $options, array $goodsNumber, $type=0)
    {
        if (!is_array($options) || empty($options) || empty($goodsNumber) || !is_array($goodsNumber)) {
            return array();
        }
        $data = parent::select($options);
        $sumMonery = 0;
        if (!empty($data))
        {
            foreach ($goodsNumber as $goodsId => $goodsNum)
            {
                foreach ($data as $primayKey => &$goods)
                {
                    if ($goodsId === intval($goods['id']))
                    {
                        $goods['goods_num']    = $goodsNum;
                        $goods['total_monery'] = $goods['price_new'] * $goodsNum;
                        $goods['type']         = $type;
                        $sumMonery += $goods['total_monery'];
                    }
                }
            }
        }
        return array('goods_info' => $data, 'total_monery' => $sumMonery);
    }

   

    /**
     * 获取关键词商品数据
     */
    public function getGoodsData(array $data, $checkKey = 'goods_class_id')
    {
        if (empty($data[$checkKey]))
        {
            return array();
        }
        $totalRows = $this->where(array('class_id' => array('in', $data[$checkKey]), 'shangjia' => 1, 'type' => 1, 'title' => array('like', $data['hot_words'])))->count();

        $page = new Page($totalRows, PAGE_SIZE);
        $goods = parent::select(array(
            'where' => array('class_id' => array('in', $data[$checkKey]), 'shangjia' => 1, 'type' => 1,'title' => array('like', $data['hot_words'])),
            'field' => array('id', 'pic_url', 'price_new', 'price_old', 'fanli_jifen', 'title', 'type'),
            'limit' => $page->firstRow. ','. $page->listRows
        ));
        return array('data' => $goods, 'page' => $page->show());
    }

    public function haver($goods_id){
        $image=M('goods_images');
        $count=$image->where(['goods_id'=>$goods_id])->count();
        $num=mt_rand(1,$count-1);
        $pic_url=$image->field('pic_url')->where(['goods_id'=>$goods_id])->limit($num,1)->find();
        return $pic_url['pic_url'];

    }
   
     //查询订单
     public function orderclass(){
         $orderId=M('order')->field('id')->where(['user_id'=>session('user_id')])->select();
         //查询订单中商品种类数量
         $classIds=[];
         foreach($orderId as $k=>$v){
             $count=M('order_goods')->where(['order_id'=>$v['id']])->count();
             if($count==1){
                 $goodsId=M('order_goods')->field('goods_id')->where(['order_id'=>$v['id']])->find();
                 $classId=M('goods')->field('class_id')->where(['id'=>$goodsId['goods_id']])->find();
                 $classIds[]=$classId['class_id'];
             }elseif(!$count){}else{
                 $goodsId=M('order_goods')->field('goods_id')->where(['order_id'=>$v['id']])->select();
                 foreach($goodsId as $v){
                     $classId=M('goods')->field('class_id')->where(['id'=>$v['goods_id']])->find();
                     $classIds[]=$classId['class_id'];
                 }
             }
             $classIds=array_unique($classIds);
         }
         return $classIds;
     }

    //分类商品模块
    public function cheap($fid=0, $num=8){
        $class=M('goods_class')->field('id')->where(['fid'=>$fid])->select();
        $classId=[$fid];
        foreach($class as $k=>$v){
            $classId[]=$v['id'];
        }
        $where['class_id']=['in',$classId];
        $rs=M('goods')->field('id')->where($where)->limit($num)->select();
        $rds=[];
        foreach($rs as $k=>$v){
            $rf=M('goods_images')->where(['goods_id'=>$v['id']])->limit(1)->find();
            $rds[$k]['pic_url']=$rf['pic_url'];
            $rds[$k]['goods_id']=$v['id'];
        }
        return $rds;
    }

    //商品对应图片
    public function goods_image($goodsId=[]){
        
        if (empty($goodsId) || !is_array($goodsId)) {
            return  array();
        }
        
        $obj = M('goods_images');
        foreach($goodsId as $k=>$v){
            $newGoodsimg = $obj->field('pic_url')->where(['goods_id'=>$v['p_id'],'is_thumb' => 1])->limit(1)->find();
            $goodsId[$k]['pic_url'] = $newGoodsimg['pic_url'];
        }

        return $goodsId;
    }

    //单个商品详细信息
    public function single($goodsId){
        // 再说一遍  模型 不能掉模型 你这样  还不如 系在控制器里呢 要在这里用到三个数据库  \\有其他方法的
        $singleGoods=[];
        $a=M('goods')->field('title,brand_id')->where(['id'=>$goodsId])->find();
        $singleGoods['title']=$a['title'];
        $b=M('goods_detail')->field('detail')->where(['goods_id'=>$goodsId])->find();
        $singleGoods['detail']=$b['detail'];
        $attrId=M('goods_attr')->field(['attribute_id'])->where(['goods_id'=>$goodsId])->select();
        if($attrId!=null){
            $infor=[];
            foreach($attrId as $k=>$v){
                $rs=M('goods_attribute')->field('id,p_id,attribute')->where('id='.$v['attribute_id'])->find();
                $infor[]=$rs;
            }
            $fog=$this->genTree($infor);
        }
        $d=M('brand')->field('brand_name')->where(['goods_class_id'=>$a['brand_id']])->find();
        $singleGoods['brand_name']=$d['brand_name'];
        return $fog;
    }
    function genTree($items) {
        foreach ($items as $item)
            $items[$item['p_id']]['son'][$item['id']] = &$items[$item['id']];
        return isset($items[0]['son']) ? $items[0]['son'] : array();
    }

    //商品评论信息
    public function comment($goodsId,$type=''){
        $count=M('comment')->where(['goods_id'=>$goodsId,'cpmment_type'=>$type])->count();
        $page=new Page($count ,2);
        $comments=M('comment')->where(['goods_id'=>$goodsId,'cpmment_type'=>$type])
            ->order('create_time DESC ')->limit($page->firstRow.','.$page->listRows)->select();
        $show       = $page->show();
        return [$comments,$show];
    }
    /**
      * 根据订单子表的数据查询商品数据
      * @return array
      */
     public function getGoodsByChildrenOrderData(array $data)
     {
         if (empty($data))
         {
             return array();
         }
         usort($data, array($this, 'compare'));
         //用户【考虑到用户连续购买同一件商品】【后续优化】
         foreach ($data as $key => &$value)
         {
             $value['goods'] = parent::select(array(
                 'field' => 'price_market,id as goods_id,title,price_member as goods_price,p_id',
                 'where' => array('id' => array('in', $value['goods_id'])),
                 'order' => 'goods_id DESC',
             ));
         }
         usort($data, array($this, 'compareOrder'));
         //处理商品数量
        foreach ($data as $key => &$value)
         {
             if (false !== strpos($value['goods_num'], ','))
             {
                 Tool::addString($value['goods_num']);
                 $goodsNum = Tool::joinString($value['goods_num']);
                  
                 $value['goods_num'] = $goodsNum;
             }
         }
    //处理商品是否评论
        foreach ($data as $key => &$value)
         {
             if (false !== strpos($value['comment'], ','))
             {
                 Tool::addString($value['comment']);
                 $comment = Tool::joinString($value['comment']);
                  
                 $value['comment'] = $comment;
             }
         }
        //处理商品是否评论
        foreach ($data as $key => &$value)
         {
             if (false !== strpos($value['status'], ','))
             {
                 Tool::addString($value['status']);
                 $status = Tool::joinString($value['status']);
                  
                 $value['status'] = $status;
             }
         }
         //移植商品数量到具体商品
         foreach ($data as $key => &$value)
         { 
             if (!empty($value['goods']))
             {
                 foreach ($value['goods'] as $goods => $name)
                 { 
                     if (is_array($value['goods_num']) && array_key_exists($name['goods_id'], $value['goods_num']))
                     {
                         $value['goods'][$goods]['goods_num'] = $value['goods_num'][$name['goods_id']];
                     }
                     else if ($name['goods_id'] === $value['goods_id'])
                     {
                        $value['goods'][$goods]['goods_num'] = $value['goods_num'];
                     }
                     if (is_array($value['comment']) && array_key_exists($name['goods_id'], $value['comment']))
                     {
                         $value['goods'][$goods]['comment'] = $value['comment'][$name['goods_id']];
                     }
                     else if ($name['goods_id'] === $value['goods_id'])
                     {
                        $value['goods'][$goods]['comment'] = $value['comment'];
                     }
                    if (is_array($value['status']) && array_key_exists($name['goods_id'], $value['status']))
                     {
                         $value['goods'][$goods]['status'] = $value['status'][$name['goods_id']];
                     }
                     else if ($name['goods_id'] === $value['goods_id'])
                     {
                        $value['goods'][$goods]['status'] = $value['status'];
                     }
                   $value['goods'][$goods]['images'] = $this->image($name['p_id']); 
                 }
             }
        } 
         return $data;
     }
    
     /**
      * 猜你喜欢 
      */
     public function guessLove(array $classId, $productId, $page = 1, $pageNumber = 10)
     {
         $goods = array();
         $pageCount = $pageCountById = 0;
         
         $limit = ($page - 1) * $pageNumber;
         
         $pWhere = array();
         if (!empty($productId)) {
             $productId = substr($productId, 1);
             $pWhere = [self::$id_d => ['not in', $productId] ];
         }
        
         
         if (!empty($classId[static::$classId_d])) {
             $where = array(
                 static::$pId_d     => array('gt', 0),
                 static::$classId_d => $classId[static::$classId_d]
             );
             
             $where = array_merge($where, $pWhere);
             
             $count = $this->where($where)->count();
             
             $pageCount  = ceil($count /$pageNumber);
             
             //当前页之前有几页
             $goods = $this->getAttribute(array(
                 'field' => array( static::$id_d,static::$title_d,static::$pId_d),
                 'where' => $where,
                 'limit' => $limit.','.$pageNumber,
                 'group' => static::$pId_d
             ));
         }
         
         if (!empty($goods)) {
             foreach ($goods as $key => $value) {
                 if ($value[static::$pId_d] === $_SESSION['goodsPId']) {
                     unset($goods[$key]);
                 }
             }
         }
         
         $productGoods = array();
         if (!empty($productId) ) {
             
            $where = array(
                static::$id_d  => array('in', $productId),
                static::$pId_d => array('gt', 0)
            );
             
             $count = $this->where($where)->count();
              
             $pageCountById  = ceil($count /$pageNumber);
              
             $productGoods = $this->getAttribute(array(
                 'field' => array( static::$id_d,static::$title_d, static::$pId_d),
                 'where' => $where,
                 'limit' => $limit.','.$pageNumber,
                 'group' => static::$pId_d
             ));
         }
         $this->pageCount = $pageCount < $pageCountById ? $pageCount : $pageCountById;
         
         return array_merge($goods, $productGoods);
     }
     
     /**
      * @desc 畅销排行
      * @param array  $data     畅销排行数据
      * @param string $splitKey 依据某个字段拼接
      * @return array
      */
     public function getGoodsByOrderCount (array $data, $splitKey)
     {
         if (empty($data) || !is_string($splitKey)) {
             return array();
         }
         
         $field  = array(
             self::$id_d,
             self::$title_d,
             self::$priceMarket_d,
             self::$priceMember_d,
             self::$description_d,
             self::$pId_d
         );
         
         $data = $this->getDataByOtherModel($data, $splitKey, $field, static::$id_d);
         
         return $data;
     }
     
     /**
      * 根据品牌 获取数据 [当结果集 只有几十条时用 in语句 比 join 快
      */
     public function getGoodsDataByBrand (array $data, $splt, $limit = 3)
     {
        if (!$this->isEmpty($data) || !is_string($splt)) {
            return array();
        }
        
        $idString = Tool::characterJoin($data, $splt);
        
        if (empty($idString)) {
            return array();
        }
        //select a.* from abc_number_prop a inner join abc_number_phone b on a.number_id = b.number_id where phone = '82306839';
        
        $goods = $this->field(array(
                self::$id_d,
                self::$brandId_d,
                self::$classId_d,
                self::$priceMember_d,
                self::$priceMarket_d,
                self::$title_d,
                self::$pId_d,
                'SUM('.self::$pId_d.') as p'
            ))->where(self::$brandId_d .' in ('.$idString.') and '.self::$pId_d .' >0')->group(self::$pId_d)->limit($limit)->select();
        
        if (empty($goods)) {
            return array();
        }
       
        
        return $goods;
     }
     
     
     /**
      * 获取商品单张图片
      * @param  integer $id 商品ID
      * @return string
      */
     public function image($goods_id)
     {
        if (empty($goods_id)) {
            return false;
        }
        $parent = $this->field('id,p_id')->find($goods_id);
        $p_id   = $parent['p_id'];
        if ($p_id < 1) {
            $p_id = $goods_id;
        }
        $img = M('goods_images')->field('pic_url')->where(['goods_id' => $p_id])->find();
        return $img['pic_url'];
     }


    /**
     * 获取子商品具体规格规格
     * @param  int $goods_id 商品ID
     * @return array
     */
    public function spec($goods_id)
    {
        $spec = M('specGoodsPrice')->field('key,price')->where('goods_id='.$goods_id)->find();
        if (empty($spec)) {
            return array();
        }
        $keys  = implode(explode('_', $spec['key']), ',');
        $field = 'i.item, i.spec_id, g.name, i.id as item_id';
        $data  = M('goodsSpec')->alias('g')->join('__GOODS_SPEC_ITEM__ as i ON i.spec_id=g.id')
            ->field($field)->where("i.id in ($keys)")->select();
        return $data;
    }   
    /**
     * 根据 商品编号 获取数据 
     * @param int $goodsId 商品编号
     * @return array;
     */
    public function getGoodsById ($goodsId, array $goodsData)
    {
        if ( ($goodsId = intval($goodsId) ) === 0 || empty($_POST['goods_num'])) {
            return array();
        }
        
        if (!empty($goodsData)) {
            
            return $this->getGoodsByPromotion($goodsData, $this->split);
        }
        
        
        $data =  $this->getAttribute([
            'field' => [self::$updateTime_d],
            'where' => [self::$id_d => $goodsId]
        ], true);
        
        if (empty($data)) {
            $this->error = '暂无商品';
            return array();
        }
        
        //计算总价
        $sum = 0;
        
        $weight = 0.0;
        
        foreach ($data as $key => $value) {
            $sum    += $value[self::$priceMember_d]*$_POST['goods_num'];
            $weight += $value[self::$weight_d]*$_POST['goods_num'];
        }
        
        self::$totalMonery = $sum;
        
        $_SESSION['user_goods_number'] = $_POST['goods_num'];
        
        $_SESSION['user_goods_weight'] = $weight;
        
        $_SESSION['user_goods_monery'] = $sum;
        
        return $data;
        
    }
    
    /**
     * 获取商品信息
     * @param int $id
     * @return array
     */
    public function getGoodsContentById ($id)
    {

        if ( ($goodsId = intval($id) ) === 0 ) {
            return array();
        }
        
        $data =  $this->getAttribute([
            'field' => [self::$updateTime_d],
            'where' => [self::$id_d => $goodsId]
        ], true, 'find');
        
        if (empty($data)) {
            $this->error = '暂无商品';
            return array();
        }
        
        return $data;
    }
    
    /**
     * 根据促销信息 获取商品信息 
     */
    public function getGoodsByPromotion(array $data, $split)
    {
        if (!$this->isEmpty($data) || empty($split)) {
            $this->error = '数据错误';
            return array();
        }
        $data = $this->getDataByOtherModel($data, $split, [
            self::$id_d. self::DBAS.$split,
            self::$description_d,
            self::$pId_d,
            self::$title_d,
        ], self::$id_d);
        
        if (empty($data)) {
            return array();
        }
        $total = 0;
        foreach ($data as $key => &$value) {
            $value[self::$id_d] = $value[$split];
            
            $value[self::$priceMember_d] = $value['price_new'];
            $total += ($value[self::$priceMember_d] * $_POST['goods_num']);
            unset($data[$key][$split]);
            unset($data[$key]['price_new']);
        }
        
        self::$totalMonery += sprintf('%01.2f',$total*$_SESSION['discount']/100);
        
        return $data;
    }
    
    /**
     * 购物车获取商品信息 
     * @param array $data 
     * @return array
     */
    public function getGoodsByCartArray(array $data, $split)
    {
        if (!$this->isEmpty($data) || empty($split)) {
            $this->error = '商品数据错误';
            return array();
        }
        
        $field = [
            self::$id_d,
            self::$title_d,
            self::$pId_d,
            self::$description_d,
            self::$status_d,
            self::$weight_d
        ];
        
        $data = $this->getDataByOtherModel($data, $split, $field, self::$id_d);
        
        $weight = 0;
        
        $total= 0;
        $number = 0;
        
        foreach ($data as $key => $value) {
        
            $weight += $value[self::$weight_d]* $value[$this->goodsNumKey];
        
            $total  +=  $value[$this->priceNewKey]*$value[$this->goodsNumKey];
            
            $number +=  $value[$this->goodsNumKey];
            
        }
        $_SESSION['user_goods_number'] = $number;
        $_SESSION['user_goods_weight'] = $weight;
        
        $_SESSION['user_goods_monery'] = $total;
        
        return $data;
        
    }
    
     //查出所有商品
    public function getGoodsAll(){
        $_GET['p'] = empty($_GET['p'])?0:$_GET['p'];
        $where['p_id'] = array('neq',0);
        $res = M('Goods')->field('id,code,brand_id,title,price_market,price_member,p_id')->where($where)->page($_GET['p'].',20')->select();
        $count = M('Goods')->count();
        $Page = new \Think\Page($count,20);      // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();      // 分页显示输出
        return array('res' =>$res, 'page' => $show);
    }
    //根据商品id查询商品信息
    public function getGoodsByGoodsId($goods_id){
        if (empty($goods_id)) {
            return false;
        }
        $where['id'] = $goods_id;
        $field = 'id as goods_id,title,price_member,p_id,class_id,integral_rebate,status';
        $res = M('Goods')->field($field)->where($where)->find();
        return $res;
    }
     
    /**
     * 检测 价格 
     */
    public function validatePrice (array $post, $split, $monery)
    {
        if (!$this->isEmpty($post) || !is_string($split) || ($monery = floatval($monery)) === 0) {
            return false;
        }
        
      
        
        $idString = Tool::characterJoin($post, $split);
        
        if (empty($idString)) {
            return false;
        }
        
        $goodsData = $this->field(self::$priceMember_d.','.self::$id_d)->where(self::$id_d.' in ('.$idString.')')->select();
        
        if (empty($goodsData)) {
            return false;
        }
        
        usort($goodsData, array($this, 'compareUserId')); //排序
        usort($post, array($this, 'compare'));//排序
        
        $curret = array();
        
        foreach ($goodsData as $key => $value) {
            $curret = current($post);//因为这里是一一对应的关系 所以
            if ((in_array($value[self::$id_d], $curret, true)) && ( empty($curret['goods_price']) || $value[self::$priceMember_d] !== $curret['goods_price']) ) {
                return false;
            }
        }
        
        //统计总价
        $total = 0;
        foreach ($post as $value) {
            $total += $value['goods_price'];
        }
        
        $this->totalMonery = $total;
        
        return true;
    }
    
    
    /**
     * 获取数据 商品模块最新上架
     */
    public function getGoodsShelevs ($num)
    {
        
       if ( ($num = intval($num)) === 0 ) {
           return array();
       }
        
        return $this->getAttribute(array(
            'field' => array(
                self::$id_d,
                self::$priceMarket_d,
                self::$priceMember_d,
                self::$pId_d,
                self::$title_d,
            ),
            'where' => array(
                self::$pId_d => array('gt', 0),
                self::$shelves_d => 1,
            ),
            'order' => self::$createTime_d.BaseModel::DESC,
            'group' => self::$pId_d,
            'limit' => $num
        ));
    }
    
    //根据data查询商品信息
    public function getGoodsByData(array $data){
        if(empty($data)) {   
            return false;
        }
        foreach ($data as $key => $value) {
            $where['id'] = $value['goods_id'];
            $goods = M('Goods')->field('id,title,price_market,price_member,p_id,status as goods_status')->where($where)->find();
            $data[$key]['title'] = $goods['title'];
            $data[$key]['price_market'] = $goods['price_market'];
            $data[$key]['price_member'] = $goods['price_member'];
            $data[$key]['goods_status'] = $goods['goods_status'];
            $data[$key]['p_id'] = $goods['p_id'];
            if (empty($data[$key]['title'])) {
                unset($data[$key]);
            }
        }
        return $data; 
    }
    // 根据订单信息查询商品信息
    public function getGoodsByOrder(array $order){
        if(empty($order)) {   
            return false;
        }
        foreach ($order as $k => $v) {
            foreach ($v['goods'] as $key => $value) {
                $where['id'] = $value['goods_id'];
                $goods = M('Goods')->field('id,title,price_market,price_member,p_id')->where($where)->find();
                $order[$k]['goods'][$key]['title'] = $goods['title'];
                $order[$k]['goods'][$key]['price_market'] = $goods['price_market'];
                $order[$k]['goods'][$key]['price_member'] = $goods['price_member'];
//                 $order[$k]['goods'][$key]['integral_rebate'] = $goods['integral_rebate'];
                $order[$k]['goods'][$key]['p_id'] = $goods['p_id'];
            }
        }
        return $order; 
    }


    /**
     * 获取商品配件
     * @param  integer $goods_id 商品id,应该传入商品主id
     * @return array
     */
    public function accessories($goods_id)
    {
        $field = 'id,goods_id,sub_ids,status,create_time,update_time';
        $info  = M('goodsAccessories')->field($field)->where(['goods_id'=>$goods_id])->find();
        if (empty($info)) {
            return $info;
        }
        $list  = M('goods')->field('id, title, price_member as price')->where(['id'=>['in', $info['sub_ids']]])->select();
        if (is_array($list) && count($list) > 0) {
            foreach ($list as &$val) {
                $val['pic_url'] = D('goods')->image($val['id']);
            }
        }
        return $list;
    }

    //根据分类查出商品信息 
    public function getGoodsByClassId($class_id,$page){
        if (empty($class_id)) {
            return false;
        }                
        $where['class_id'] = $class_id;
        $where['p_id'] = array('neq','0');
        $count = $this->where($where)->count();
        if ($page>$count) {
            $page = $page-6;
        }
        if ($page<'0') {
            $page = '0';
        }
        $field = 'id as goods_id,price_member,class_id,title,p_id';
        $data = $this->field($field)->where($where)->limit($page,6)->select();
        return array('data'=>$data,'count'=>$count,'page'=>$page,'class_id'=>$class_id);
    }


    /**
     * 最佳组合
     * @param  integer $goods_id 商品id,应该传入商品的主id
     * @return array
     */
    public function combo($goods_id)
    {
        $field = 'id,goods_id,sub_ids,create_time,update_time';
        $info = M('goodsCombo')->field($field)->where(['goods_id'=>$goods_id])->find();
        if (empty($info)) {
            return [];
        }
        $list = M('goods')->field('id, title, price_member as price')->where(['id'=>['in', $info['sub_ids']]])->select();
        
        if (is_array($list) && count($list)>0) {
            foreach ($list as &$val) {
                $val['pic_url'] = $this->image($val['id']);
            }
        }
        return $list;
    }
   

    /**
     * 优惠套餐
     * @param  integer $goods_id 商品id
     * @param  integer $number   获取数量
     * @return array
     */
    public function package($goods_id, $number = 1)
    {
        // 获取商品所属于的套餐列表
        $ids = M('goodsPackageSub')->field('package_id')->where(['goods_id'=>$goods_id])->select();
        foreach ($ids as $id) {
            $str .= ','.$id['package_id'];
        }
        // 获取套餐以及商品列表
        $list = M('goodsPackage')->field('id as package_id, total, discount')
            ->where(['id' => ['in', trim($str, ',')]])->limit($number)->select();

        $field = 'p.goods_id,p.discount,g.price_member as price,g.title,g.stock';
        foreach ($list as &$vo) {

            // 获取套餐下的商品
            $temp = M('goodsPackageSub')->field($field)->alias('p')->join('__GOODS__ as g')
                ->where('g.id=p.goods_id AND package_id='.$vo['package_id'])->select();

            // 获取商品其他形象
            foreach ($temp as &$vo1) {
                $vo1['pic_url'] = $this->image($vo1['goods_id']);
                $vo1['spec']    = $this->spec($vo1['goods_id']);
            }
            $vo['sub'] = $temp;
        }
        return $list;
    }


    /**
     * 获取商品的同类商品,就是同属于改商品的父商品的子商品 ID 列表
     * @param  integer $goods_id 商品
     * @param  boolean $has_p    是否包含父商品
     * @return array
     */
    public function classGoods($goods_id, $has_p = true)
    {
        if ($goods_id < 1) {
            return false;   
        }
        $info = $this->field('id, p_id')->find($goods_id);
        $p_id = empty($info['p_id'])? $goods_id : $info['p_id'];
        $list = $this->field('id')->where(['p_id' => $p_id])->select();
        foreach ($list as $val) {
            $ids[] = $val['id'];
        }
        if ($has_p) {
            $ids[] = $p_id;
        }
        return $ids;
    }
    //热卖精选
    public function hot_buy(){
        $where['latest_promotion'] = 2;
        $res = $this->field('id as goods_id,title,price_member,p_id')->where($where)->limit(5)->select();
        return $res;
    }
    
    //获取上架产品
    public function getShelve($goodsId) 
    {
        if ( ($goodsId = intval($goodsId)) === 0 ) {
            return array();
        }
        
        return $this
                ->field(self::$updateTime_d.','.self::$createTime_d, true)
                ->where(self::$id_d.'=%d and '.self::$shelves_d.' = 1', $goodsId)
                ->find();
    }
    
    /**
     * 获取子类商品【上架的】 
     * @param int $pId 商品父级编号
     * @return array
     */
    public function getChildrenGoods ($pId)
    {
        if ( !is_numeric($pId)) {
            return array();
        }
        
        $field = [
            self::$id_d,
            self::$title_d,
            self::$brandId_d,
            self::$pId_d,
            self::$description_d,
            self::$classId_d,
            self::$priceMarket_d,
            self::$priceMember_d,
            self::$pId_d
        ];
        return $this->field($field)->where(self::$pId_d.'= %d and '.self::$shelves_d.'= 1', $pId)->select();
    }
    
    /**
     * 根据促销信息 获取商品数据
     * @param array $data  促销数据
     * @param string $split 关联字段
     * @return array
     */
    public function getGoodsByPoop (array $data, $split)
    {
        
        if (!$this->isEmpty($data)) {
            return array();
        }
        
        $goodsColum =  [
            static::$id_d ,
            static::$title_d,
            static::$stock_d,
            static::$priceMarket_d,
            static::$priceMember_d,
            static::$pId_d
        ];
        
        $goodsData = $this->getDataByOtherModel($data, $split, $goodsColum, static::$id_d);
        
        return $goodsData;
    }
    
    /**
     * 处理尾货清仓数据
     * @param int   $status     尾货清仓折扣类型
     * @param float $expression 折扣值
     * @return array;
     */
    public function parseGoodsByPoopClear ($status, $expression)
    {
        if ($expression < 0) {
            return array();
        }
        $goodsData = $this->getShelve($this->setGoodsId);
        
        if (empty($goodsData)) {
            return array();
        }
        
        
        $method = 'getPromotionType'.$status;
        
        $this->expression = $expression;
        
        $goodsData[self::$priceMember_d] = $this->$method($goodsData[self::$priceMember_d]);
        
        return $goodsData;
    }
    
    public function getGoodsByClassSon(array $where)
    {
        if (empty($where)) {
            return array();
        }
        
        $sql = <<<aaa
         SELECT a1.* FROM db_goods a1
            INNER JOIN (
                SELECT a.p_id FROM db_goods a
                LEFT JOIN db_goods b
                ON a.id = b.p_id
                GROUP BY a.p_id
				HAVING COUNT(a.p_id) <= 1
            ) b1
            ON a1.p_id = b1.p_id
            where   a1.class_id in ({$where[self::$classId_d]}) and a1.shelves = {$where[self::$shelves_d]} and a1.status=0
        ORDER BY a1.id , b1.p_id  DESC ;
aaa;
            
        $sql = <<<aaa
            SELECT*FROM db_goods as a
            WHERE 1>(SELECT COUNT(*)FROM db_goods WHERE p_id=a.p_id  AND id >a.id ) AND a.p_id > 0 and a.class_id in ({$where[self::$classId_d]})
            and a.shelves = {$where[self::$shelves_d]} and a.status=0
            ORDER BY a.id DESC;
aaa;
        
        return $this->query($sql);
    }
    
    
    /**
     * @param number $setGoodsId
     */
    public function setSetGoodsId($setGoodsId)
    {
        $this->setGoodsId = $setGoodsId;
    }
}