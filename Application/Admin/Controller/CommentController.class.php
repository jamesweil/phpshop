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
use Think\Auth;
use Think\Page;

/**
 * 订单评论
 */
class CommentController extends AuthController
{

	/**
     * 列表
     */
    public function comment_list()
    {
        $text    = I('GET.content', '');
        $type    = I('GET.type', 'none');
        $type_id = I('GET.type_id', '');
        $args    = array();
        $where   = array();

        $args['type']    = $type;
        $args['type_id'] = $type_id;
        if ($type != 'none' && $type_id != '') {
            $where[$type] = $type_id;
        }
        if ($text != null && $text != '') {
            $where['content'] = ['like', '%'.$text.'%'];
            $args['content']  = $text;
        }

        $model = D('orderComment');
        $count = $model->where($where)->count();
        $page  = new Page($count, 5, $args);
        $limit = $page->firstRow.','.$page->listRows;
        $list  = $model->search($where, $limit);
        $page  = $page->show();

        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('where', $args);
    	$this->display();
    }


    /**
     * 处理评论
     */
    public function handle()
    {
        $data = I('post.');
        switch ($data['act']) {
            case 'add':
            case 'edit':
                break;
            case 'del':
                $where['id'] = $data['del_id'];
                $ret = M('orderComment')->where($where)->delete();
            default:
                break;
        }
        if (IS_AJAX) {
            $this->ajaxReturn(intval($ret>0));
        }
    }
}