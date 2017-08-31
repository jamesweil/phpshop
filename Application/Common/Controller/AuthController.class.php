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

namespace Common\Controller;
use Think\Controller;
use Admin\Model\GoodsClassModel;
use Admin\Model\AuthGroupModel;
use Admin\Model\AuthRuleModel;
use Think\Auth;
use Common\Model\BaseModel;
use Common\Tool\Tool;
use Common\TraitClass\NoticeTrait;
use Common\Model\PromotionTypeModel;
use Admin\Model\ProDiscountModel;
use Admin\Model\OrderModel;
use Common\Model\ExpressModel;
use Common\TraitClass\SmsVerification;
use Think\Hook;
use Common\Behavior\WangJinTing;

//权限认证
class AuthController extends Controller {
    
    use NoticeTrait;
    use SmsVerification;
    protected $addressModel = null;
    
    protected $cackeKey = 'EXPRESS_CACHE_DATA';
    
    
    protected $orderStatus = [2, 3, 4];//获取指定订单状态
    
    protected $title = '后台管理系统';
    
	protected function _initialize(){
	    
	    Hook::add('reade', WangJinTing::class);
		//session不存在时，不允许直接访问
		if(!session('aid')){
			$this->error('还没有登录，正在跳转到登录页',U('Public/login'));
		}

		//session存在时，不需要验证的权限
		$not_check = array('Index/index','Index/main',
				'Index/clear_cache','Index/edit_pwd','Index/logout');
		
		//当前操作的请求                 模块名/方法名
		if(in_array(CONTROLLER_NAME.'/'.ACTION_NAME, $not_check)){
			return true;
		}
		
// 		下面代码动态判断权限
// 		$auth = new Auth();
// 		if(!$auth->check(CONTROLLER_NAME.'/'.ACTION_NAME,session('aid')) && session('aid') != 1){
// 			$this->error('没有权限', U('Index/index'));
// 		}
		
		$this->assign('title', $this->title);
		
	}
	
	
	/**
	 * 获取分类 
	 */
	protected  function getClass()
	{
	    if (!S('classData'))
	    {
	        //获取商品分类
	        
            $classData =  BaseModel::getInstance(GoodsClassModel::class)->getChildren(array(
                GoodsClassModel::$hideStatus_d => 1,
                GoodsClassModel::$fid_d        => 0,
                GoodsClassModel::$type_d       => 1,
            ), array( GoodsClassModel::$id_d,  GoodsClassModel::$className_d, GoodsClassModel::$fid_d));
        
	        S('classData', $classData, 10);
	    }
	    return  S('classData');
	}
	
    
    /**
     * 获取登录的人的权限 
     */
    protected function getPromisson()
    {
        $group = AuthGroupModel::getInitnation()->getAuthGroupById('rules', array('id' => session('group_id')), 'find');
        
        if (empty($group))
        {
            $this->error('抱歉，您没有任何权限');
        }
        
        //获取权限菜单
        $rule = AuthRuleModel::getInitnation()->getAuthGroupById('id,name,title', 'id in('.$group['rules'].')');
    } 
    
    /**
     * @param BaseModel $model
     * @return array
     */
    protected  function getGoodsClass(BaseModel $model)
    {
        if (!($model instanceof BaseModel))
        {
            return array();
        }
    
        $goodsClass = $model->getAttribute(array(
            'field' => array($model::$id_d, $model::$className_d, $model::$fid_d),
            'where' => array($model::$fid_d => 0, $model::$hideStatus_d => 1),
            'order' => array($model::$sortNum_d.' DESC')
        ), false, 'getAllClassId');
    
        //获取二级分类
        Tool::connect('parseString');
    
        $ids = Tool::characterJoin($goodsClass, 'id');
        if (!empty($ids))
        {
            $ids = str_replace('"', null, $ids);
            $children = $model->getAttribute(array(
                'field' => array($model::$id_d, $model::$className_d, $model::$fid_d),
                'where' => array($model::$fid_d => array('in', $ids), $model::$hideStatus_d => 1),
                'order' => array($model::$sortNum_d.' DESC')
            ), false, 'getAllClassId');
    
            $goodsClass = array_merge($goodsClass, (array)$children);
            Tool::connect("Tree");
            $goodsClass = Tool::makeTree($goodsClass, array('parent_key' => $model::$fid_d));
        }
        return $goodsClass;
    }
    
    //获取分类
    
    public  function getChildren($id = 'goods_class_id')
    {
        Tool::checkPost($_POST, array('is_numeric' => array($id)),true, array($id)) ? true : $this->ajaxReturnData(null, 0, '参数错误');
    
        $model = BaseModel::getInstance(GoodsClassModel::class);
        $goodsClass = $model->getAttribute(array(
            'field' => array($model::$id_d, $model::$className_d, $model::$fid_d),
            'where' => array($model::$fid_d => $_POST[$id], $model::$hideStatus_d => 1),
            'order' => array($model::$sortNum_d.' DESC')
        ), false, 'getAllClassId');
        $this->ajaxReturnData($goodsClass);
    }
    
    /**
     * 促销类型
     */
    protected  function getProType()
    {
        //获取促销类型配置
        $promotionTypeModel = BaseModel::getInstance(PromotionTypeModel::class);
    
        $promotionData      = $promotionTypeModel->getField(
            $promotionTypeModel::$id_d.','.$promotionTypeModel::$promationName_d.','.$promotionTypeModel::$status_d
            );
    
        $this->promotionTypeModel = PromotionTypeModel::class;
    
        $this->classData  = $promotionData;
    }
    
    /**
     * 获取顶级分类 
     */
    public function getTopClass (&$id, $forNumber = 2)
    {
        $this->errorNotice($id);
        
        $classModel = BaseModel::getInstance(GoodsClassModel::class);
        
        $classId  = $classModel->getTop($id, $forNumber);
        
        return $classId;
    }
    
    
    /**
     * 商品分类接口分级获取
     */
    public function goodsCategory(){
        $validata = ['class_name'];
        Tool::checkPost($_POST, ['is_numeric' => $validata], true, $validata) ? : $this->ajaxReturnData(null, 0, '参数错误');
    
        $result = D("GoodsClass")->getListByCondition($_POST['class_name']);
    
        $this->updateClient($result);
    }
    
    public function getAllClass()
    {
        $model = BaseModel::getInstance(GoodsClassModel::class);
        
        $result = $model->getOneAndSecondClass();
        
        $this->updateClient($result);
    }
    
    /**
     * 获取订单状态 
     */
    public function getOrderStatus ()
    {
        if(!($cache = S('order')))
        {
            //获取全部订单状态
            $orderModel = new \ReflectionClass(OrderModel::class);
        
            $data       = $orderModel->getConstants();
            Tool::connect('ArrayChildren', $data);
            //删除不是状态的属性
            $data = Tool::deleteByCondition();
            
            Tool::setData($data);
            //状态 改为键  value改为汉字提示；
            $data = Tool::changeKeyValueToPrompt( C('order'));
            $cache = $data;
            S('order', $data, 86400);
        }
        
        return $cache;
    }
    
    /**
     * 获取指定状态数据 
     */
    public function getAppointData ()
    {
        $orderData = $this->getOrderStatus();
        
        $flag = array();
        
        foreach ($orderData as $key => $value)
        {
            if (!in_array($key, $this->orderStatus, true)) {
                continue;
            }
            $flag[$key] = $value;
        }
        
        return $flag;
    }
    
    /**
     * 获取运送方式 
     */
    protected  function getExpress ($param, $model, $splitKey)
    {
        if (!is_object($model) || !is_object($this->addressModel)) {
            return array();
        }
        
        Tool::connect('parseString');
        //获取运送方式
        
        $expressModel = BaseModel::getInstance(ExpressModel::class);
        
        $param = $expressModel->getExpressData($param, $model, $this->cackeKey);
      
        //传递用户地址模型
        $param = $this->addressModel->goodsAdressByOrder($param, $splitKey);
      
        return $param;
    }
    
    /**
     * 促销值
     */
    public function getDataTypeValue()
    {
        Tool::checkPost($_POST, array('is_numeric' => array('id')), true, array('id')) ? true : $this->ajaxReturnData(null, 0, '操作失败');
    
        $model = BaseModel::getInstance(ProDiscountModel::class);
    
        $data  = $model->getAttribute(array(
            'field' => array($model::$proId_d, $model::$proDiscount_d),
            'where' => array($model::$proId_d => $_POST['id'])
        ));
        $this->ajaxReturnData($data);
    }
    
    /**
     * 获取图片宽高 
     */
    protected function getImageWidthAndHeight(array $config)
    {
        if (empty($config)) {
            return array();
        }
        
        $widthAndHeight = array();
        
        foreach ($config as $value)
        {
            $widthAndHeight[] = $this->getConfig($value);
        }
        
        return $widthAndHeight;
        
    }
}