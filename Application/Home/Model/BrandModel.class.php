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

class BrandModel extends BaseModel
{   
    private static  $obj;

	public static $id_d;	//

	public static $brandName_d;	//品牌名称

	public static $goodsClass_id_d;	//所属商品分类编号

	public static $brandLogo_d;	//品牌图片

	public static $brandDescription_d;	//品牌描述

	public static $recommend_d;	//1推荐0不推荐

	public static $createTime_d;	//

	public static $updateTime_d;	//

	public static $letter_d;	//品牌 字母

	public static $brandBanner_d;	//品牌banner

       
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    /**
     * 获取相应状态的品牌
     */
    public function getDataByStatus ($limit = 30)
    {
        if ( !is_numeric($limit) ) {
            return array();
        }
        
        return $hotBrandData = $this->getAttribute(array(
            'field' => array(
                BrandModel::$updateTime_d,
                BrandModel::$createTime_d,
            ),
            'limit' => $limit
        ), true);
    }
    
    /**
     * 生成 对应的品牌 +首字母 
     */
    public function getBrandBuild ()
    {
        
        $data = S('dataBrand');
        
        if (empty($data)) {
        
            $data = $this->getField(self::$id_d.','.self::$brandName_d);
            
            if (empty($data)) {
                return array();
            }
            
            foreach ($data as $key => & $value)
            {
                $value = Tool::getFirstEnglish($value).' '.$value;
            }
            
            S('dataBrand', $data, 180);
        }
        return (array)$data;
    }
    
    /**
     * 根据对应的 首字母 寻找 品牌 
     * @param string $english 首字母
     * @param array $receive  接受数组
     * @return array;
     */
    public function getBrandEnglish ($english, array & $receive) 
    {
        
        $data = $this->getBrandBuild();
       
        if (empty($data)) {
            return array();
        }
     
        foreach ($data as $key => $value)
        {
            if (0 === strpos($value, $english)) {
                $receive[$key] = $value;
                
            }
        }
        return $receive;
        
    }
    //根据商品查询对应的品牌
    public function getBrandByData(array $data){
        if (empty($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $where['id'] = $value['brand_id'];//品牌id
            $brand = M('Brand')->field('id,brand_name')->where($where)->find();
            $data[$key]['brand'] = $brand['brand_name'];
        }
        return $data;
    }
    
}
