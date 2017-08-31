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

namespace Admin\Controller;
use Common\Controller\AuthController;

/**
 * 抽奖 奖品控制器
 * @author 王强
 * @version 1.0
 */
class PrizeController extends AuthController
{
    
    public function index ()
    {
        $this->display();
    }
    
}
