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

/**
 * 发票内容表 
 */
class InvoiceContentModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//

	public static $content_d;	//


	public static $def_d;	//是否默认 0否 1 是

	public static $status_d;	//1 开启0关闭

	public static $createTime_d;	//

	public static $updateTime_d;	//

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return !(self::$obj instanceof $class) ? self::$obj = new self() : self::$obj;
    }
}