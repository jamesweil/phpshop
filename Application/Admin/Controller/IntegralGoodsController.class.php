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
use Admin\Model\GoodsModel;
use Admin\Model\BrandModel;
use Common\Model\BaseModel;
use Common\Tool\Tool;
use Think\Page;

/**
 * 积分商品管理
 * 后台仅需要设置积分管理
 */
class IntegralGoodsController extends AuthController 
{

	/**
	 * 获取积分商品的列表
	 */
	public function index()
	{
        $ig     = M('IntegralGoods'); 
        $key    = I('keywords',false);
        $where  = "1=1";
        $status = I('status', -1);

        if ($status != -1) {
            $where .= " AND i.status=".$status;
        }
        if ($key) {
            $where .= " AND g.title LIKE '%$key%'";
        }
        $count = $ig->alias('i')->join('db_goods AS g ON i.goods_id=g.id')->where($where)->count();
        $page  = new Page($count, 10);
        $field = 'i.id,i.goods_id,g.title,i.integral,g.price_member,g.stock,i.status,i.create_time,i.update_time';
        $list  = $ig->alias('i')->join('db_goods AS g ON i.goods_id=g.id')->field($field)->where($where)
        	->order('i.update_time DESC')->limit($page->firstRow.','.$page->listRows)->select();
        if (!is_array($list)) {
            $list = array();
        }
        $this->assign('page', $page->show());
        $this->assign('status', $status);
        $this->assign('keywords', $key);
        $this->assign('list', $list);
        $this->display();
	}


    /**
     * 显示页面
     */
    public function integral()
    {
        $id   = I('id', -1, 'intval');
        $info = array();
        if ($id !== -1) {
            $field = 'i.id,i.goods_id,i.delayed,i.money,g.title,i.integral,g.price_member,g.stock,i.status,i.create_time,i.update_time';
            $info  = M('IntegralGoods')->alias('i')->join('db_goods AS g ON i.goods_id=g.id')->field($field)->where('i.id='.$id)->find();
            $this->assign('info', $info);
        }
        $act = I('act',' add');
        $this->assign('act', trim($act));
        $this->display('edit');
    }


	/**
	 * 处理积分商品
	 */
	public function goodsHandle()
	{
        $data = I('post.');
        $act  = $data['act'];
        switch ($act) {
            case 'add':
                if (empty($data['goods_id'])) {
                    $ret = 0;
                } else {
                    $data['create_time'] = time();
                    $data['update_time'] = time();
                    $ret = M('integralGoods')->add($data);
                     // 修改为积分商品
                    D('goods')->save(['id'=>$data['goods_id'], 'status'=>3]);
                }
                break;
            case 'edit':
                $data['update_time'] = time();
                $ret = M('integralGoods')->save($data);
                break;
            case 'del':
                $where = 'id='.$data['del_id'];
                $ret = M('integralGoods')->where($where)->delete();
                // 移除活动
                D('goods')->save(['id'=>$data['del_id'], 'status'=>0]);
                break;
            default:
                break;
        }
        if ($act == 'del') {
            $this->ajaxReturn(intval($ret));
        }

        if (intval($ret)) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
	}


    /**
     * 搜索商品
     */
    public function searchGoods()
    {
        //获取商品数据
        $goodsModel = BaseModel::getInstance(GoodsModel::class);
    
        //组装筛选条件
        $static = (new \ReflectionObject($this))->getStaticProperties(); 
       
      
        $where = array();
        if (array_key_exists('configMinStock', $static)) {
          
            $where = array(GoodsModel::$stock_d => array('lt',static::$configMinStock));
        }
        
        Tool::connect("ArrayChildren");
        
        $initWhere = array_merge($where, array($goodsModel::$pId_d => array('gt', 0)));
    
        $where      = array_merge($initWhere, (array)$goodsModel->bulidWhere($_POST));
       
        $goodsData = $goodsModel->getDataByPage(array(
            'field' => array($goodsModel::$id_d, $goodsModel::$title_d, $goodsModel::$priceMember_d, $goodsModel::$stock_d),
            'where' => $where,
            'order' => $goodsModel::$createTime_d.BaseModel::DESC.','.$goodsModel::$updateTime_d.BaseModel::DESC
        ));
        //获取分类
        $goodsClassModel = BaseModel::getInstance(GoodsClassModel::class);
    
        $data = $goodsClassModel->getAttribute(array(
            'field' => array($goodsClassModel::$id_d, $goodsClassModel::$className_d),
            'where' => array($goodsClassModel::$hideStatus_d => 1)
        ));
    
    
        //获取品牌
        $brandModel = BaseModel::getInstance(BrandModel::class);
    
        $brandData = $brandModel->getAttribute(array(
            'field' => array($brandModel::$id_d, $brandModel::$brandName_d),
            'where' => array($brandModel::$recommend_d => 1)
        ));
    
        //设置默认值
        Tool::isSetDefaultValue($_POST, array(
            $goodsModel::$brandId_d => null,
            $goodsModel::$classId_d => null,
            $goodsModel::$title_d   => null
        ));

        // 获取已经是积分商品
        if (is_array($goodsData['data']) && count($goodsData['data'])>0) {
            $ids = '';
            foreach ($goodsData['data'] as $goods) {
                $ids .= ','.$goods['id'];
            }
            $ids = substr($ids, 1);
            $id_list = M('integralGoods')->field('goods_id')->where("goods_id IN ($ids)")->select();
            if (is_array($id_list)) {
                $temp = array();
                foreach ($id_list as $id) {
                    $temp[$id['goods_id']] = $id['goods_id'];
                }
                $id_list = $temp;
            }
            foreach ($goodsData['data'] as &$goods) {
                if ($id_list[$goods['id']]) {
                    $goods['isIntegral'] = 1;
                } else {
                    $goods['isIntegral'] = 0;
                }
            }
        }
        
        $this->brandModel = $brandModel;
    
        $this->brandData  = $brandData;
    
        $this->classData = $data;
    
        $this->classModel = GoodsClassModel::class;
        $this->goodsData  = $goodsData;
    
        $this->goodsModel = GoodsModel::class;
    
        return $this->display();
    }

}