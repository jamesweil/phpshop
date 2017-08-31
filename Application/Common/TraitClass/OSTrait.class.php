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

use Think\Model;

trait OSTrait
{

    /**
     * @return string|string[][]|mixed
     */
    public function getOSInfor()
    {

        $sysInformation = array();
        $sysInformation['os'] = PHP_OS;
        $sysInformation['zlib'] = function_exists('gzclose') ? 'YES' : 'NO'; // zlib
        $sysInformation['safe_mode'] = (boolean) ini_get('safe_mode') ? 'YES' : 'NO'; // safe_mode = Off
        $sysInformation['timezone'] = function_exists("date_default_timezone_get") ? date_default_timezone_get() : "no_timezone";
        $sysInformation['curl'] = function_exists('curl_init') ? 'YES' : 'NO';
        $sysInformation['web_server'] = $_SERVER['SERVER_SOFTWARE'];
        $sysInformation['phpv'] = phpversion();
        $sysInformation['ip'] = get_client_ip();
        $sysInformation['fileupload'] = ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknown';
        $sysInformation['max_ex_time'] = ini_get("max_execution_time") . 's'; // 脚本最大执行时间
        $sysInformation['set_time_limit'] = function_exists("set_time_limit") ? true : false;
        $sysInformation['domain'] = $_SERVER['HTTP_HOST'];
        $sysInformation['memory_limit'] = ini_get('memory_limit');
        $mysqlInfo = (new Model())->query('select version() as ver');
        
        $sysInformation['mysql_version'] = $mysqlInfo[0]['ver'];
        if (function_exists("gd_info")) {
            $gd = gd_info();
            $sysInformation['gdinfo'] = $gd['GD Version'];
        } else {
            $sysInformation['gdinfo'] = "未知";
        }
        return $sysInformation;
    }
}