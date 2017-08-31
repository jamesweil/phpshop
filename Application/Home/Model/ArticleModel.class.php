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


namespace Home\Model;


use Think\Model;
use Think\Hook;
use Common\Behavior\WangJinTing;

class ArticleModel extends Model
{
    //获取文章分类列表
    public function getList(){
        Hook::add('reade', WangJinTing::class);
        $article_category_model = M("ArticleCategory");
        $article_categories = $article_category_model->where(['status'=>1])->order("sort")->limit(5)->getField('id,name');
        $result = [];
        if (empty($article_categories)) {
            return array();
        }
        
        foreach($article_categories as $key=>$value){
            $articles = $this->field('id,name')
                       ->where(['status'=>1,'article_category_id'=>$key])
                       ->order('create_time')
                       ->limit(6)
                       ->select();
            $result[$value] = $articles;
        }
      return $result;
    }
}