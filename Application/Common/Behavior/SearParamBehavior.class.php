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

namespace Common\Behavior;

use Think\Behavior;

class SearParamBehavior extends Behavior
{
    /**
     * 搜索参数处理
     * {@inheritDoc}
     * @see \Think\Behavior::run()
     */
    public function run(&$params)
    {
        // TODO Auto-generated method stub
        
        if (empty($params) || !is_array($params)) {
            return array();
        }
        
        foreach ($params as $key => & $value) {
            
            $value = urlencode($value);
        }
        
        return $params;
    }
    
}