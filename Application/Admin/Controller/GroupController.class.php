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
use Admin\Model\GroupModel;
use Common\TraitClass\SearchTrait;
use Common\Tool\Tool;
use Admin\Model\GoodsModel;

/**
 * 团购管理
 */
class GroupController extends AuthController
{
    use SearchTrait;
    /**
     * 团购首页 
     */
    public function index()
    {
        $model = BaseModel::getInstance(GroupModel::class);
        
        Tool::isSetDefaultValue($_POST, array(GroupModel::$title_d => ''));
        
        Tool::connect('ArrayChildren');
        $where = $model->buildSearch($_POST, true);
        
        $data  = $model->getDataByPage(array(
            'field' => array(GroupModel::$updateTime_d, GroupModel::$createTime_d),
            'where' => $where,
            'order' => GroupModel::$createTime_d.BaseModel::DESC.','.GroupModel::$updateTime_d.BaseModel::DESC
        ), 10, true);
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        Tool::connect('parseString');
        
        $data['data'] = $goodsModel->getDataByOtherModel($data['data'], GroupModel::$goodsId_d, array(
            GroupModel::$id_d,
            GoodsModel::$priceMember_d
        ), GroupModel::$id_d);
        
        $this->data = $data;
        $this->goodsModel = GoodsModel::class;
        $this->model = GroupModel::class;
        
        return $this->display();
    }
    
    /**
     * 编辑页 
     */
    public function editGroupHtml($id)
    {
        $this->errorNotice($id);
        
        $model = BaseModel::getInstance(GroupModel::class);
        
        $data  = $model->getAttribute(array(
            'field' => array(
                GroupModel::$createTime_d,
                GroupModel::$updateTime_d
            ),
            'where' => array(
                GroupModel::$id_d => $id
            )
        ), true, 'find'); 
        
        $this->prompt($data);
       
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        $goodsData = $goodsModel->getAttribute(array(
            'field' => array(GoodsModel::$title_d),
            'where' => array(GoodsModel::$id_d => $data[GroupModel::$goodsId_d])
        ));
        
        $this->goodsData  = $goodsData;
        
        $this->goodsModel = GoodsModel::class;
        
        $this->groupModel = GroupModel::class;
        
        $this->data = $data;
        
        return $this->display();
        
    }
    
    /**
     * 保存团购 
     */
    public function saveGroup()
    {
        
        Tool::checkPost($_POST, array('is_numeric' => array('goods_id', 'price', 'goods_num')), true, array(
            'goods_id', 'price', 'goods_num', 'title', 'end_time','start_time'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(GroupModel::class);
         
        //是否存在
        
        $status = $model->addProGoods($_POST, 'save');
        
        $this->promptPjax($status, $model->getError());
        return $this->updateClient(array(
            'url' => U('index')
        ), '保存');
    }
    
    /**
     * 添加团购页面 
     */
    public function addGroupBuy()
    {
        BaseModel::getInstance(GroupModel::class);
        
        $this->groupModel = GroupModel::class;
        
        return $this->display();
    }
    
    /**
     * 添加 团购 
     */
    public function addGroupData()
    {
        Tool::checkPost($_POST, array('is_numeric' => array('goods_id', 'price', 'goods_num')), true, array(
            'goods_id', 'price', 'goods_num', 'title', 'end_time','start_time'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(GroupModel::class);
       
        //是否存在
        
        $data = $model->getAttribute(array(
            'field' => array(GroupModel::$id_d),
            'where' => array(GroupModel::$title_d => $_POST['title'])
        ));
        
        $this->alreadyInDataPjax($data);
        
        $status = $model->addProGoods($_POST);
        
        $this->promptPjax($status, '添加失败');
        return $this->updateClient(array(
            'url' => U('index')
        ), '添加');
    }
    
    /**
     * 删除 
     */
    public function deleteData()
    {
        Tool::checkPost($_POST, array('is_numeric' => array('id')), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '操作失败');
        
        $status = BaseModel::getInstance(GroupModel::class)->where(GroupModel::$id_d.'= "%s"', $_POST['id'])->delete();
        
        return $this->updateClient($status, '删除');
    }
}