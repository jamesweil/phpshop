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
// 应用入口文件
header("Content-type:text/html;charset=utf-8");
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.5.0','<'))  die('require PHP > 5.5.0 !');

if (!file_exists('Install/install.lock')) {
    header('Location:/install.php');die();
}

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

//默认分页长度
define('PAGE_SIZE', 20);

// 定义应用目录
define('APP_PATH','./Application/');

define('BIND_MODULE','Admin');

// 引入ThinkPHP入口文件
require './Core/index.php';