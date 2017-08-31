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
use Admin\Model\PoopClearanceModel;
use Common\TraitClass\SearchTrait;
use Common\TraitClass\SmsVerification;
use Common\Tool\Tool;
use Common\Model\PromotionTypeModel;
use Admin\Model\GoodsModel;
use Admin\Model\CouponModel;

/**
 * 尾货清仓 
 */
class PoopClearanceController extends AuthController
{
    use SearchTrait;
    use SmsVerification;
    protected static $configMinStock;
    /**
     * 尾货促销列表 
     */
    protected function _initialize()
    {
        parent::_initialize();
        
        if (!self::$configMinStock) {
            self::$configMinStock = $this->getConfig('minStock');
        }
    }
    
    public function index()
    {
        $poopModel = BaseModel::getInstance(PoopClearanceModel::class);
        
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        Tool::isSetDefaultValue($_POST, array(GoodsModel::$title_d => ''));
        
        Tool::connect('ArrayChildren');
        
        $where = $poopModel->buildSearch($_POST, true);
        
        $desc = BaseModel::DESC;
        
        $data = $poopModel->getDataByPage([
            'field' => [
                $poopModel::$addTime_d,
                $poopModel::$updateTime_d
            ],
            'where' => $where,
            'order' => $poopModel::$sort_d. $desc.','.$poopModel::$updateTime_d.$desc
        ], 10, true);
        
        //传递促销类型表
        $proType     = BaseModel::getInstance(PromotionTypeModel::class);
        
        Tool::connect('parseString');
        
        $data['data'] = $proType->getDataByOtherModel($data['data'], $poopModel::$typeId_d, array(
            $proType::$id_d, $proType::$promationName_d
        ), $proType::$id_d);
        
        $data['data'] = $goodsModel->getDataByOtherModel($data['data'], PoopClearanceModel::$goodsId_d, array(
           GoodsModel::$title_d, GoodsModel::$id_d
        ), GoodsModel::$id_d);
        
        //优惠类型是不是代金券
        $couponModel = BaseModel::getInstance(CouponModel::class);
        
        $couponModel->setPoopKey(PoopClearanceModel::$expression_d);
        
        $data['data'] = $couponModel->getCouponData($data['data'], PoopClearanceModel::$typeId_d);
        
        
        $this->assign('couponModel', CouponModel::class);
        
        $this->startTime = $this->getConfig('start_time');
        
        $this->endTime  = $this->getConfig('end_time');
        
        $this->promotionData = $data;
        
        $this->goodsModel = GoodsModel::class;
        
        $this->proType       = PromotionTypeModel::class;
        
        $this->poopModel  = PoopClearanceModel::class;
        $this->display();
    }
    
    /**
     * 编辑 
     */
    public function editHtml($id)
    {
        //检测参数 是否正常
        $this->errorNotice($id);
        
        $poopModel = BaseModel::getInstance(PoopClearanceModel::class);
        
        //获取尾货清仓数据
        $data = $poopModel->find($id);
        //是否存在
        $this->prompt($data);
        
        //获取尾货清仓商品
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
        
        //是否是 代金券
        $conponModel = BaseModel::getInstance(CouponModel::class);
        
        $goodsData  = $goodsModel->getUserNameById($data[PoopClearanceModel::$goodsId_d], GoodsModel::$title_d);
        
        $this->getProType();
        $this->assign('data', $data);
        $this->assign('goodsData', $goodsData);
        $this->assign('goodsModel', GoodsModel::class);
        $this->poopModel  = PoopClearanceModel::class;
        $this->display();
    }
    
    /**
     * 添加尾货清仓
     */
    public function addHtml()
    {
        $poopModel = BaseModel::getInstance(PoopClearanceModel::class);
        
        $this->getProType();
        
        $this->poopModel  = PoopClearanceModel::class;
        
        $this->display();
    }
    
    /**
     * 添加促销商品数据
     */
    public function addProData()
    {
        Tool::checkPost($_POST, [
            'is_numeric' => ['expression', 'type_id']
        ], true, ['expression', 'type_id', 'goods_id']) ? true : $this->ajaxReturnData(null, 0, '操作失败');
    
    
        $model = BaseModel::getInstance(PoopClearanceModel::class);
    
        $promotionType = BaseModel::getInstance(PromotionTypeModel::class)->getUserNameById($_POST['type_id'], PromotionTypeModel::$status_d);
        
        $model->setPromotionType($promotionType);
        
        //是否存在
        $isExits = $model->getAttribute(array(
            'field' => array($model::$id_d),
            'where' => array(PoopClearanceModel::$goodsId_d => $_POST['goods_id'])
        ));
    
        $this->alreadyInDataPjax($isExits);
    
        $status = $model->addProGoods($_POST, BaseModel::getInstance(GoodsModel::class));
    
        $this->promptPjax($status, $model->getError());
    
        $this->updateClient(array('url' => U('index')), '操作');
    }
    
    /**
     * 保存 编辑数据 
     */
    public function saveEditData ()
    {
        Tool::checkPost($_POST, [
            'is_numeric' => ['expression', 'type_id', 'id']
        ], true, ['expression', 'type_id', 'goods_id', 'id']) ? true : $this->ajaxReturnData(null, 0, '操作失败');
        
        
        $promotionType = BaseModel::getInstance(PromotionTypeModel::class)->getUserNameById($_POST['type_id'], PromotionTypeModel::$status_d);
        
        $model = BaseModel::getInstance(PoopClearanceModel::class);
        
        $model->setPromotionType($promotionType);
        
        $status = $model->editPoop($_POST, BaseModel::getInstance(GoodsModel::class));
        
        $this->promptPjax($status, $model->getError());
    
        $this->updateClient(array('url' => U('index')), '操作');
        
    }
    
    /**
     * 删除尾货清仓 
     */
    public function deletePoopClear ()
    {
        $validate = array('id', 'goods_id');
        Tool::checkPost($_POST, array('is_numeric' => $validate), true, $validate) ? : $this->ajaxReturnData(null, 0, '失败');
        
        $model = BaseModel::getInstance(PoopClearanceModel::class);
        
        $status = $model->deleteData($_POST['id']);
        
        $this->promptPjax($status, '删除失败');
        
        //修改商品状态
        $status = BaseModel::getInstance(GoodsModel::class)->editStatus ($_POST[PoopClearanceModel::$goodsId_d]);
        $this->ajaxReturnData([
            'url' => U('index')
        ]);
        
    }
}
