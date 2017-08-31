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

namespace Common\TypeParse;

/**
 * 执行 数据解析
 */
interface ActionRunInterface
{
     /**
      * 执行方法 必须实现
      */
     public function actionRun();
     
     /**
      * 处理 数据库事件
      */
     public function parseDataBaseByUser($model = null);
     
}