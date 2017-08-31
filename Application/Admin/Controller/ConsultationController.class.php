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
use Common\Model\BaseModel;
use Common\Model\GoodsConsultationModel;
use Think\AjaxPage;
use Admin\Model\GoodsModel;
use Common\Tool\Tool;
use Admin\Model\AdminModel;
use Admin\Model\UserModel;

/**
 * 商品咨询 
 */
class ConsultationController extends AuthController
{
    public function lists ()
    {
        $model = BaseModel::getInstance(GoodsConsultationModel::class);
        
        Tool::isSetDefaultValue($_GET, array('p' => 1));
        
        $this->model = GoodsConsultationModel::class;
        $this->display();
    }
    
    /**
     * ajax 获取商品咨询 
     */
    public function ajaxGetCoulatation ()
    {
        $model = BaseModel::getInstance(GoodsConsultationModel::class);
        
        Tool::connect('ArrayChildren');
        
        $buildSearch = $model->buildSearch($_POST, true, array(GoodsConsultationModel::$content_d));
       
        //传递用户表
        if (!empty($_POST[GoodsConsultationModel::$userId_d])) {
            $userModel = BaseModel::getInstance(UserModel::class);
            
            $userSearch = $userModel->getSearchByUser($buildSearch[GoodsConsultationModel::$userId_d]);
            
            $buildSearch[GoodsConsultationModel::$userId_d] =$userSearch;
        }
       
        $data  = $model->getDataByAjax(15, AjaxPage::class, $buildSearch);
       
        //传递到商品表获取商品名称
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        Tool::connect('parseString');
        
        $data['data'] = $goodsModel->getDataByOtherModel($data['data'], GoodsConsultationModel::$goodsId_d, array(
            GoodsModel::$id_d .BaseModel::DBAS .GoodsConsultationModel::$goodsId_d,
            GoodsModel::$title_d
        ), GoodsModel::$id_d);

        //获取管理员名字
        $adminModel = BaseModel::getInstance(UserModel::class);
        
        $field = UserModel::$id_d.','.UserModel::$nickName_d;
        
        $data['data'] = $adminModel->getTemplateDataByMode($data['data'], GoodsConsultationModel::$parentId_d, $field);
        
        //获取注释
        $notes = $model->getComment(GoodsConsultationModel::$commentType_d.','.GoodsConsultationModel::$id_d.','.GoodsConsultationModel::$parentId_d);
     
        $this->notes = $notes;
        
        $this->data = $data;
        
        $this->goodsModel = GoodsModel::class;
        $this->model = GoodsConsultationModel::class; 
        $this->display();
    }
    
    /**
     * 咨询回复 
     */
    public function consulationInfo ($id)
    {
        $this->errorNotice($id);
        
        $model = BaseModel::getInstance(GoodsConsultationModel::class);
        
        $data = $model->getFindData($id, $model::$id_d);
        
        $this->prompt($data);
        
        //获取回复内容
        $reply = $model->getFindData($id, $model::$parentId_d, 'select');
        
        $userModel = BaseModel::getInstance(UserModel::class);
        
        $data[$model::$userId_d] = ($userName = $userModel->userInfoByConsulate($data[$model::$userId_d], UserModel::$userName_d)) ? '' : $userName[$userModel::$userName_d] ;
        
        $this->reply = $reply;
        $this->comment = $data;
        $this->model   = GoodsConsultationModel::class; 
        $this->display();
    }
    
    /**
     * 添加回复信息 
     */
    public function addReply()
    {
        $validate = array(
            'parent_id',
        );
        
        $mustExits = array_merge($validate, array('content'));
        
        Tool::checkPost($_POST, array('is_numeric' => $validate), true, $mustExits) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(GoodsConsultationModel::class);
        
        $status = $model->addContent($_POST);
        
        $this->promptPjax($status);
        
        $this->updateClient(array(
            'url' => U('consulationInfo', array('id' => $_POST[GoodsConsultationModel::$parentId_d]))
        ), '操作');
    }
    /**
     * ajax 设置是否显示 
     */
    public function isShow()
    {
        $checkValidate = array('id', 'is_show');
        Tool::checkPost($_POST, array('is_numeric' => $checkValidate), true, $checkValidate) ? true : $this->ajaxReturnData(null, 0, '操作失败');
        
        $status = BaseModel::getInstance(GoodsConsultationModel::class)->save($_POST);
        $this->updateClient($status, '操作');
    }
    /**
     * 删除问题
     */
    public function deleteConsulation ()
    {
        Tool::checkPost($_POST, array('is_numeric' => array('id')), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '删除失败');
        
        $status = BaseModel::getInstance(GoodsConsultationModel::class)->deleteAllConsulationById($_POST['id']);
        
        $this->updateClient($status, '删除');
    }
}