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

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/2
 * Time: 11:05
 */

namespace Admin\Controller;


use Common\Controller\AuthController;
use Think\Page;

/**
 * 文章分类
 * @author Administrator
 */
class ArticleCategoryController extends AuthController
{
    //文章分类列表
    public function index(){
        $articleCategoryModel = D("ArticleCategory");
        $count = $articleCategoryModel->where(['status'=>1])->count();
        $page_setting = C('PAGE_SETTING');
        $page = new Page($count, $page_setting['PAGE_SIZE']);
        $page_show = $page->show();
        $rows = $articleCategoryModel->where(['status'=>1]) ->order("sort")->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('rows',$rows);
        $this->assign('page_show',$page_show);
        $this->display("index");
    }
    //文章分类添加
    public function add(){
        $model = D("ArticleCategory");
        if(IS_POST){
            if($model->create()===false){

                $this->error(get_error($model));
            }
            if($model->add()===false){
                $this->error(get_error($model));
            }else{
                $this->success("文章分类添加成功",U('index'));
            }

        }else{
            $this->display();
        }



    }
    //文章分类修改
    public function edit($id){
        $model = D("ArticleCategory");
        if(IS_POST) {
            if ($model->create() === false) {

                $this->error(get_error($model));
            }
            if ($model->save() === false) {
                $this->error(get_error($model));
            } else {
                $this->success("文章分类修改成功", U('index'));
            }
        }else{
            $row = $model->find($id);
            $this->assign('row',$row);
            $this->display('add');
        }




    }
    //文章分类修改
    public function remove($id){
        $model = D("ArticleCategory");
        if($model->delete($id)){
            $this->success("文章分类删除成功", U('index'));
        }else{
            $this->error("文章分类删除失败", U('index'));
        }

    }
}