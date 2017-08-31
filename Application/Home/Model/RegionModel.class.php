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
 * 地区 
 */
class RegionModel extends BaseModel
{

	public static $id_d;	//地区编号

	public static $parentid_d;	//上级id

	public static $name_d;	//名称

	public static $type_d;	//类型

	public static $displayorder_d;	//排序

    private static $obj;
    public static function getInitnation()
    {
        $class = __CLASS__;
        return !(self::$obj instanceof $class) ? self::$obj = new self() : self::$obj;
    }
    //查询省份
    public function getProvince(){
    	$where['parentid'] = 0;
    	$data = $this->where($where)->select();
    	return $data;
    }
    //根据Provinceid查询下级
    public function getProvinceByProvinceId($id){
        if (empty($id)) {
            return false;
        } 
        $where['parentid'] = $id;
        $data = $this->where($where)->select();
        return $data;
    }
}