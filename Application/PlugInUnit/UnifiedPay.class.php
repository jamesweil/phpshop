<?php
namespace PlugInUnit;

class UnfiedPay
{
    private static $obj;
    
    public static function getInstance()
    {
        $class = __CLASS__;
        
        return self::$obj = !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    public static function __callstatic($method, $args)
    {
        
    }
}