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

class FreightSendModel extends BaseModel
{
    private static $obj;
    
    public static $freightId_d;
    
    public static $mailArea_d;
    
    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    /**
     * 该快递是否包含 改地区 
     */
    public function isInclude (array $areaId, array $express, BaseModel $freightModelMonery)
    {
        if ( !$this->isEmpty($areaId)|| !$this->isEmpty($express) || !($freightModelMonery instanceof BaseModel)) {
            return array();
        }
        
        $id = Tool::characterJoin($express, $freightModelMonery::$id_d);
        
        $data = $this->where(self::$freightId_d .' in ('.$id.') and '.self::$mailArea_d .' in ('.implode(',', $areaId).')')->select();
       
        if (empty($data)) {//不包含该地区【没有 倒着的】
            return array();
        }
        
        $i = 0;
        
        $lenght = count($data);
        
        foreach ($express as $key => $value) {
            
            if ( $i >= $lenght) {
                break;
            }
            if ($data[$i][self::$freightId_d] !== $value[$freightModelMonery::$id_d]) {
                unset($express[$key]);
            }
            $i++;
        }
      
        return $express;//包含该地区
    }
}