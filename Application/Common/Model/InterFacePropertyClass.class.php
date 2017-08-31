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

/**
 * 属性 trait
 */
trait  InterFacePropertyClass
{

    protected static  $primaryId;

    protected static $tbId;

    protected static $tbName;

    protected static $key;

    protected static $values;
    /**
     * @return the $primaryId
     */
    public static function getPrimaryId()
    {
        return static::$primaryId;
    }

    /**
     * @return the $tbId
     */
    public static function getTbId()
    {
        return static::$tbId;
    }

    /**
     * @return the $tbName
     */
    public static function getTbName()
    {
        return static::$tbName;
    }

    /**
     * @return the $key
     */
    public static function getKey()
    {
        return static::$key;
    }

    /**
     * @return the $values
     */
    public static function getValues()
    {
        return static::$values;
    }

    /**
     * @param field_type $primaryId
     */
    public static function setPrimaryId($primaryId)
    {
        static::$primaryId = $primaryId;
    }

    /**
     * @param field_type $tbId
     */
    public static function setTbId($tbId)
    {
        static::$tbId = $tbId;
    }

    /**
     * @param field_type $tbName
     */
    public static function setTbName($tbName)
    {
        static::$tbName = $tbName;
    }

    /**
     * @param field_type $key
     */
    public static function setKey($key)
    {
        static::$key = $key;
    }

    /**
     * @param field_type $values
     */
    public static function setValues($values)
    {
        static::$values = $values;
    }

   

}