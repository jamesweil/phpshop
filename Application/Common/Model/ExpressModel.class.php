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

/**
 * 快递公司模型 
 */
class ExpressModel extends BaseModel implements IsExitsModel
{
    private static $obj;

	public static $id_d;	//索引ID

	public static $name_d;	//公司名称

	public static $status_d;	//状态1启用 2弃用

	public static $code_d;	//编号

	public static $letter_d;	//首字母

	public static $order_d;	//1常用0不常用

	public static $url_d;	//公司网址

	public static $ztState_d;	//是否支持服务站配送0否1是

	public static $tel_d;	//客服电话


	public static $discount_d;	//折扣

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    /**
     * 获取默认开启的快递 五秒钟缓存
     */
    public function getDefaultOpen( $isAddEnglish = true)
    {
        
        $data = S('expressData');
        
        if (empty($data)) {
            
            $data = $this->where(self::$status_d .' = 1')->getField(self::$id_d.','.self::$name_d.','.self::$discount_d);
            
            if (empty($data)) {
                return array();
            }
            
            if ($isAddEnglish) {
            
                foreach ($data as $key => & $value) {
                    $value = Tool::getFirstEnglish($value).' '.$value;
                }
            }
            
            S('expressData', $data, 5);
        }
        
        return $data;
    }
    /**
     * 根据其他模型数据 获取对应的数据
     * @param array $data
     * @param BaseModel $model
     * @param string $cacheKey
     * @return mixed|object
     */
    public function getExpressData(array $data, BaseModel $model, $cacheKey = 'EXPRESS_CACHE_DATA')
    {
        if (!$this->isEmpty($data) || !($model instanceof BaseModel)) {
            return array();
        }
        
        $expressData = S($cacheKey);
        
        if (empty($expressData)) {
            $expressData = $this->getDataByOtherModel($data, $model::$expId_d, [
                self::$id_d,
                self::$name_d,
            ], self::$id_d);
        
            if (empty($expressData)) {
                return array();
            }
            S($cacheKey, $expressData, 6);
        }
        return $expressData;
    }
    
    /**
     * 获取快递名字 
     */
    public function getExpressTitle($id)
    {
        if ( ($id = intval($id)) === 0)
        {
            return null;
        }
        return $this->where(self::$id_d.' = '.$id)->getField(self::$name_d);
    }
    
    /**
     * 获取 快递表 id 及其名称
     */
    public function getIdAndName ()
    {
        $data = S('EXPRESS_KEY_H_');
        
        if (empty($data)) {
            $data = $this->where(self::$status_d .' = 1')->getField(self::$id_d.','.self::$name_d);
        }  else {
            return $data;
        }
        
        S('EXPRESS_KEY_H_', $data, 100);
        
        return $data;
    }
    /**
     * {@inheritDoc}
     * @see \Common\Model\IsExitsModel::IsExits()
     */
    public function IsExits($post)
    {
        // TODO Auto-generated method stub
        
        if (empty($post)) {//空即是存在
            return true;
        }
        
        return $this->where(self::$name_d.'="%s"', $post)->getField(self::$id_d);
    }
}