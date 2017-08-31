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
 

namespace Home\Controller;

use Think\Controller;

/**
 * 收藏
 */
class CollectionController extends Controller
{
    /**
     * 过滤登陆用户
     */
    public function _initialize()
    {
        if(empty($_SESSION['user_id']) || empty($_SESSION['mobile'])){
            if (IS_AJAX) {
                $url = U('Public/login');
                $this->ajaxReturn($url);
            }
            $this->redirect('Public/login');
        }
    }


    /**
     * 收藏商品
     */
    public function collection()
    {
        $ret = 0;
        $goods_id = I('goods_id', 0, 'intval');
        if ($goods_id > 0) {
            $data = [
                'goods_id' => $goods_id,
                'user_id'  => $_SESSION['user_id'],
                'status'   => 0
            ];
            $ret = D('collection')->store($data);
        }

        if (IS_AJAX) {
            $this->ajaxReturn(intval($ret > 0));
        }
    }
}