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
 * Date: 2016/12/17
 * Time: 17:07
 */

namespace Admin\Controller;


use Common\Controller\AuthController;

/**
 * 规格控制器
 * @author Administrator
 */
class GoodsSpecController extends AuthController
{
    private $_model = null;
    protected function _initialize()
    {
        parent::_initialize();
        $this->_model = D("GoodsSpec");
    }


    /**
     * 商品规格列表
     */
    public function index(){
        //获取分页结果
        $result = $this->_model->getPageResult();
        //获取商品类型的所有结果
        $goodsType = D("GoodsType")->getList();
        $this->assign("goodsType",$goodsType);
        $this->assign($result);
        $this->display();
    }

    /**
     * 添加商品规格
     */
    public function add(){
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->addSpec(I('post.'))===false){
                $this->error(get_error($this->_model));
            }else{
                $this->success("添加成功",U("index"));
            }
        }else{
            //回显所有商品类型
            $rows = D("GoodsType")->getList();
            $this->assign("rows",$rows);
            $this->display();
        }
    }

    public function edit($id){
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->saveSpec(I('post.'))===false){
                $this->error(get_error($this->_model));
            }else{
                $this->success("修改成功",U("index"));
            }

        }else{
            $row = $this->_model->find($id);
            //回显所有商品类型
            $rows = D("GoodsType")->getList();
            //回显商品规格项
            $data = M('GoodsSpecItem')->where(['spec_id'=>$id])->getField('id,item');
            $row['items'] = implode(PHP_EOL, $data);
            $this->assign("row",$row);
            $this->assign("rows",$rows);
            $this->display("add");
        }
    }
    
    /**
     * 移除规格
     * @param int $id
     */
    public function remove($id){
        $result = $this->_model->deleteSpec($id);
        if($result){
            $res = "success";
            $this->ajaxReturn($res);
        }else{
            $res = "error";
            $this->ajaxReturn($res);
        }
    }
}