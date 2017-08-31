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
use Admin\Model\GoodsClassModel;
use Think\Model;

class GoodsAttributeControllerddd extends AuthController
{
    public function index()
    {
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
         
        $attribute = $model->getDataByPage(array(
            'order' => array($model::$createTime_d.' DESC')
        ));
        
        
        $classModel = BaseModel::getInstance(GoodsClassModel::class);
        
        $attribute['data']  = $classModel->getClassNameByGoodsAttribute($attribute['data'], $model::$goodsClassId_d);
       
        //连接树形工具
        Tool::connect('Tree');
        $attribute['data'] = Tool::makeTree($attribute['data']);
        $this->data = $attribute;
        
        $this->model = $model;
        
        $this->display();
    }
    
    public function addGoodsAttribute()
    {
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        
        //获取商品分类
        $goodsClassModel = BaseModel::getInstance(GoodsClassModel::class);
        
        $data = $this->getGoodsClass($goodsClassModel);
        
       
        $this->goodsClassData = $data;
        $this->classModel = $goodsClassModel;
        $this->attributeClass = $this->auxiliary($model);
        $this->model = $model;
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
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        
        $this->saveOrAddAuxiliary($model);
    }
    /**
     * 编辑 商品属性
     */
    public function editAttribute()
    {
        Tool::checkPost($_GET,array('is_numeric' => array('id')), true, array('id')) ? true : $this->error('参数错误');
        
        
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        
        $children = $model->getAttribute(array(
            'field' => array($model::$updateTime_d,$model::$createTime_d),
            'where' => array($model::$id_d => $_GET['id']),
        ),true,'find');
        $this->prompt($children, null);
        
        //获取商品分类
        $goodsClassModel = BaseModel::getInstance(GoodsClassModel::class);
        
        $topId           = $goodsClassModel->getAttribute(array(
            'field' => array($goodsClassModel::$fid_d, $goodsClassModel::$className_d, $goodsClassModel::$id_d),
            'where' => array($goodsClassModel::$id_d => $children[$model::$goodsClassId_d])
        ), false, 'find');

        $data = $this->getGoodsClass($goodsClassModel);
        $this->goodsClassData = $data;
        //顶级分类
        $this->classModel = $goodsClassModel;
        $this->topId      = $topId;
        $this->attributeClass = $this->auxiliary($model);
        $this->model          = $model;
        $this->current        = $children;
        $this->display();
    }
    
    /**
     * 保存编辑 
     */
    public function saveEditAttribute()
    {
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        $this->saveOrAddAuxiliary($model, 'save', '更新');
    }
    
    private function saveOrAddAuxiliary(BaseModel $model, $method = 'add', $message = '添加')
    {
        if (!method_exists($model, $method) || !($model instanceof BaseModel) )
        {
            throw new Exception('模型不存在该方法【'.$method.'】');
        }
       
        
        Tool::checkPost($_POST,array('is_numeric' => array('p_id','status')), true, array('attribute')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        
        if ($method == 'add')
        {
            $data = $model->getAttribute(array(
                'field' => array($model::$attribute_d),
                'where' => array($model::$attribute_d => array('like', $_POST['attribute']) ),
                ),false,'find');
            
            if (!empty($data))
            {
                $this->ajaxReturnData(null, 0, '已存在该属性');
            }
        }
      
        $_POST[$model::$goodsClassId_d] = !empty($_POST['cat_id']) && is_numeric($_POST['cat_id']) ? $_POST['cat_id'] : $_POST[$model::$goodsClassId_d];
       
        $status = $model->$method($_POST);
        
        $this->updateClient($status, $message);
    }
    
    /**
     * 删除 属性 
     */
    public function deleteAttribute()
    {
        Tool::checkPost($_POST,array('is_numeric' => array('id')), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(GoodsAttributeModel::class);
        
        
        //练接字符串工具
        Tool::connect("parseString");
        
        //子父级关系的处理删除
        $status = $model->delete(array(
            'field' => array($model::$pId_d, $model::$id_d),
            'where' => array($model::$id_d => $_POST['id'])
        ));
        
        $this->updateClient($status, '删除');
    }
}