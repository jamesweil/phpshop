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

namespace Common\Tool\Extend;

use Common\Tool\Tool;

/**
 * 删除数据【后期优化】
 * @author 王强
 * @version 1.0.1
 */
class UnsetData extends Tool
{
    /**
     * 从数组删除数据
     * @param array $data
     * @param array $key 要删除的键 
     */
    public  function unsetDataByKey(array & $data, array $unsetkey)
    {
        if (empty($data) || empty($unsetkey) || !is_array($data) || !is_array($unsetkey)) {
            return array();
        }
        
        foreach ($unsetkey as $key => $value) {
            if (!array_key_exists($value, $data)) {
                continue;
            }
            unset($data[$value]);
        }
        return $data;
    }
}