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

/**
 * @author 王强 
 * @license Apache
 * @see   BaseModel
 * 【我们保留一切权利】
 */
class SiteModel extends BaseModel
{
    private static $obj;

	public static $id_d;

	public static $ipAddress_d;

	public static $areaId_d;

	public static $siteName_d;

	public static $url_d;

	public static $status_d;

	public static $pId_d;

	public static $createTime_d;

	public static $updateTime_d;
    
	private $selectField = [];
    

	public static $def_d;


	public static $geographical_d;


    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    
    /**
     * 获取数据 
     */
    public function getData()
    {
        $data = S('SITE_CACHE');
        
        $this->selectField = [
            self::$id_d,
            self::$siteName_d,
            self::$areaId_d,
            self::$url_d,
            self::$geographical_d
        ];
        if (empty($data)) {
            $data = $this->field($this->selectField)->where(self::$def_d.'=0')->select();
            
            if (empty($data)) {
                return $data;
            }
            S('SITE_CACHE', $data, 30);
        }
        return $data;
    }
    
    /**
     * 根据 地域处理地区
     */
    public function geographical($data)
    {
        if (!$this->isEmpty($data)) {
            return array();
        }
        
        $parseArray = array();
        
        foreach ($data as $key => $value)
        {
            $parseArray[$value[self::$geographical_d]][] = $value;
        }
       
        return $parseArray;
    }
    
    
    /**
     * 获取默认站点 
     */
    public function getDefault()
    {
        $data = S('SITE_DEFAULT_CACHE');
        
        if (empty($data)) {
            $data = $this->field($this->selectField)->where(self::$def_d.'=1')->find();
        
            if (empty($data)) {
                return array();
            }
            S('SITE_DEFAULT_CACHE', $data, 30);
        }
        return $data;
    }
    
    /**
     * @return the $selectField
     */
    public function getSelectField()
    {
        return $this->selectField;
    }
    
    /**
     * @param multitype: $selectField
     */
    public function setSelectField(array $selectField)
    {
        if (!$this->isEmpty($selectField)) {
            throw new \Exception('系统崩溃了');
        }
        $this->selectField = $selectField;
    }
}