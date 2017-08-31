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

namespace Common\TraitClass;

/**
 * 订单统计插件
 * @author 王强
 */
trait StatisticsTrait
{
    protected $orderData = array();
   
    public function compilerData(array $payType, array $orderNumberByPayType)
    {
        $combine = array();
        foreach ($payType as $key => $value)
        {
            $combine[$key]['value'] = isset($orderNumberByPayType[$key]) ? $orderNumberByPayType[$key] : 0;
          
            $combine[$key]['name']  = $value;
        }
        
        $param = array();
        
        foreach ( $combine as $key => $value ) {
            if (!isset($param[$value['name']])) {
                $param[$value['name']] = $value;
            } else {
                $param[$value['name']]['value'] += $value['value'];
            }
        }
        sort($param);
        return $param;
    }
    
    /**
     * 
     */
    public function parseDataByArea (array $addressData, array $areaData)
    {
        if (empty($this->orderData) || empty($addressData)|| empty($areaData)) {
            return array();
        }
        foreach ($addressData as $key => & $value)
        {
            if (!array_key_exists($value, $areaData)) {
                continue;
            }
            $value = mb_substr($areaData[$value], 0, -1);
        }
       
        if (empty($addressData)) {
            return array();
        }
        
        return $this->compilerData($addressData, array_flip($this->orderData));
    }
    
}