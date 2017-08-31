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

use Common\Model\BaseModel;
use Common\Tool\Tool;
use Think\Hook;

/**
 * 促销 
 */
class PromotionGoodsModel extends BaseModel
{
    private static $obj;

	public static $id_d;

	public static $promId_d;

	public static $goodsId_d;


	public static $startTime_d;

	public static $endTime_d;

	public static $activityPrice_d;

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return !(self::$obj instanceof $class) ? self::$obj = new self() : self::$obj;
    }
    
    /**
     * @desc  获取促销信息
     * @param int $id 商品编号
     * @return array
     */
    public function getPromotionByGoodsId($id)
    {
        if ($id == 0 || !is_numeric($id)) {
            return array();
        }
        
        $idArray = $this->getAttribute(array(
            'field' => array(
                self::$promId_d,
                self::$goodsId_d
            ),
            'where' => array(
                self::$goodsId_d => $id
            )
        ));
        
        return $idArray;
    }
    
    /**
     * 获取促销商品 
     */
    public function getPromotionGoods(array $goods, $splitKey)
    {
        if (!$this->isEmpty($goods) || empty($splitKey)) {
            return array();
        }
        
        $data = $this->getDataByOtherModel($goods, $splitKey, [
            self::$goodsId_d,
            self::$promId_d,
        ], self::$goodsId_d);
      
        return $data;
    }
    /**
     * 获取促销数据
     */
    public function getPromotionData(array $post, $split)
    {
        if (empty($post[$split]) || empty($split)) {
            return array();
        }
        
        $data = $this->field(self::$promId_d.','.self::$goodsId_d)->where(self::$goodsId_d.'=%d', (int)$post[$split])->select();
        
        return $data;
        
    }
    

    /**
     * 商品促销
     * @param string $type_name 商品促销的名字
     * @param integer $goods_class_num 商品分类的数目
     * @param integer $goods_num 商品的数目
     * @return mixed array  返回的数据
     */
    public function chooseType($type_name,$goods_class_num,$goods_num){
        $goodsClassModel=D("GoodsClass");
        $goodsModel = M("Goods");
        $choose_id = M("PromGoods")->where(['name'=>$type_name])->getField("id");
        $choose_goods_ids = $this->where(['prom_id'=>$choose_id])->getField("goods_id",true);
        $choose_goods_ids?$cont['id']=['in',$choose_goods_ids]:false;
        $topId=$goodsClassModel->where(['fid'=>0,'is_show_nav'=>0])->limit($goods_class_num)->getField("id,class_name");
        $hot_choose=[];
        foreach($topId as $k=>$v){
            $result=$goodsClassModel->field('fid,id')->select();
            //获取每种分类的id
            $change_num=$goodsClassModel->selectClass($result,$k);
            $change_num = trim($change_num,',');
            $change_num=explode(',',$change_num);
            array_unshift($change_num,(string)$k);
            $change_num = array_filter($change_num);
            $change_num? $cont['class_id']=['in',$change_num]:false;
            $cont['p_id'] = ['gt',0];
            $hot_choose[$v]=$goodsModel->field('id,title,p_id,price_market,price_member,p_id')->where($cont)->order('create_time DESC')->group("p_id")->limit($goods_num)->select();
        }
        //查询对应id的商品相册
        return $this->choose_picture($hot_choose);


    }

    /**
     * 查询对应的商品id相册
     * @param array $hot_choose 商品的一些信息
     * @return  mixed  商品信息
     */
    protected function choose_picture($hot_choose){

        $goodsImagesModel = M("GoodsImages");
        foreach($hot_choose as &$hot_choose_one){
            foreach($hot_choose_one as &$hot_choose_two){
                $pic_url = $goodsImagesModel->where(['goods_id'=>$hot_choose_two['p_id']])->limit(1)->find();
                $hot_choose_two['pic_url'] = $pic_url['pic_url'];
            }
        }

        unset($hot_choose_one);
        unset($hot_choose_two);
        return $hot_choose;
    }

    /**
     * 精选商品
     * @return mixed array 返回的数据
     */
    public function careSelect(){
        //前10名订单
        $cond['db_goods.p_id'] = ['gt',0];
        $cond['db_goods.title']= ['neq',''];
        $order_goods = M("OrderGoods")
                    ->field("db_goods.id,db_goods.price_market,db_goods.title,db_goods.p_id,count(db_order_goods.id)")
                    ->join("db_goods on db_goods.id=db_order_goods.goods_id")
                    ->group('db_order_goods.goods_id')
                    ->where($cond)
                    ->order("count(db_order_goods.id) desc")
                    ->limit(10)
                    ->select();
        //取图片
        $goodsImagesModel = M("GoodsImages");
        foreach($order_goods as &$goods) {
            $pic_url = $goodsImagesModel->where(['goods_id' => $goods['p_id']])->getField("pic_url");
            $goods['pic_url'] = $pic_url;
        }
        unset($goods);
        return $order_goods;
    }
}