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

namespace Common\Model;

use Common\Tool\Tool;
use Common\Tool\Extend\PregPicture;

/**
 * 商品详情model 
 * @author 王强
 * @version 1.0.1
 */
class GoodsDetailModel extends BaseModel
{
    
    private static  $obj;

	public static $id_d;	//主键编号

	public static $goodsId_d;	//商品编号

	public static $detail_d;	//详情

	public static $updateTime_d;	//更新时间【标记更新】

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    /**
     * 添加前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected function _before_insert(& $data, $options)
    {
        $data[self::$updateTime_d] = time();
        
        return $data;
    }
    
    /**
     * 更新前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected function _before_update(& $data, $options)
    {
        $data[self::$updateTime_d] = time();
    
        return $data;
    }
    
    /**
     * 保存商品详情 
     * @param array $data
     * @param string $key
     * @return boolean
     */
    public function saveData (array $data, $key)
    {
        if (empty($data)) {
            $this->rollback();
            return false;
        }
        
        $status = $this->where(array(self::$goodsId_d =>$_POST[$key]))->save($data);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        
        $this->commit();
        
        return $status;
    }
    
    /**
     * 删除商品 详情
     * @param integer $id
     * @return boolean
     */
    public function deleteGoodsById ($id) 
    {
        if (($id = intval($id)) === 0) {
            $this->rollback();
            return false;
        }
        
        $obj = $this->where(self::$goodsId_d.' = %d', $id);
        
        $detail = $this->where(self::$goodsId_d.' = %d', $id)->getField(self::$detail_d);
        
        
        // 筛选图文详情 删除图片
        Tool::partten(htmlspecialchars_decode($detail), PregPicture::class);
       
        $status = $this->where(self::$goodsId_d.' = %d', $id)->delete();
      
        return $this->traceStation($status);
        
    }
}