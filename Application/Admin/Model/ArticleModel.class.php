<?php

// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>\n
// +----------------------------------------------------------------------


namespace Admin\Model;


use Think\Model;

class ArticleModel extends Model
{
    //开启验证
    protected $patchValidate = true;
    //验证规则
    protected $_validate = [
        ['name','require','文章名称不能为空'],
        ['sort','number','排序只能是数字'],
        ['content','require','文章内容不能为空']
    ];

    /**
     * 添加文章
     * @param array $newdata post 数据
     * @return bool
     */
    public function addArticle($newdata){
        $this->startTrans();
        //保存文章基本信息表
        $data = $newdata;
        $data['admin_account'] = session('account');
        $data['create_time'] = time();
        if(($article_id = $this->add($data))===false){
            $this->error = '文章基本信息保存失败';
            $this->rollback();
            return false;
        }
        //保存文章内容表
        $arr = [
            'article_id'=>$article_id,
            'content'=>$newdata['content'],
        ];
        if(!empty($arr)){
            $articleContentModel = D("ArticleContent");
            if($articleContentModel->add($arr)===false){
                $this->error = "保存文章内容失败";
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 修改文章
     * @param $newdata 传入数据
     * @return bool
     */
    public function editArticle($newdata){
        $this->startTrans();
        $article_id = $this->data['id'];
        //保存文章基本信息
        if($this->save()===false){
            $this->error ="更新文章基本信息失败";
            $this->rollback();
            return false;
        }
        //保存文章内容
        $data = [
            'article_id'=>$article_id,
            'content'=>$newdata['content'],
        ];
        if(M('ArticleContent')->save($data) === false){
            $this->error = '保存详细内容失败';
            $this->rollback();
            return false;
        }
        return $this->commit();
    }

    /**
     * 删除文章
     * @param $id
     * @return bool
     */
    public function deleteArticle($id){
        $this->startTrans();
        //删除文章基本信息表
        if($this->delete($id)===false){
            $this->rollback();
            return false;
        }
        //删除文章内容表
        $result = D("ArticleContent")->where(['article_id'=>$id])->delete();
        if($result === false){
            $this->rollback();
            return false;
        }
        return $this->commit();
    }
}