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

namespace Admin\Model;

use Common\Model\BaseModel;
use Common\Tool\Tool;
use Common\Tool\Extend\UnlinkPicture;
use Common\Model\IsExitsModel;

/**
 * 品牌模型
 * @author Administrator
 * @version 1.0.0
 */
class BrandModel extends BaseModel implements IsExitsModel
{
    /**
     * 类实例承载着
     * @var BrandModel
     */
    private static  $obj;

	public static $id_d;	//主键编号

	public static $brandName_d;	//品牌名称

	public static $goodsClassId_d;	//所属商品分类编号

	public static $brandLogo_d;	//品牌图片

	public static $brandDescription_d;	//品牌描述

	public static $recommend_d;	//1推荐0不推荐

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

	public static $letter_d;	//品牌 字母

	public static $brandBanner_d;	//品牌banner

    /**
     * 取得类的实例
     * @return \Admin\Model\BrandModel
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
     
    /**
     * 重写 添加
     * {@inheritDoc}
     * @see \Common\Model\BaseModel::add()
     */
    public function add($data='',$options=array(),$replace=false)
    {
        if (empty($data))
        {
            return false;
        }
       
        $isExits = $this->IsExits($data[static::$brandName_d]);
        
        if ($isExits === true) {// 检测品牌名称是否存在
            $this->error = '已存在该品牌';
            return false;
        }
        
        if (!empty($data['cat_id']))
        {
            $data[static::$goodsClassId_d] = $data['cat_id'];
            
            unset($data['cat_id']);
        }
        
        $data = $this->create($data);
         
        return parent::add($data, $options, $replace);
    }
    
    /**
     * 保存品牌
     * @param array $data
     * @return boolean
     */
    public function saveBrand(array $data)
    {
        if (empty($data))
        {
            return false;
        }
        
        if (is_numeric($data['cat_id']) && $data['cat_id'] != 0)
        {
            $data[static::$goodsClassId_d] = $data['cat_id'];
        
            unset($data['cat_id']);
        }
        $data = $this->create($data);
        //图片是否和原来一样
        $imge = $this->getAttribute(array(
            'field' => array(static::$brandLogo_d),
            'where' => array(static::$id_d => $data[static::$id_d])
        ), false, 'find');
        
        if (!empty($imge) && !empty($data[static::$brandLogo_d]) && $imge[static::$brandLogo_d] != $data[static::$brandLogo_d])
        {
            $status = Tool::partten(array( $imge[static::$brandLogo_d] ), UnlinkPicture::class);
        }
        
        return parent::save($data);
    }
    
    /**
     * 删除品牌
     * {@inheritDoc}
     * @see \Think\Model::delete()
     */
    public function delete( $data=array())
    {
        if ( empty($data) || !is_array($data))
        {
            return false;
        }
        
        $imge = $this->getAttribute(array(
            'field' => array(static::$brandLogo_d),
            'where' => array(static::$id_d => $data[static::$id_d])
        ), false, 'find');
        $status = Tool::partten(array( $imge[static::$brandLogo_d] ), UnlinkPicture::class);
        
        return parent::delete(array('where' => array(static::$id_d => $data[static::$id_d])));
    }
    
    /**
     * 添加前 数据操作
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected  function _before_insert(& $data, $options)
    {
        $data[static::$createTime_d] = time();
         
        $data[static::$updateTime_d] = time();
         
        return $data;
    }
    
    /**
     * 根据商品名称 查询数据 
     * @param string $brandName 品牌名字
     * @return array
     */
    public function getBrandByName ($brandName)
    {
        if (empty($brandName)) {
            return array();
        }
        
        return $this->field(static::$updateTime_d.','.static::$createTime_d, true)->where(static::$brandName_d.' = "%s"', $brandName)->find();
    }
    
    /**
     * 更新前数据操作
     * {@inheritDoc}
     * @see \Think\Model::_before_update()
     */
    protected function _before_update(& $data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$brandName_d, $data[static::$brandName_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$brandName_d].'】';
            return false;   
        }
        $data[static::$updateTime_d] = time();
    
        return $data;
    }
    /**
     * 是否存在该品牌
     * {@inheritDoc}
     * @see \Common\Model\IsExitsModel::IsExits()
     */
    public function IsExits($post)
    {
        // TODO Auto-generated method stub
        
        if (empty($post)) {
            return false;
        }
        
        $data = $this->getBrandByName($post);
        return empty($data) ? false : true;
    }

}
