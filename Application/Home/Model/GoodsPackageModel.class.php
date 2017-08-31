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
 * 商品套餐主表 
 */
class GoodsPackageModel extends BaseModel
{
    private static $obj;
    

	public static $id_d;	//

	public static $total_d;	//商品总价

	public static $discount_d;	//优惠总价

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//修改时间


    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    /**
     * 处理套餐商品 
     * @param array $package 套餐数组
     * @return array；
     */
    public function getPackageByCart(array $package, $split)
    {
        if (!$this->isEmpty($package)) {
            $this->error = '空数据';
            return array();
        }
        
        $field = [
            self::$discount_d,
            self::$id_d. self::DBAS. $split,
            self::$total_d
        ];
        
        $data = $this->getDataByOtherModel($package, $split, $field, self::$id_d);
        
        return $data;
    }
    
}