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
 * 支付 类型 
 */
class PayTypeModel extends BaseModel
{
    private static $obj;

	public static $id_d;

	public static $typeName_d;

	public static $createTime_d;

	public static $updateTime_d;

	public static $status_d;

	public static $isDefault_d;


	public static $isSpecial_d;	//特殊支付方式 0 不是 1 是

    
    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    /**
     * 获取已开启的支付类型 
     */
    public function getPay()
    {
        $data = S('payType');
        
        if (empty($data)) {
            $data = $this->getAttribute([
                'field' => [self::$updateTime_d, self::$createTime_d],
                'where' => [self::$status_d => 1]
            ], true);
            $data = $this->covertKeyById($data, self::$id_d);
            S('payType', $data, 6);
        }
        return $data;
    }
    //查询订单支付类型
    public function getPayTypeByOrder(array $order){
        if (empty($order)) {
            return false;
        }
        foreach ($order as $key => $value) {
            $where['pay_type_id'] = $value['pay_type'];
            $res = M('pay_type')->where($where)->find();
            $order[$key]['pay_type'] = $res['type_name'];
        }
        return $order;
    }
}