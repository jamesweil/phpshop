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

namespace Common\TypeParse;

/**
 * 类型解析 抽象父类 
 * @version 1.0.2
 */
abstract class AbstractParse implements InterfaceGetType
{
   protected static  $typeData;//数据
   
//    protected static  $curretType;//当前数据类型
   
   protected static  $sonObj; //子类对象
   
   private static  $obj;
   
   //模型对象
   private $model ;
   
   protected function __construct($typeData) {}
   
   /**
    * @return the $model
    */
   public function getModel()
   {
       return $this->model;
   }
   
   /**
    * @param field_type $model
    */
   public function setModel($model)
   {
       $this->model = $model;
   }
    
   
   /**
    * 获取类的实例
    * @param mixed $typeData 数据 
    */
   public static function getInstance($typeData)
   {
       self::$typeData = $typeData;
        
       $curretType = self::getTypeByData();
        
       $class = __CLASS__;
       
       $objName = ucfirst($curretType).'Type';
       
       if (self::$obj[$objName] instanceof $class) {
           return self::$obj[$objName];
       }
        
       try {
           $objInstance = '\\Common\\TypeParse\\SonType\\'.$objName;
           
           $obj = new $objInstance($typeData);
       
           self::$obj[$objName] = $obj;
       
       } catch (\Exception $e) {
           throw new \Exception('未找到此'.self::$curretType.'类');
       }
       
       return self::$obj[$objName];
   }
   
   /**
    * {@inheritDoc}
    * @see \Common\TypeParse\InterfaceGetType::getTypeByData()
    */
   public function getTypeByData()
   {
       return gettype(self::$typeData);
   }
}