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
use Admin\Model\GoodsAttributeModel;
use Common\Model\BaseModel;
use Common\Tool\Tool;
use Think\Exception;
use Think\Model;
use Admin\Model\GoodsTypeModel;
use Common\Tool\Event;

class GoodsAttributeController extends AuthController
{
    private $checkNumber = array('type_id');
    
    private $checkIsExits = array('attr_name');
    
    /**
     * 商品属性列表页 
     */
    public function index()
    {
        BaseModel::getInstance(GoodsAttributeModel::class);
        
        $typeModel = BaseModel::getInstance(GoodsTypeModel::class);
        
        $parentData = $typeModel->getType();
        
        $this->assign('model', GoodsAttributeModel::class);
        
        $this->assign('parentClassData', $parentData);
        $this->display();
    }
    
    
    /**
     * ajax 获取数据 
     */
    public function ajaxGetData()
    {
        $attributeModel = BaseModel::getInstance(GoodsAttributeModel::class);
        
        Tool::connect('ArrayChildren');
        
        $where = $attributeModel->buildSearch($_POST, true);
        
        $attrData = $attributeModel->getList(C('PAGE_NUMBER'), $where);
       
        $typeModel = BaseModel::getInstance(GoodsTypeModel::class);
        
        Tool::connect('parseString');
        
        $attrData['data'] = $typeModel->getDataByGoodsAttribute($attrData['data'], GoodsAttributeModel::$typeId_d);
        
        $this->assign('attrModel', GoodsAttributeModel::class);
        
        $this->assign('input_type', C('input_type'));
        
        $this->assign('goodsType', GoodsTypeModel::class);
        
        $this->assign('attr_index', C('attr_index'));
        
        $this->assign('data', $attrData);
        
        $this->display();
    }
    
    /**
     * 添加商品属性页面 
     */
    public function addGoodsAttribute()
    {
        BaseModel::getInstance(GoodsAttributeModel::class);
        $typeModel = BaseModel::getInstance(GoodsTypeModel::class);
        
        $parentData = $typeModel->getType();
        
        $this->assign('parentClassData', $parentData);
        
        $this->assign('model', GoodsAttributeModel::class);
        
        $this->display();
    }
    
    /**
     * 辅助方法 
     */
    private function auxiliary(BaseModel $model)
    {
        if (!($model instanceof BaseModel))
        {
            throw new Exception('模型不匹配');
        }
        return $attribute = $model->getAttribute(array(
            'field' => array($model::$updateTime_d,$model::$createTime_d),
            'where' => array($model::$status_d => 1, $model::$pId_d => 0),
            'order' => array($model::$createTime_d.' DESC')
        ), true);
        
    }
    
    /**
     * 添加属性 
     */
    public function addAttr()
    {  
    
        $self = $this;
    
        Event::insetListen('isExits', function ($param)use($self){
            $model = $param['model'];
            
            $post = $param['post'];
            $status = $model->isExits ($post);
            return $self->alreadyInDataPjax($status);
            
        });
        $this->saveOrAddAuxiliary();
    }
    /**
     * 编辑 商品属性
     */
    public function editAttribute($id)
    {
        $this->errorNotice($id);
        
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        
        $children = $model->getAttribute(array(
            'field' => array($model::$updateTime_d,$model::$createTime_d),
            'where' => array($model::$id_d => $id),
        ),true,'find');
        
        
        $this->promptParse($children);
        
        $typeModel = BaseModel::getInstance(GoodsTypeModel::class);
        
        $parentData = $typeModel->getType();
        
        $this->assign('model', GoodsAttributeModel::class);
        
        $this->assign('parentClassData', $parentData);
        
        $this->current        = $children;
        
        $this->assign('input_type', C('input_type'));
        $this->display();
    }
    
    /**
     * 更改状态 
     */
    public function saveStatus ()
    {
        $validate =  ['id', 'attr_index'];
        
        $this->update($validate);
        
    }
    
    /**
     * 更新排序 
     */
    public function updateSort ()
    {
        $validate =  ['id', 'order'];
        
        $this->update($validate);
        
    }
    
    /**
     * 辅助 
     */
    private function update($validate)
    {
        Tool::checkPost($_POST, ['is_numeric' => $validate], true, $validate) ? : $this->ajaxReturnData(null, 0, '更新失败');
        
        $status = BaseModel::getInstance(GoodsAttributeModel::class)->save($_POST);
        
        $this->updateClient($status, '更新');
    }
    
    /**
     * 保存编辑 
     */
    public function saveEditAttribute()
    {
        
        $this->checkNumber[] = 'id';
        
        $this->checkIsExits[] = 'id';
        
        $this->saveOrAddAuxiliary('save', '更新');
        
    }
    
    private function saveOrAddAuxiliary($method = 'add', $message = '添加')
    {
        Tool::checkPost($_POST,array('is_numeric' => array('type_id'), 'type_id', 'attr_values'), true, array('attr_name')) ? : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        
        $param = ['post' =>$_POST, 'model' => $model];
        //是否存在
        Event::listen('isExits', $param);
        
       
        $status = $model->$method($_POST);
        
        $this->updateClient($status, $message);
    }
    
    /**
     * 删除 属性 
     */
    public function delGoodsAttribute()
    {
        Tool::checkPost($_POST,array('is_numeric' => array('id')), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        //子父级关系的处理删除
        $status = $model->delete(array(
            'where' => array($model::$id_d => $_POST['id'])
        ));
        $this->updateClient($status, '删除');
    }
}