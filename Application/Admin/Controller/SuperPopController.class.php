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

use Think\Controller;

class SuperPopController extends Controller
{
    // 列表
    public function index()
    {
        $this->display();
    }

    /**
     * 修改和添加 超强人气
     * @return bool
     */
    public function edit()
    {
        $superPopModel = D("SuperPop");
        if (IS_POST) {
            $arr = json_decode($_POST['spec_value'], true);
            $nav_type = $_POST['nav_type'];
            if ($superPopModel->where([
                'nav_type' => $nav_type
            ])->find()) {
                // 先删除存在数据
                $superPopModel->startTrans();
                if (! ($superPopModel->where([
                    'nav_type' => $nav_type
                ])->delete())) {
                    $superPopModel->rollback();
                    return false;
                }
                // 在保存数据
                if (! ($superPopModel->addAll($arr))) {
                    $superPopModel->rollback();
                    return false;
                }
                $superPopModel->commit();
                $this->success("修改成功", U('index'));
            } else {
                if ($superPopModel->addAll($arr)) {
                    $this->success("修改成功", U('index'));
                }
            }
        } else {
            $this->assign($superPopModel->getSuperPop("热卖商品"));
            $this->display();
        }
    }
}