<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <13052079525>
// +----------------------------------------------------------------------
namespace Common\Strategy;

use Common\Tool\Extend\ArrayParse;

class FreightContent
{

    private  $moneyObj ; //活动类对象
    
    private static $obj;   // 静态初始化对象
    
    private $constructParam; //构造方法参数
    
    private static $freightType = [
        0 => '',
        1 => 'Common\Strategy\SpecificStrategy\NumberMoney', //按件收费
        2 => 'Common\Strategy\SpecificStrategy\WeightMoney',  // 按件收费
        3 => 'Common\Strategy\SpecificStrategy\VolumeMoney'
    ];
    
    private function __construct( $activity, $param = null)
    {
        $this->moneyObj = $activity;
         
        $this->constructParam = $param;
    }
    
    /**
     * 获取活动处理对象
     * @param AbstractActivity $activity 具体活动
     * @param mixed $param
     * @return \Home\Logical\Content
     */
    public static function getInstance ($activity, $param = null)
    {
        $class = __CLASS__;
        if (!(self::$obj instanceof $class)) {
            self::$obj = new static($activity, $param);
        }
    
        return self::$obj;
    }
    
    public static function parseCall ($type)
    {
        $classType = self::$freightType;
    
        if (!isset($classType[$type])) {
            throw new \Exception('未找到此类');
        }
    
        return $classType[$type];
    }
    
    /**
     * 处理运费
     * @param array $receive
     * @param array $freightModeInAreaList
     */
    public static function parseReceive (array $receive, array $freightModeInAreaList)
    {
        if (!empty($receive)) {
            return $receive;
        }
        
        $param = [];
        
        (new ArrayParse([]))->oneArray($param, $freightModeInAreaList);
        
        return $param;
    }
    
    
    /**
     * 获取类的实例
     */
    public function newInstance ()
    {
       
        try {
            $name = new \ReflectionClass($this->moneyObj);
            $obj  = $name->newInstance($this->constructParam);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $obj;
    }
    
}