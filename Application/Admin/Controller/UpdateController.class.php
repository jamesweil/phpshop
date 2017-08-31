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
 * 升级 
 */
class UpdateController extends AuthController
{

    public function index()
    {
        $app = M('app');
        $rows = $app->select();
        $this->assign('rows', $rows);
        $this->display();
    }

    public function add()
    {
        $app = M('app');
        if (IS_POST) {
            $version_num = strtolower(I('version_num'));
            $map['version_num'] = $version_num;
            $map['app_type'] = I('app_type');
            $map['url'] = I('url');
            $map['intro'] = I('intro');
            $map['version'] = str_replace('v', '', $version_num);
            $map['create_time'] = NOW_TIME;
            $rst = $app->add($map);
            // dump($app->getLastSql());exit;
            if ($rst) {
                $this->success('发布成功', U('Update/index'));
            } else {
                $this->error('发布失败', U('Update/index'));
            }
        } else {
            $this->display();
        }
    }

    public function del()
    {
        $app = M('app');
        $rst = $app->where(array(
            'id' => I('get.id')
        ))->delete();
        if ($rst) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}