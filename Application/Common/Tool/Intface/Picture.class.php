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

namespace Common\Tool\Intface;

/**
 * 图片删除接口
 * @author 王强
 * @version 1.0.1
 */
interface Picture
{
    /**
     * @copyright 版权所有©亿速网络
     * 删除图片
     * @param bool   $isPartten 是否使用正则【编辑器图片】
     * @param string $parttenCondition 正则数组中的建
     * @return bool
     */
    public  function delPicture($isPartten = false, $parttenCondition = 'imgSrc');
}