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

use Common\Model\BaseModel;
use Common\TraitClass\ParsePromotionTrait;

/**
 * 尾货清仓 模型
 * @version 1.0.1
 * @author 王强
 */
class PoopClearanceModel extends BaseModel
{
    use ParsePromotionTrait;
    private static  $obj;

	public static $id_d;	//尾货清仓编号

	public static $status_d;	//是否限制时间购买 0  false 1 true

	public static $goodsId_d;	//商品编号

	public static $typeId_d;	//折扣类型

	public static $addTime_d;	//添加时间

	public static $updateTime_d;	//更新时间

	public static $expression_d;	//折扣值

	public static $sort_d;	//排序
    
	private $promotionType;
	
	protected $goodsId = 0;
	
	private $model;
	
    /**
     * 获取类的实例
     * @return \Admin\Model\PoopClearanceModel

	public static $endTime_d;	//活动结束时间

     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 添加尾货清仓产品 
     * @param array $post post 数据
     * @param BaseModel $model 其他模型对象
     * @param string $method 方法名
     * @return boolean
     */
    public function addProGoods(array $post, BaseModel $model, $method = 'add')
    {
        if (! $this->isEmpty($post) || !( $model instanceof BaseModel) || ! method_exists($this, $method)) {
            return false;
        }
        
        $id = (int)$post[static::$goodsId_d];
        
        $this->goodsId = $id;
        
        $isPuss = $this->validatePoop($post, $model);
        
        if (!$isPuss) {
            return false;
        }
        
        $this->startTrans();
        
        $status = $this->$method($post);
      
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        
        $saveData = [ // 尾货清仓修改状态
            $model::$id_d => $id,
            $model::$status_d => 1
        ];
        
        $status = $model->save($saveData);
      
        if (!$this->traceStation($status)) {
            return false;
        }
        $this->commit();
        return $status;
    }
    
    /**
     * 验证尾货清仓
     * @param array $post
     * @param BaseModel $model
     * @return boolean
     */
    protected function validatePoop (array $post,BaseModel $model)
    {
        $price = 0;
        if ($post[self::$typeId_d] != -1) {//检测价格
            $price = $model->getUserNameById($this->goodsId, $model::$priceMember_d);
        }
        
        $method = 'getPromotionType'.$this->promotionType;
        
        $this->expression = $post[self::$expression_d];
        
        $promotionPrice = $this->$method($price);
        
        if ($promotionPrice < 0) {
            $this->error = '优惠金额过大商品原价：'.$price.'，优惠价：'.$promotionPrice;
            return false;
        }
        return true;
    }
    
    /**
     * 编辑 尾货清仓
     * @param array $post
     * @param BaseModel $model
     * @return boolean
     */
    public function editPoop (array $post, BaseModel $model)
    {
        if (! $this->isEmpty($post) || !( $model instanceof BaseModel) ) {
            return false;
        }
        
        $this->goodsId = (int)$post[static::$goodsId_d];
        
        $status = $this->validatePoop($post, $model);
        
        if ($status === false) {
            return false;
        }
        
        $goodsId = $this->getUserNameById($post[self::$id_d], self::$goodsId_d);
        
        $this->model = & $model;
        
        $this->startTrans();
        
        $status = $this->isUpdateGoodsStatus((int)$goodsId);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        
        $status =  $this->save($post);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        $this->commit();
        return $status;
    }
   
    /**
     * 是否要更新 商品促销状态
     * @param int $goodsId
     * @return boolean
     */
    public function isUpdateGoodsStatus ($goodsId)
    {
        if ($goodsId == $this->goodsId) {
            return true;
        }
        
        $model = $this->model;
        
        $saveData = [ // 尾货清仓修改状态
            $model::$id_d =>  $this->goodsId,
            $model::$status_d => 1
        ];
        
        $status = $model->save($saveData);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        
        $saveData[$model::$id_d] = $goodsId;
        
        $saveData[$model::$status_d] = 0;
        
        $status  = $model->save($saveData);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        return true;
    }
    
    
    /**
     * 删除尾货清仓 
     * @param int $id
     * @return boolean
     */
    public function deleteData ($id)
    {
        if (($id = intval($id)) === 0) {
            return false;
        }
        
        $this->startTrans();
        
        $status = $this->delete($id);
       
        if (!$this->traceStation($status)) {
            return false;
        }
        $this->commit();
        return $status;
    }
    
    
    /**
     * 添加前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected function _before_insert(& $data, $options)
    {
        $data[static::$addTime_d]     = time();
        $data[static::$updateTime_d]  = time();
        
        return $data;
    }
    
    /**
     * 更新数据
     * {@inheritDoc}
     * @see \Think\Model::_before_update()
     */
    protected function _before_update(& $data, $options)
    {
        $data[static::$updateTime_d]  = time();
    
        return $data;
    }
    
    /**
     * @param field_type $promotionType
     */
    public function setPromotionType($promotionType)
    {
        $this->promotionType = $promotionType;
    }
}