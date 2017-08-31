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
use Common\Tool\Event;
/**
 * 简单的session管理
 * @author 王强
 */
class Session extends Tool
{
    public function setSession($isURL = '*')
    {
        header("Access-Control-Allow-Origin:".$isURL);
        $sessionId = isset($_POST['token']) ? $_POST['token'] : null;
        Event::listen('sId', $sessionId);
        if($sessionId){
            session_write_close();
            session_id($sessionId);
            session_start();
        }
    }
}