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

/**
 * 会员充值记录 
 */
class RechargeModel extends BaseModel
{
    private static $obj;

	public static $id_d;	// 主键编号

	public static $userId_d;	//会员ID

	public static $orderSn_d;	//充值单号

	public static $account_d;	//充值金额

	public static $ctime_d;	//充值时间

	public static $payTime_d;	//支付时间

	public static $payCode_d;	//

	public static $payName_d;	//支付方式

	public static $payStatus_d;	//充值状态0:待支付 1:充值成功 2:交易关闭

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 组装筛选条件 
     */
    public function buildActiveSearch($data, $id)
    {
        
        $where = array();
        
        if (!empty($_POST['timegap'])) {
            list($begin, $end) = explode($_POST['timegap'], ' - ');
            
            $where[static::$ctime_d] = array('between',array(strtotime($begin),strtotime($end)));
        }
        
        if (!empty($data) && is_array($data) && !empty($id))
        {
            $id = Tool::characterJoin($data, $id);
            $id = str_replace('"', null, $id);
            $where[static::$userId_d] = array('in', $id);
        }
        
        return $where;
    }
}