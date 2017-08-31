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

namespace Home\Model;

use Common\Model\BaseModel;

/**
 * 余额模型 
 */
class BalanceModel extends BaseModel
{
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
    
    protected function _before_insert( &$data, $options) 
    {
        $data[self::$modifyTime_d] = time();
        
        $data[self::$status_d]     = 1;
        
        return $data;
    }
    
    /**
     * 获取余额
     */
    public function getBalanceMoney ()
    {
        $data = $this->field(self::$accountBalance_d.','.self::$lockBalance_d)->where(self::$userId_d.'=%d and '.self::$status_d.'= 1', (int)$_SESSION['user_id'])->order(self::$id_d.self::DESC)->find();
    
        if (empty($data)) {
            return 0;
        }
    
        $money = (float)$data[self::$accountBalance_d] - (float)$data[self::$lockBalance_d];
    
        return $money;
    }
    
    
    /**
     * 添加余额记录
     */
    public function addBalanceLogs ($monery)
    {
        if (!is_numeric($monery)) {
            return $this->traceStation(false, '添加余额记录失败');
        }
        
        $array = [
            self::$userId_d => $_SESSION['user_id'],
            self::$accountBalance_d => $monery,
            self::$description_d    => '购买商品',
        ];
        
        $status = $this->add($array);
        
        if (!$this->traceStation($status, '添加余额记录失败')) {
            return false;
        }
        $this->commit();
        return $status;
    }
    
    /**
     * 余额充值
     * @param array $recharge
     * @param string $className
     */
    public function rechargeMoney(array $recharge, $className)
    {
        if (empty($recharge)) {
            $this->rollback();
            return false;
        }
         
        $userId = $recharge[$className::$userId_d];
    
        $isHas = $this
        ->field(self::$id_d.','.self::$accountBalance_d.','.self::$lockBalance_d)
        ->where(self::$userId_d.'= %d', (int)$userId)
        ->order(self::$id_d.self::DESC)
        ->find();
    
        $add = [];
    
        $moneyRecharge = $recharge[$className::$account_d];
    
        if(!empty($isHas))
        {
            $money = floatval($isHas[self::$accountBalance_d] + $moneyRecharge - $isHas[self::$lockBalance_d]);
             
            $status= $this
            ->where(array('id'=>$isHas[self::$id_d]))
            ->save(array(self::$accountBalance_d=>$money, self::$rechargeTime_d=>time()));
            file_put_contents('./Uploads/save.txt', $status);
        }else{
            $add[self::$userId_d] = $userId;
            $add[self::$accountBalance_d] = $moneyRecharge;
            $add[self::$status_d] = 1;
            $add[self::$lockBalance_d] = 0;
            $add[self::$rechargeTime_d] = time();
    
            $status=$this->add($add);
        }
    
         
        if (!$this->traceStation($status, '充值失败')) {
            $this->rollback();
            return false;
        }
    
        $this->commit();
        return $status;
    }
}