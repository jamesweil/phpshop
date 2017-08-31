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
 * 从数组 随机取值 
 */
trait RandTrait 
{
    /**
     * 随机数据
     * @var unknown
     */
    protected $randData;
    
    public function getRandData() 
    {
        $randData = $this->randData;
        if (empty($randData)) {
            return null;
        }
      
       
    }
}