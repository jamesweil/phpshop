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
 * 代金卷模型 
 */
class CouponModel extends BaseModel
{
    private static  $obj;

	public static $id_d;

	public static $name_d;

	public static $type_d;

	public static $money_d;

	public static $condition_d;

	public static $createnum_d;

	public static $sendNum_d;

	public static $useNum_d;

	public static $sendStart_time_d;

	public static $sendEnd_time_d;

	public static $useStart_time_d;

	public static $useEnd_time_d;

	public static $addTime_d;

	public static $updateTime_d;
    
	protected  $error;
    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 优惠券编号【线下发放的】 
     */
    public function getCouponByPoop ($idString, array $data, BaseModel $model)
    {
        if (!is_string($idString) || !$this->isEmpty($data) || !($model instanceof BaseModel)) {
            return $data;
        }
        
        $coupon = $this->where(self::$id_d .' in ('.$idString.')')->getField(self::$id_d.','.self::$name_d);
        
        if (empty($coupon)) {
            return $coupon;
        }
        
        foreach ($data as $key => &$value) {
            
            if (!array_key_exists($value[$model::$expression_d], $coupon)) {
                continue;
            }
            $value[$model::$expression_d] = $coupon[self::$id_d];
        }
        
        return $data;
    }


    /**
     * 根据用户ID获取优惠券关联列表
     */
    public function getCouponByUser($user_id, $field = '', $limit = '0,100')
    {
        if (empty($field)) {
            $field = 'l.`id`, c.`name`, c.`use_start_time`, c.`use_end_time`, c.`condition`, c.`money`';
        }
        $time  = time();
        $where = [
            'l.user_id'        => $user_id,
            'l.status'         => 0,
            'c.use_start_time' => ['lt', $time],
            'c.use_end_time'   => ['gt', $time]
        ];
        $list = $this->alias('c')->join('db_coupon_list AS l ON l.c_id=c.id')
            ->where($where)->field($field)->limit($limit)->select();
        return $list;
    }
    
    /**
     * 验证优惠券  
     */
    public function validateCoupon(array $data, BaseModel $model)
    {
       
        if (!$this->isEmpty($data) || !($model instanceof BaseModel)) {
            return array();
        }
        
        $parseArray = S('CouponMaster_HOME');
        
        if (empty($parseArray)) {
            
            $field = [
                self::$id_d,
                self::$condition_d,
                self::$money_d,
                self::$name_d,
                self::$useStart_time_d,
                self::$useEnd_time_d
            ];
            
            $dataArray = $this->getDataByOtherModel($data, $model::$cId_d, $field, self::$id_d);
           
            if (empty($dataArray)) {
                return array();
            }
            $curretTime =  time();
            
            $parseArray = array();
            
            foreach ($dataArray as $key => & $value) {
                if (empty($value)) {
                    continue;
                }
            
                if ($value[self::$sendStart_time_d] > $curretTime || $value[self::$useEnd_time_d] < $curretTime) {
                    $parseArray['alearldyUse'][$value[self::$id_d]] = $value;
                } else {
                    $parseArray['notUse'][$value[self::$id_d]] = $value;
                }
            }
            
            S('CouponMaster_HOME', $parseArray, 6);
        }
        
        return $parseArray;
    }
    
    /**
     * 是否可用
     * @param array $data 优惠券数组数据 
     */
    public function checkEffective (array $data)
    {
        if (!$this->isEmpty($data)) {
            return array();
        }
        
        $curretTime =  time();
        foreach ($data as $key => & $value) {
            
            if (empty($value)) {
                continue;
            }
            
            if ($value[self::$sendStart_time_d] > $curretTime || $value[self::$useEnd_time_d] < $curretTime) {
                $this->error = '未在规定的时间使用';
                $value['status'] = 0;//不可用
            }
        }
        
        return $data;
        
    }
    
    /**
     * 获取 优惠券 
     */
    public function getCoupon ($where)
    {
        
    }
    
    /**
     * 是否符合条件使用 
     */
    public function isUse($id, $monery) 
    {
       
        if (($id = intval($id)) === 0 || ($monery = floatval($monery)) === 0.0) {
            return false;
        }
      
        $condition = (float)$this->getDataByField($id, self::$condition_d);
        if ($condition < $monery) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取某个字段值 
     */
    public function getDataByField($id, $field) 
    {
        $fieldString = implode(',', $this->getDbFields());
        if (($id = intval($id)) === 0 || false === strpos($fieldString, $field)) {
            return false;
        }
        return $condition = $this->where(self::$id_d.'=%d', $id)->getField($field);
    }
    //查询我的优惠券数量
    public function getCouponCountByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        $where['user_id'] = $user_id;
        $where['status']  = '0';
        $count = M('coupon_list')->where($where)->count();
        return $count;
    }
    //查询单张优惠券信息
    public function getCouponDetailsById($id){
        if (empty($id)) {
            return false;
        }
        $res = $this->field('id,money,condition')->where(['id'=>$id])->find();
        return $res;
    }


    /**
     * 获取一张优惠券,并判断是否有效
     * @param  integer $promo_id 优惠券id
     * @param  float  $total    现在条件
     * @return array
     */
    public function getCouponValidById($promo_id, $total = 0.00)
    {
        if (empty($promo_id)) {
            return false;
        }
        $where = [
            'l.id'     => $promo_id,
            'l.status' => 0
        ];
        $prom = M('coupon')->alias('c')->join('__COUPON_LIST__ as l on c.id=l.c_id')->where($where)
            ->field('c.condition, c.money, c.use_start_time, c.use_end_time')->find();
        if (!empty($prom) 
            && $prom['use_start_time'] < time() 
            && $prom['use_end_time'] > time() 
            && (empty($total) || $prom['conditon'] < $total))
        {
            return $prom;
        }
        return false;
    }

    
    /**
     * 买就送优惠券优惠 
     */
    public function getCouponData(array $data, BaseModel $model)
    {
        if (!$this->isEmpty($data) ||!($model instanceof  BaseModel)) {
            $this->error = '数据错误';
            return array();
        }
        
        $idString = null;
        foreach ($data as $key => $value) {
            if ($value[$model::$type_d] != -1) {
                continue;
            }
            $idString .= ','.addslashes($value[$model::$expression_d]);
        }
       
        $idString = substr($idString, 1);
        
        if (empty($idString)) {
            return $data;
        }
        
        $coupon = $this->where(self::$id_d.' in ('.$idString.')')->getField(self::$id_d.','.self::$name_d);
       
        if (empty($coupon)) {
            return $data;
        }
        
       foreach ($data as $key => & $value) {
           if ($value[$model::$type_d] != -1 || !array_key_exists($value[$model::$expression_d], $coupon)) {
               continue;
           }
           $value['promation_name'] .= '买就送代金券、' . $coupon[$value[$model::$expression_d]];
       }
       return $data;
    }
    
    /**
     * 使用优惠券
     * @param  integer $promo_id 优惠券id
     * @param  integer $order_id 订单id
     * @return boolean
     */
    public function used($promo_id, $order_id)
    {
        $data = [
            'id'       => $promo_id,
            'order_id' => $order_id,
            'use_time' => time(),
            'status'   => 1
        ];
        $ret = M('couponList')->save($data);
        return $ret > 0;
    }
}