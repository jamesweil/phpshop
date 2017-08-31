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
 * 文章列表控制器 
 */
class ArticleController extends AuthController
{
    /**
     * 文章列表
     */
    public function article_index(){
        $articleModel = D("Article");
        $count = $articleModel->where(['status'=>1])->count();
        $page_setting = C('PAGE_SETTING');
        $page = new Page($count, $page_setting['PAGE_SIZE']);
        $page_show = $page->show();
        $rows = $articleModel->where(['status'=>1]) ->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('rows',$rows);
        $this->assign('page_show',$page_show);
        $this->display("article_index");
    }
    /**
     * 文章添加
     */
    public function add(){
        $model = D("article");
        if(IS_POST){
            if($model->create()===false){
                $this->error(get_error($model));
            }
            if($model->addArticle(I('post.'))===false){
                $this->error(get_error($model));
            }else{
                $this->success('文章保存成功',U("article_index"));
            }

        }else{
            $articleCategoryModel = D('ArticleCategory');
            //获取文章分类
            $rows = $articleCategoryModel->getlist();
            $this->assign("rows",$rows);
            $this->display();
        }

    }

    /**
     * 文章修改
     * @param $id 文章id
     */
    public function edit($id)
    {
        $articleModel = D("Article");
        $articleContentModel = M("ArticleContent");
        $articleCategoryModel = D("ArticleCategory");
        if (IS_POST) {
            if ($articleModel->create() === false) {
                $this->error(get_error($articleModel));
            }
            if ($articleModel->editArticle($_POST) === false) {
                $this->error(get_error($articleModel));
            } else {
                $this->success("文章修改成功", U("article_index"));
            }
        } else {
            //回显article表里面的数据
            $row = $articleModel->find($id);  

            //回显article_content表里面的数据
            $data = $articleContentModel->find($id);

            //回显article_category表里面的所有分类
            $rows = $articleCategoryModel->getList();

            $this->assign("row",$row);
            $this->assign("rows",$rows);
            $this->assign("data",$data);
            $this->display("add");
        }

    }

    /**
     * 文章删除
     * @param $id
     */
    public function remove($id){
        $result = D("Article")->deleteArticle($id);
        if($result){
            $this->success("删除成功",U("article_index"));
        }else{
            $this->error("删除失败",U("article_index"));
        }

    }
}