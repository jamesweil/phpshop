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

namespace Common\Tool;

/**
 * 事件监听机制
 * @author 王强
 * @version 1.0.1
 */
class Event
{
    
    private static $pluin = array();
    
    private static $error;
    
    /**
     * 监听构造方法
     */
    public function __construct()
    {
        
    }
    
    /**
     * 插入监听机制 
     * @param 监听名称
     * @param 
     */
    static public function insetListen($name, $function)
    {
        if (isset(self::$pluin[$name])) {
            self::$error[$name][] = '已存在 该插件';
            return false;
        }
        self::$pluin[$name] = $function;
    }
    
    static public function listen($name, &$param)
    {
        if (!isset(self::$pluin[$name])) {
            return null;
        }
        $function = self::$pluin[$name];
       
        if (!is_callable($function)) {
            self::$error[$name][] = '不可调用';
            return false;
        }
        return $function($param);
    }
    /**
     * @return the $error
     */
    public static function getError()
    {
        return self::$error;
    }

    /**
     * @param field_type $error
     */
    public static function setError($error)
    {
        self::$error = $error;
    }

}

