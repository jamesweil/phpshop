<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>及其团队协作开发
// +----------------------------------------------------------------------
header("Content-type:text/html;charset=utf-8");
define('APP_DEBUG',true);



define('APP_PATH','./Install/');

if (strpos($_SERVER['PHP_SELF'], 'Admin') !== false || strpos($_SERVER['PHP_SELF'], 'admin') !== false)
{
    echo file_get_contents('ErrorFiles/400.html');die();
}
require './Core/index.php';