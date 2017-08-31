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
use Common\TraitClass\callBackClass;

/**
 * 余额模型 
 */
class BalanceModel extends BaseModel
{
    use callBackClass;
    private static  $obj;

	public static $id_d;	//主键id

	public static $userId_d;	//用户id

	public static $accountBalance_d;	//账户余额

	public static $lockBalance_d;	//锁定余额

	public static $status_d;	//1有效2过期

	public static $modifyTime_d;	//修改时间

	public static $rechargeTime_d;	//充值时间

	public static $description_d;	//描述

    /**
     * 获取类的实例
     * @return \Admin\Model\BalanceModel
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 根据用户信息 查询余额信息 
     * @param array $data 用户数据
     * @param string $id  以哪个字段拼接编号的字段
     * @return array
     */
    public function getBalanceByUser(array $data, $id)
    {
        if (empty($data) || empty($id)) {
            return $data;
        }
        $userIds = Tool::characterJoin($data, $id);
        
        $userIds = str_replace('"', null, $userIds);
        if (empty($userIds)) {
            return $data;
        }
        
        $balance = $this
                ->field(static::$accountBalance_d.','.static::$userId_d.' as '.$id.','.static::$lockBalance_d)
                ->where(static::$userId_d .' in ('.$userIds.')')
                ->order('field('.static::$userId_d.','.$userIds.')')
                ->select();
        if (empty($balance)) {
            return $data;
        }
        
        $parseData = array();
        //根据id合并相同的数组
        foreach ($balance as $value)
        {
            if (!isset($parseData[$value[$id]]))
            {
                $parseData[$value[$id]] = $value;
            }
            else
            {
                $parseData[$value[$id]][static::$accountBalance_d] += $value[static::$accountBalance_d];
                $parseData[$value[$id]][static::$lockBalance_d]    += $value[static::$lockBalance_d];
            }
        }
        unset($balance);
        $data = Tool::oneReflectManyArray( $parseData, $data, $id);
        
        return $data;
    }
}