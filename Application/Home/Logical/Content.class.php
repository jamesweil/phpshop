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
namespace Home\Logical;
/**
 * 初始化上下文
 * @version 1.0.0
 * @author 王强
 */
class Content
{
    private  $activityObj ; //活动类对象名
    
    private static $obj;   // 静态初始化对象 
    
    private $constructParam; //构造方法参数
    
    private static $activityType = [
        'Home\Logical\ActivityDetail\NoActivity',
        'Home\Logical\ActivityDetail\PoopClearanceActivityDetail'
    ];
    
    /**
     * @return the $activityType
     */
    public static function getActivityType()
    {
        return self::$activityType;
    }

    /**
     * @param multitype:string  $activityType
     */
    public static function setActivityType($activityType)
    {
       
        
        self::$activityType = $activityType;
    }

    public function __construct( $activity, $param = null)
    {
       $this->activityObj = $activity;
       
       $this->constructParam = $param;
    }
    
    
    /**
     * @return the $activityObj
     */
    public function getActivityObj()
    {
        return $this->activityObj;
    }

    /**
     * @return the $constructParam
     */
    public function getConstructParam()
    {
        return $this->constructParam;
    }

    /**
     * @param field_type $activityObj
     */
    public function setActivityObj($activityObj)
    {
        $this->activityObj = $activityObj;
    }

    /**
     * @param string $constructParam
     */
    public function setConstructParam($constructParam)
    {
        $this->constructParam = $constructParam;
    }
    
    /**
     * 活动类型
     * @param integer $type
     * @throws \Exception
     * @return string
     */
    public static function parseCall ($type)
    {
        $classType = self::$activityType;
       
        if (!isset($classType[$type])) {
            throw new \Exception('未找到此类');
        }
        
        return $classType[$type];
    }
    
    /**
     * 循环 处理活动类型
     */
    public function parseForeachActivity ( )
    {
        $goodsData = $this->constructParam;
        
        if (empty($goodsData)) {
            return array();
        }
        
        //活动类型
        $activityType = null;
        
        foreach ($goodsData as $key => & $value) {
            
            $activityType = self::parseCall($key);
            
            $this->activityObj = $activityType;
            
            $this->constructParam = $value;
            
            $value = $this->newInstance()->getResultByManyArrays();
            
        }
        return $goodsData;
    }
    
    /**
     * 获取类的实例
     */
    public function newInstance ()
    {
        if (!empty(self::$obj[$this->activityObj])) {
        
            return self::$obj[$this->activityObj];
        }
        
        try {
            $name = new \ReflectionClass($this->activityObj);
            $obj  = $name->newInstance($this->constructParam);
            
            self::$obj[$this->activityObj] = $obj;
           
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $obj;
    }
    
}