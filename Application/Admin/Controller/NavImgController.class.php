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
 * 导航规格图片控制器 
 */
class NavImgController extends AuthController
{

    /**
     * 添加
     */
    public function add(){
        if(IS_POST){
            $arr = json_decode($_POST['spec_value'],true);
            $edit_nav_type = $_POST['edit_spec_type'];
            $navImgModel = M("NavImg");
            if($edit_nav_type && $navImgModel->where(['nav_type'=>$edit_nav_type])->find() ){
                $navImgModel->startTrans();
                //先删除修改的导航规格类型
                $result_del = $navImgModel->where(['nav_type'=>$edit_nav_type])->delete();
                if(!$result_del){
                    $navImgModel->rollback();
                    return false;
                }
                //再保存数据
                $result_add = $navImgModel->addAll($arr);
                if(!$result_add){
                    $navImgModel->rollback();
                    return false;
                }
                $navImgModel->commit();
                $this->success("添加成功",U("index"));
            }else{
                $result = $navImgModel->addAll($arr);
                if($result){
                    $this->success("添加成功",U("index"));
                }else{
                    $this->error("添加失败");
                }
            }
        }else{
            $this->display();
        }

    }

    /**
     * 添加商品
     */
    public function search_goods(){
        $cond = ['p_id'=>['gt',0]];
        $count = M('goods')->where($cond)->count();
        $Page  = new \Think\Page($count,10);
        $goodsList = M('goods')->where($cond)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $show = $Page->show();//分页显示输出
        $this->assign('page',$show);//赋值分页输出
        $this->assign('goodsList',$goodsList);

        $this->display();
    }

    /**
     * 列表
     */
    public function index(){
        $navImgs = M("NavImg")->distinct(true)->field("nav_type,title_type")->select();
        $this->assign("navImgs",$navImgs);
        $this->display();
    }

    /**
     * 修改导航规格不同的图片
     * @return bool
     */
    public function edit(){
        $navImgModel = M("NavImg");
        $goodsModel = M("Goods");
        if(IS_POST){
            $edit_nav_type = $_POST['edit_spec_type'];
            $arr = json_decode($_POST['spec_value'],true);
            $navImgModel = M("NavImg");
            $navImgModel->startTrans();
            //先删除修改的导航规格类型
             $result_del = $navImgModel->where(['nav_type'=>$edit_nav_type])->delete();
             if(!$result_del){
                 $navImgModel->rollback();
                 return false;
             }
            //再保存数据
            $result_add = $navImgModel->addAll($arr);
            if(!$result_add){
                $navImgModel->rollback();
                return false;
            }

           $navImgModel->commit();
            $this->success("修改成功",U("index"));

        }else{
            $title_type = I("get.title_type");
            $nav_type = $navImgModel->where(['title_type'=>$title_type])->getField("nav_type");
            $nav_type_info = D("NavImg")->getNavimgInfo($nav_type);
            $this->assign($nav_type_info);
            $this->assign('nav_type',$nav_type);
            $this->display("add");
        }
    }

    public function remove(){
        $navImgModel = M("NavImg");
        $title_type = I("get.title_type");
        if($navImgModel->where(['title_type'=>$title_type])->delete()){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }


}