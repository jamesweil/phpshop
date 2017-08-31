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
use Common\Tool\Tool;

/**
 * 尾货清仓 模型
 */
class PoopClearanceModel extends BaseModel
{
    private static  $obj;


	protected $couponList = '';

	public static $id_d;	//

	public static $status_d;	//是否限制时间购买 0  false 1 true

	public static $goodsId_d;	//商品编号

	public static $typeId_d;	//折扣类型

	public static $addTime_d;	//添加时间

	public static $updateTime_d;	//更新时间

	public static $expression_d;	//折扣值

	public static $sort_d;	//排序

	
    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    public function getPoopData ($where)
    {
        if (!$this->isEmpty($where)) {
            return array();
        }
        $data = $this->getAttribute(array(
            'field' => array(
                self::$updateTime_d,
                self::$addTime_d,
            ),
            'where' => $where,
            'order' => self::$sort_d.self::DESC.','.self::$addTime_d.self::DESC,
            'limit' => 20
        ), true);
        
        if (empty($data)) {
            return array();
        }
        
        $coponId = '';
        foreach ($data as $key => $value) {
            if ($value[self::$typeId_d] == -1) {
                $coponId .= ','.$value[self::$expression_d]; 
            }
        }
        
        $this->couponList = substr($coponId, 1);
        
        return $data;
    }
    
    /**
     * 获取尾货清仓数据
     * @param int $id
     */
    public function getPoopClearData ($id)
    {
        if (($id = intval($id)) === 0) {
            return false;
        }
        
        $notFiled = [self::$addTime_d, self::$updateTime_d];
        
        return $this->field($notFiled, true)->where(self::$goodsId_d.'=%d', $id)->find();
    }
    
    /**
     * 根據商品數據获取尾货清仓数据
     * @param array $goods
     * @param string $split
     * @return array
     */
    public function getPoopClearByGoods (array $goods, $split)
    {
        if (empty($goods)) {
            return array();
        }
        
        $field = $this->deleteFields([
            self::$id_d,
            self::$addTime_d,
            self::$updateTime_d,
            self::$sort_d
        ]);
        
        $idString = Tool::connect('parseString')->characterJoin($goods, $split);
        
        
        $data = $this->field($field)->where(self::$goodsId_d.' in ('.$idString.')')->select();
        
        if (empty($data)) {
            return $data;
        }
        //商品编号唯一
        $data = $this->covertKeyById($data, self::$goodsId_d);
        
        foreach ($goods as $key => & $value) {
            
            if (!array_key_exists($value[$split], $data)) {
                continue;
            }
            
            $value = array_merge($value, $data[$value[$split]]);
            
        }
        
        
        return $goods;
    }
    
    /**
     * @return string
     */
    public function getConponListIds ()
    {
        return $this->couponList;
    }
}