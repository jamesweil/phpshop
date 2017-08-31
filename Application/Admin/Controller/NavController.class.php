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
use Think\Controller;
use Think\Page;

/**
 * 导航控制器 
 */
class NavController extends AuthController
{
    private $_model = null;
    protected function _initialize(){
        parent::_initialize();
        $this->_model = D("Nav");
    }

    /**
     *导航菜单列表
     */
    public function index()
    {
        $count = $this->_model->count();
        $page_setting = C('PAGE_SETTING');
        $page = new Page($count, $page_setting['PAGE_SIZE']);
        $page_show = $page->show();
        $rows = $this->_model->limit($page->firstRow.','.$page->listRows)->order("sort desc")->select();
        $this->assign('rows',$rows);
        $this->assign('page_show',$page_show);
        $this->display();
    }

    /**
     * 添加导航菜单
     */
    public function add()
    {
        if(IS_POST){
            if($this->_model->create() === false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->add() === false){
                $this->error(get_error($this->_model));
            }else{
                $this->success("添加成功",U("index"));
            }
        }else{
            $this->display();
        }

    }

    /**
     * 导航菜单修改
     * @param $id
     */
    public function edit($id){
        if(IS_POST){
             if($this->_model->create() === false){
                 $this->error(get_error($this->_model));
             }
            if($this->_model->save() === false){
                $this->error(get_error($this->_model));
            }else{
              $this->success("修改成功",U("index"));
            }
        }else{
            $row = $this->_model->find($id);
            $this->assign('row',$row);
            $this->display("add");
        }
    }
    public function remove($id){
        if($this->_model->delete($id)){
            $this->success("删除成功",U("index"));
        }else{
            $this->error("删除失败",U("index"));
        }
    }


    /**
     * ajax改变排序的值
     * 输入框输入值来改变排序的值
     */
    public function changeSort(){
        $id = I("id");
        $sort = I("sort");
        $arr = [
            'id'=>$id,
            'sort'=>$sort
        ];
        if(M("Nav")->save($arr)){
            $this->ajaxReturn(['msg'=>1]);
        }else{
            $this->ajaxReturn(['msg'=>0]);
        }

    }

    /**
     * ajax 改变状态
     * 手动切换改变是否显示
     */
    public function changStatus(){
        $id = I("id");
        $data_flag = I("data_flag");
        if(	$data_flag == "true"){
           $result = [
               'id'=>$id,
               'status'=>0,
           ];
            if(M("Nav")->save($result)){
               $this->ajaxReturn("no");
            }
        }else{
            $result = [
                'id'=>$id,
                'status'=>1,
            ];

            if(M("Nav")->save($result)){
                $this->ajaxReturn("yes");
            }
        }
    }
}