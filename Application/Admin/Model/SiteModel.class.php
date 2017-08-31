<?php

// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>\n
// +----------------------------------------------------------------------

namespace Admin\Model;

use Common\Model\BaseModel;
use Common\Model\IsExitsModel;

/**
 * 分站点模型
 * @author 王强 
 */
class SiteModel extends BaseModel implements IsExitsModel
{
    private static $obj;


	public static $id_d;	//ID编号

	public static $ipAddress_d;	//IP地址

	public static $areaId_d;	//所在地域编号

	public static $siteName_d;	//分站点名称

	public static $url_d;	//分站点域名

	public static $status_d;	//是否开启【1开启 0关闭】

	public static $pId_d;	//父站点【扩展字段】

	public static $createTime_d;	//添加时间

	public static $updateTime_d;	//更新时间

	public static $def_d;	//是否默认【0不是， 1是】

	public static $geographical_d;	//区域分布0华东地区 1华东东北2华南西南3华中西北

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 添加前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected function _before_insert(& $data, $options)
    {
        $data[static::$createTime_d] = time();
        
        $data[static::$updateTime_d] = time();
        
        $data[static::$ipAddress_d]  = get_client_ip();
        
        $data[static::$pId_d]        = 0;
        return $data;
    }
    
    /**
     * 更新前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_update()
     */
    protected function _before_update(& $data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$siteName_d, $data[static::$siteName_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$siteName_d].'】';
            return false;
        }
        $data[static::$updateTime_d] = time();
        
        return $data;
    }
    
    /**
     * 是否存在
     * {@inheritDoc}
     * @see \Common\Model\IsExitsModel::IsExits()
     */
    public function IsExits($post)
    {
        // TODO Auto-generated method stub
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        $isExits = $this->getInfoByName($post[static::$siteName_d]);
        
        return empty($isExits) ? false : true;
    }
    
    /**
     * 根据 站点信息 搜索是否只存在 
     */
    public function getInfoByName ($name)
    {
        if (empty($name)) {
            return array();
        }
        
        $data = $this->where(static::$siteName_d.'="%s"', $name)->find();
        
        return $data;
    }
    
    /**
     * 获取数据 
     */
    public function getData()
    {
        $data = S('SITE_CACHE');
        
        if (empty($data)) {
            $data = $this->select();
            
            S('SITE_CACHE', $data, 30);
        }
        return $data;
    }
}