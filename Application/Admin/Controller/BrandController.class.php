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
use Admin\Model\GoodsClassModel;
use Common\Model\BaseModel;
use Common\Tool\Tool;
use Admin\Model\BrandModel;
use Admin\Model\GoodsModel;

/**
 * 品牌控制器
 * @author Admin
 */
class BrandController extends AuthController
{
    /**
     * 首页
     */
    public function index()
    {
        $model = BaseModel::getInstance(BrandModel::class);
        
        Tool::connect('ArrayChildren');
        
        $where = $model->buildSearch($_POST);
        $attribute = $model->getDataByPage(array(
            'field' => array(
                $model::$createTime_d
            ),
            'order' => array(
                $model::$createTime_d . ' DESC'
            ),
            'where' => $where
        ), 10, true);
        
        // 连接字符串工具
        $goodsModel = BaseModel::getInstance(GoodsClassModel::class);
        Tool::connect('parseString');
        
        $attribute['data'] = $goodsModel->getDataByOtherModel($attribute['data'], BrandModel::$goodsClassId_d, [
            GoodsClassModel::$id_d,
            GoodsClassModel::$className_d
        ], GoodsClassModel::$id_d);
        
        $this->brand = $attribute;
        $this->model = BrandModel::class;
        $this->classModel = GoodsClassModel::class;
        $this->assign('title',C('title'));
        return $this->display();
    }

    /**
     * 添加 品牌页面
     */
    public function addBrand()
    {
        $model = BaseModel::getInstance(GoodsClassModel::class);
        
        $goodsClass = $this->getGoodsClass($model);
        
        $this->goodsClass = $goodsClass;
        
        $this->model = $model;
        $this->display();
    }

    /**
     * 获取分类
     */
    public function getClassById()
    {
        Tool::checkPost($_POST) ?: $this->ajaxReturnData(null, 0, '失败');
        
        $model = BaseModel::getInstance(GoodsClassModel::class);
        
        $data = $model->getClassById($_POST['goods_class_id']);
        
        $this->updateClient($data, '成功');
    }

    /**
     * 添加品牌
     */
    public function addBrands()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'goods_class_id'
            ),
            'brand_logo',
            'brand_banner'
        ), true, array(
            'goods_class_id',
            'brand_name',
            'brand_description'
        )) ?: $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(BrandModel::class);
        
        $status = $model->add($_POST);
        
        $this->updateClient($status, $model->getError());
    }

    /**
     * 是否推荐
     */
    public function isRecommend()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'recommend',
                'id'
            )
        ), true, array(
            'recommend',
            'id'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(BrandModel::class);
        
        $status = $model->save($_POST);
        $this->updateClient($status, '更新');
    }

    /**
     * 编辑页面
     */
    public function editBrandHtml()
    {
        Tool::checkPost($_GET, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->error('参数错误');
        
        $model = BaseModel::getInstance(BrandModel::class);
        
        $attribute = $model->getAttribute(array(
            'field' => array(
                $model::$createTime_d,
                $model::$updateTime_d
            ),
            'where' => array(
                $model::$id_d => $_GET['id']
            ),
            'order' => array(
                $model::$createTime_d . ' DESC'
            )
        ), true, 'find');
        
        $this->prompt($attribute, null);
        
        $goodsModel = BaseModel::getInstance(GoodsClassModel::class);
        
        $goodsClass = $this->getGoodsClass($goodsModel);
        
        $classId = $attribute[BrandModel::$goodsClassId_d];
        
        $parent = $goodsModel->getTop($classId, 1);
        
        $attribute['toClassId'] = $parent[GoodsClassModel::$id_d];
        
        $this->goodsClass = $goodsClass;
        
        $this->goodsModel = $goodsModel;
        $this->brand = $attribute;
        $this->model = $model;
        
        $this->display();
    }

    /**
     * 编辑
     */
    public function editBrands()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'goods_class_id',
                'cat_id'
            ),
            'brand_logo',
            'brand_banner'
        ), true, array(
            'goods_class_id',
            'brand_name',
            'cat_id',
            'brand_description'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(BrandModel::class);
        
        $status = $model->saveBrand($_POST, array(
            'where' => array(
                $model::$id_d => $_POST['id']
            )
        ));
        $this->promptPjax($status, $model->getError());
        $this->updateClient($status, '更新');
    }

    /**
     * 删除品牌
     */
    public function delBrand()
    {
        Tool::checkPost($_POST, array(
            'is_numeric' => array(
                'id'
            )
        ), true, array(
            'id'
        )) ? true : $this->ajaxReturnData(null, 0, '参数错误');
        
        $model = BaseModel::getInstance(BrandModel::class);
        
        $status = $model->delete($_POST);
        
        $this->updateClient($status, '删除');
    }
}