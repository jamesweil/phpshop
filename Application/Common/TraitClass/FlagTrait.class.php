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

use Common\Tool\Tool;

/**
 * 数据处理 
 */
trait  FlagTrait 
{
    /**
     * @desc  商品添加 提取不同规格中的 库存价格
     * @param array $data  规格数据
     * @param string $deleteKey  要去掉的键
     * @return array
     */
    public function loadSpecificalByStockAndPrice(array $data, $deleteKey = 'sku')
    {
        if (empty($data) || !is_array($data)) {
            return array();
        }
        
        foreach ($data as $key => & $value) {
            if (!array_key_exists($deleteKey, $value)) {
                continue;
            }
            unset($data[$key][$deleteKey]);
        }
        return $data;
    }
    
    /**
     * 首字母 添加
     * @param array $data
     * @return array
     */
    public function firstAdd (array $data) 
    {
        if (empty($data) || !is_array($data)) {
            return array();
        }
        
        foreach ($data as $key => & $value) {
            $value = Tool::getFirstEnglish($value).' '. $value;
        }
        return $data;
    }
    
    /**
     * 按值排序 不改变 键 
     * @param array $array
     * @return array;
     */
    public function  sortByValue ( array  $array)
    {
        if (empty($array)) {
            return array();
        }
        
        $tempArray  = $array; //复制一份 用于恢复键
        
        sort($array);
       
        $returnArray = array();
        
        foreach ($array as $key => $value) {
        
            if (!in_array($value, $tempArray, true)) {
                continue;
            }
        
            $returnArray[array_search($value, $tempArray)] = $value;
        }
        unset($tempArray);
        return $returnArray;
    }
}