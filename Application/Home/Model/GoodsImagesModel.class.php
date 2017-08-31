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

class GoodsImagesModel extends BaseModel
{
    private static $obj;
    

	public static $id_d;

	public static $goodsId_d;

	public static $picUrl_d;

	public static $status_d;


	public static $isThumb_d;	//缩略图【1是 0否】

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return !(self::$obj instanceof $class) ? self::$obj = new self() : self::$obj;
    }

    /**
     * 商品相册
     */
    public  function getGoodsPictureAlbum($id)
    {
        if (!is_numeric($id) || $id == 0) {
            return array();
        }
    
        $data  = $this->getAttribute(array(
            'field' => array(self::$goodsId_d, self::$picUrl_d, self::$id_d),
            'where' => array(self::$goodsId_d => $id, self::$status_d => 1, self::$isThumb_d => 0)
        ));
        return $data;
    }
    
  
    /** 
     * @desc 热卖推荐
     * @param array $data
     * @param string $splitKey
     * @param array|string $field
     * @param string $where
     * @return array;
     */
    public function hotRecommendation (array $data, $splitKey) 
    {
        if (empty($data) || !is_string($splitKey)) {
            return array();
        }
        
        
        $length   = count($data);
        
        $noImages = array();
        if ($length > 3 ) {
            
            $noImages = array_splice($data, 2);
        } 
        
        $data = $this->getImageById($data, $splitKey);
        
        return array_merge($data, $noImages);
    }
     //查询商品图片
    public function getGoodsImageByData(array $data){
        if(empty($data)) {   
            return false;
        }
        foreach ($data as $key => $value) {
            if ($value['p_id'] == 0) {
                $where['goods_id'] = $value['goods_id'];
            }else{
                $where['goods_id'] = $value['p_id'];
            }            
            $img = M('goods_images')->field('id,pic_url')->where($where)->find();
            $data[$key]['images'] = $img['pic_url']; 
        }
        return $data; 
    }
     //查询商品图片
    public function getGoodsImageByOrder(array $order){
        if(empty($order)) {   
            return false;
        }
        foreach ($order as $key => $value) {
            foreach ($value['goods'] as $k => $v) {
                $where['goods_id'] = $v['p_id'];
                $img = M('goods_images')->field('id,pic_url')->where($where)->find();
                $order[$key]['goods'][$k]['images'] = $img['pic_url'];
            } 
        }
        return $order; 
    }
     //查询商品图片
    public function getGoodsImageByGoods(array $goods){
        if(empty($goods)) {   
            return false;
        }            
        $where['goods_id'] = $goods['p_id'];
        $img = M('goods_images')->field('id,pic_url')->where($where)->find();
        $goods['images'] = $img['pic_url'];       
        return $goods; 
    }
    
    /**
     * 获取图片 
     */
    public function getImageById (array $goods, $split)
    {
        if (empty($goods)) {
            return array();
        }
        
        $ids = Tool::characterJoin($goods, $split);
        
        $ids = str_replace('"', null, $ids);
        if (empty($ids)) {
            return $goods;
        }
        
        //分组依据列
        $data = $this
                ->field(self::$goodsId_d.self::DBAS.$split.', MAX('.self::$picUrl_d.') as '.self::$picUrl_d)
                ->where(self::$isThumb_d.' = 0 and '.self::$goodsId_d.' in (%s)', $ids)
                ->group(self::$goodsId_d)
                ->select();
        
        if (empty($data)) {
            return $goods;
        }
        
        $data = $this->covertKeyById($data, $split);
        
        $temp = [];
        
        foreach ($goods as $key => & $value)
        {
            $temp = $data[$value[$split]][self::$picUrl_d];
            $value[self::$picUrl_d] = empty($temp) ? '' : $temp;
        }
        
        return $goods;
    }
}