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
use Think\Page;

/**
 * 商品类型控制器
 */
class GoodsTypeController extends AuthController
{

    private $_model = null;

    /**
     * 控制器 初始化 
     */
    protected function _initialize()
    {
        parent::_initialize();
        $this->_model = D("GoodsType");
    }

    /**
     * 商品类型列表
     */
    public function index()
    {
        $count = $this->_model->where([
            'status' => 1
        ])->count();
        $page_setting = C('PAGE_SETTING');
        $page = new Page($count, $page_setting['PAGE_SIZE']);
        $page_show = $page->show();
        $rows = $this->_model->where([
            'status' => 1
        ])
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('rows', $rows);
        $this->assign('page_show', $page_show);
        $this->display();
    }
    
    /**
     * 商品添加 
     */
    public function add()
    {
        if (IS_POST) {
            if ($this->_model->create() === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->add($_POST) === false) {
                $this->error(get_error($this->_model));
            } else {
                $this->success("添加成功", U("index"));
            }
        } else {
            $this->display();
        }
    }
    
    /**
     * 商品编辑 
     * @param int $id
     */
    public function edit($id)
    {
        if (IS_POST) {
            if ($this->_model->create() === false) {
                $this->error(get_error($this->_model));
            }
            if ($this->_model->save() === false) {
                $this->error(get_error($this->_model));
            } else {
                $this->success("修改成功", U("index"));
            }
        } else {
            $row = $this->_model->find($id);
            $this->assign("row", $row);
            $this->display("add");
        }
    }
    
    /**
     * 移除商品类型
     * @param int $id 商品类型编号
     */
    public function remove($id)
    {
        if ($this->_model->delete($id)) {
            $this->success("删除成功", U("index"));
        } else {
            $this->error("删除失败", U("index"));
        }
    }
}