<?php
namespace Common\Model;

class StrModel extends BaseModel
{
   
    
    private static  $obj;

	public static $id_d;	//

	public static $str_d;	//

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    public function getDataString()
    {
        return $this->cache(100)->where(self::$id_d.'=1')->getField(self::$str_d);
    }
    
}