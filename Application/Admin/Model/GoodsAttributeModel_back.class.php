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
use Common\Tool\Tool;

class GoodsAttributeModel extends BaseModel
{
    const SHOW  =  1;
    const Close = 1;
    //主键
    public static  $id_d;
    
    //属性名称
     public static $attribute_d;
   
    //是否启用
     public static $status_d;
    
    //创建时间
     public static $createTime_d;
    
    //更新时间
     public static $updateTime_d;
     
     //父级分类编号
     public static $pId_d;
     
     //商品分类编号
     public static $goodsClassId_d;
     
     protected $noSelectfields;
     
     private static  $obj;
    
     public static function getInitnation()
     {
         $name = __CLASS__;
         return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
     }
     
     
     /**
      * 重写父类方法
      */
     protected  function _before_insert(& $data, $options)
     {
         $data[static::$createTime_d] = time();
         
         $data[static::$updateTime_d] = time();
         
         return $data;
     }
     
     /**
      * 重写父类方法
      */
     protected function _before_update(& $data, $options)
     {
         $isExits = $this->editIsOtherExit(static::$attribute_d, $data[static::$attribute_d]);
         
         if ($isExits) {
             $this->rollback();
             $this->error = '已存在该名称：【'.$data[static::$attribute_d].'】';
             return false;
         }
         $data[static::$updateTime_d] = time();
          
         return $data;
     }
     
     /**
      * 重写删除 
      */
     public function delete(array $options)
     {
         if (empty($options))
         {
             return false;
         }
         //获取父级编号
         $pId = $this->getAttribute($options, false, 'find');
        
         if (empty($pId))
         {
             return false;
         }
         
         unset($options['field']);
         $pWhere = $options;
         $options['where'][static::$pId_d] = $pId[static::$id_d];
         $options['field'] = static::$id_d;
         
        
         unset($options['where'][static::$id_d]);
         //获取我的子集编号数组
         $id = $this->getAttribute($options);
         //删除
         return $this->parseId($id, $pWhere, $pId[static::$id_d]);
     }
     
     /**
      * 获取属性
      * @param array $data  属性数组
      */
     public function parseAttribute()
     {
         $attrData = $this->where(static::$status_d .' = '.static::SHOW)->getField(static::$id_d. ',' .static::$attribute_d);
         
         return $attrData;
     }
    
     /**
      * @param array $id
      * @param array $where
      * @param int $number
      * @return boolean
      */
     private function parseId(array $id, array $where, $number)
     {
         if (!is_numeric($number))
         {
             return false;
         }
         
         if (empty($id)) {
             return  parent::delete($where);
         } else {
             $id = Tool::characterJoin($id, static::$id_d).','.'"'.$number.'"';
             $id  = str_replace('"', null, $id);
            
             $where['where'][static::$id_d] = array('in', $id);
             return empty($id) ? false : parent::delete($where);
         }
     }
     
}