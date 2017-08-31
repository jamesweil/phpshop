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
use Think\Page;
use Common\Model\BaseModel;

/**
 * 地址模型 
 */
class RegionModel extends BaseModel
{
    private static $obj;

    
	protected  $dataClass;
	
    protected $musterSplit = null;


	public static $id_d;	//地区编号

	public static $parentid_d;	//上级id

	public static $name_d;	//名称

	public static $type_d;	//类型

	public static $displayorder_d;	//排序

	protected static $fid_d;
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    /**
     * 获取数据 并添加标识 
     */
    public function getContent($id)
    {
        if (!is_numeric($id)) {
            return array();
        }
        
        // 此处 可显示出 与数据库字段 的无关性【
        
        $data      = $this->getAttribute(array(
            'field' => array(self::$displayorder_d),
            'where' => array(
               self::$parentid_d => $_POST['id']
            )
        ), true);
        
        if (empty($data)) {
            return array();
        }
        foreach ($data as $key => & $value) {
            if (empty($value[self::$name_d])) {
                continue;
            }
            $value[self::$name_d] = Tool::getFirstEnglish($value[self::$name_d]).' '. $value[self::$name_d];
        }
        return $data;
    }
    /**
     * 获取地址 
     */
    public function getArea (array $data, $split)
    {
        if (!$this->isEmpty($data) || !is_string($split)) {
            return array();
        }
       
        $idString = Tool::characterJoin($data, $split);
        
        if (empty($idString)) {
            return $data;
        }
        $areaStart = $this->field(self::$id_d.','.self::$name_d.','.self::$parentid_d)->where(self::$id_d .' in('.$idString.')')->select();
        
        if (empty($areaStart)) {
            return $data;
        }
        $this->musterSplit = $split;
        
        $area = self::getLevelTop($areaStart, $split);
        return Tool::oneReflectManyArray($area, $data, $split);
    }
    
    /**
     * 获取 上级地区
     * @param array $area 地区数组
     * @param string $split 以什么 字段来分割
     * @return unknown
     */
    private  function getLevelTop (array $area, $split)
    {
       
        if (empty($area)) {
            return array();
        }
        
        foreach ($area as $key => &$value) {
            $value[$split] = $value[self::$id_d];
            $value[self::$name_d] = $this->getJoinAndCache($value[self::$id_d]);
            unset($area[$key][self::$id_d]);
        }
        
        return $area;
    }
    
    /**
     * 获取省市 
     */
    public function getCityAndPro($page)
    {
        if (($page = intval($page))=== 0) {
            return array();
        }
        
        $count = S('count');
        
        if (empty($count)) {
            $count = $this->where(self::$parentid_d .' = 0')->count();
            S('count', $count, 3600);
        }
       
        
        $middle = ceil($count/6);

        $pageObj = new Page($count, $middle);
        
        $start = ($page-1) * $middle;
      
        $prov = $this->field(array(
            self::$id_d,
            self::$name_d,
            self::$parentid_d
        ))->where(self::$parentid_d .' = 0')->limit($pageObj->firstRow, $pageObj->listRows)->select();
        
        if (empty($prov)) {
            return array();
        }
        
        $idString = Tool::characterJoin($prov, self::$id_d);
        
        if (empty($idString)) {
            return array();
        }
        $city = $this->field(array(
            self::$id_d,
            self::$name_d,
            self::$parentid_d
        ))->where(self::$parentid_d .' in ('.$idString.')')->select();
        
        
        $area = array_merge($prov, $city);
        
        //线性输出
        $area = Tool::connect('Tree')->makeTreeForHtml($area, array(
            'parent_key' => RegionModel::$parentid_d
        ));
        
        
        $initArea = array(); 
        static::$fid_d = static::$parentid_d;
        
        $area = $this->covertKeyById($area, self::$id_d);
       
        $this->dataClass = $area;
        //是否有子级
        foreach ($area as $key => & $value) {
            if ($value['level'] == 0) {
                $this->isHaveSon($initArea, $value[self::$id_d]);
            }
        }
        
        $buildData = array();
        $buildData['area'] = $initArea;
        $buildData['page'] = $pageObj->show();
        unset($prov, $city, $initArea);
        return $buildData;
    }
    
    /**
     * 获取包邮地区 
     */
    public function getFreightArea (array $idArray, $split)
    {
        if (!$this->isEmpty($idArray)) {
            return array();
        }
       
        $idString = Tool::characterJoin($idArray, $split);
        
        if (empty($idString)) {
            return array();
        }
        
        $data =  $this->where(self::$id_d .' in ('. $idString.')')->getField(self::$id_d.','.self::$name_d);
        return $data;
    }
    
    /**
     * 获取收货地址 
     */
    public function getDefaultRegion(array $area, BaseModel $model)
    {
       
        $region = $this->getRegion($area, $model);
                   
        if (empty($region)) {
            return array();
        }
        $area[$model::$provId_d] = $region[$area[$model::$provId_d]];
        $area[$model::$city_d]   = $region[$area[$model::$city_d]];
        $area[$model::$dist_d]   = $region[$area[$model::$dist_d]];
        
        return $area;
    }
    
    /**
     * 组装城市列表 
     */
    public function getRegionByUserAddress(array $list, BaseModel $model)
    {
        if (!$this->isEmpty($list) || !($model instanceof BaseModel)) {
            return array();
        }
        
        $userAddressList = S('PARSE_USER_ADDRESS_LIST');
        
        if (empty($userAddressList)) {
            
            $str = null;
            
            foreach ($list as $key => $value) {
                $str .= ','.$value[$model::$provId_d].','.$value[$model::$city_d].','.$value[$model::$dist_d];
            }
            $str = substr($str, 1);
            if (empty($str)) {
                return array();
            }
           
            $data = $this->where(self::$id_d.' in('.addslashes($str).')')->getField(self::$id_d.','.self::$name_d);
          
            if (empty($data)) {
                return array();
            }
            
            foreach ($list as $key => & $value) {
            
                if (array_key_exists($value[$model::$provId_d], $data)) {
                     
                    $value[$model::$provId_d] = $data[$value[$model::$provId_d]];
                     
                }  if (array_key_exists($value[$model::$city_d], $data)) {
                     
                    $value[$model::$city_d] = $data[$value[$model::$city_d]];
                     
                }  if (array_key_exists($value[$model::$dist_d], $data)){
                     
                    $value[$model::$dist_d] = $data[$value[$model::$dist_d]];
                     
                }
            }
            
            $userAddressList = $list;
            unset($list);
            S('PARSE_USER_ADDRESS_LIST', $userAddressList, 10);
        }
        return $userAddressList;
    }
    /**
     * 获取地区 
     */
    protected  function getRegion (array $area, BaseModel $model)
    {
        if (!$this->isEmpty($area) || !($model instanceof BaseModel)) {
            return array();
        }
        
        $region = $this
            ->where(self::$id_d .' in ('.$area[$model::$provId_d].','.$area[$model::$city_d].','.$area[$model::$dist_d].')')
            ->getField(self::$id_d.','.self::$name_d);
        return $region;
    }
    /**
     * 获取编辑地区数据 
     */
    public function getEditAddressData (array $area, BaseModel $model)
    {
        $region = $this->getRegion($area, $model);
         
        if (empty($region)) {
            return array();
        }
        
        $area['provName'] = $region[$area[$model::$provId_d]];
        $area['cityName'] = $region[$area[$model::$city_d]];
        $area['distName'] = $region[$area[$model::$dist_d]];
        
        return $area;
    }
    
    /**
     * 获取地区 
     */
    public function getAreaByName($name)
    {
        if (empty($name)) {
            return array();
        }
        
        $data = $this->where(self::$name_d.'like "%s""%"', $name)->select();
        
        return $data;
    }
    /**
     * 获取地区名字 
     */
    public function getAreaName($data, $split)
    {
        if (!$this->isEmpty($data) || empty($split)) {
            return array();
        }
        
        $dataCache = S('SITE_REGION_CACHE');
//         if (empty($dataCache)) {
            $dataCache = $this->getDataByOtherModel($data, $split, [
                self::$name_d,
                self::$id_d
            ], self::$id_d);
            S('SITE_REGION_CACHE', $dataCache, 30);
//         }
        return $dataCache;
    }
    
    /**
     * 根据编号低级编号 获取地区
     * @param integer $id 地区编号
     * @return array
     */
    public function getAreaTopIdBySmallId ($id)
    {
        if (($id = intval($id)) === 0) {
            return array();
        }
        $key = md5($id).'_cache';
        
        $data = S($key);
        
        if (empty($data)) {
            $data = $this->getTop($id);
        } else {
            return $data;
        }
        
        if (empty($data)) {
            return array();
        }
        S($key, $data);
        return $data;
    }
    
    /**
     * 获取顶级编号 
     */
    public function getTop ($id)
    {
        if (($id = intval($id)) === 0) {
            return array();
        }
        
        $data = $this->field(self::$id_d.','.self::$parentid_d)->where(self::$id_d.'= %d', $id)->find();
        if (empty($data)) {
            return array();
        }
        
        if ($data[self::$parentid_d] == 0)  {
            return $data;
        }
        
        return $this->getTop($data[self::$parentid_d]);
    }
    
    //拼接地区
    public function getJoin ($id)
    {
        
        if (($id = intval($id)) === 0) {
            return array();
        }
        $data = $this->field(self::$id_d.','.self::$parentid_d.','.self::$name_d)->where(self::$id_d.'= %d', $id)->find();
        if (empty($data)) {
            return array();
        }
    
        if ($data[self::$parentid_d] == 0)  {
            return $data[self::$name_d];
        }
    
        $name = $this->getJoin($data[self::$parentid_d]);
        return $name.'-'.$data[self::$name_d];
    }
    
    public function getJoinAndCache ($id) 
    {
        $cacheKey = md5($id).'_name_'.$id;
        
        $data = S($cacheKey);
        
        if (empty($data)) {
            $data = $this->getJoin($id);
        } else {
            return $data;
        }
        if (empty($data)) {
            return array();
        }
        S($cacheKey, $data);
        return $data;
    }
    
    
    /**
     * 获取下级地区 
     * @param unknown $id
     * @return mixed|boolean|NULL|string|unknown|object
     */
    public function getUpData ($id)
    {
        if (!is_numeric($id)) {
            return array();
        }
        $data = $this->where(self::$parentid_d.'= %d', $id)->getField(self::$id_d.','.self::$name_d.','.self::$parentid_d);
        return (array)$data;
    }
    
    /**
     * 获取并缓存地区列表 
     */
    public function getUpDataAndCache ($id) 
    {
        if (!is_numeric($id)) {
            return array();
        }
        $cacheKey = md5($id).'_up_'.$id;
        
        $data = S($cacheKey);
        
        if (empty($data)) {
            $data = $this->getUpData($id);
        } else {
            return $data;
        }
        if (empty($data)) {
            return array();
        }
        
        S($cacheKey, $data);
        return $data;
    }
    
   
    
    /**
     * 获取默认地区
     * @param unknown $data
     * @param unknown $split
     * @return mixed|object|NULL|unknown|string[]|unknown[]
     */
    public function getDataDefault($data, $split)
    {
        if (($id = intval($data[$split])) === 0) {
            return array();
        }
        $arrayData = S('DEFAULT_CACHE_SITE_NAME');
        if (empty($arrayData)) {
            $data[$split] = $this->where(self::$id_d.='=%d', $id)->getField(self::$name_d);
            $arrayData = $data;
            S('DEFAULT_CACHE_SITE_NAME', $arrayData, 30);
        }
        return $arrayData;
    }
    
}