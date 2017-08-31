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


class ArticleController extends BaseController
{
    /**
     * 文章详情页
     */
      public function articleDetails(){
          $id = I("get.id");
          $id?$cond['id']=$id:false;
          $cond['status'] = 1;
          $article = M("Article")->field("name,create_time")->where($cond)->find();
          if($article){
              $article_content = M("ArticleContent")->field("content")->where(['article_id'=>$id])->find();
              $article = array_merge($article,$article_content);
          }else{
            $article = [];
          }
          $article['content'] = html_entity_decode($article['content']);
          $this->assign("article",$article);
          $this->display();
      }
}