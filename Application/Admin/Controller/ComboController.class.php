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
use Admin\Model\BrandModel;
use Admin\Model\GoodsModel;
use Common\Model\BaseModel;
use Common\Tool\Tool;
use Think\Page;

/**
 * 推荐配件,最佳组合,优惠套餐
 */
class ComboController extends AuthController
{

    /**
     * 推荐配件
     */
    public function index()
    {
        $where = array();
        $goods_id = I('goods_id', -1, 'intval');
        if ($goods_id==-1 || empty($goods_id)) {
            $goods_id = '';
        } else {
            $where['goods_id'] = $goods_id;
        }

        $count= M('goodsAccessories')->where($where)->count();
        $page = new Page($count, PAGE_SIZE);
        $list = M('goodsAccessories')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        if (is_array($list) && count($list)) {
            foreach ($list as &$val) {
                $sub_ids = explode(',', $val['sub_ids']);
                $val['number'] = count($sub_ids);
            }
        }

        $this->assign('goods_id', $goods_id);
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display();
    }


    /**
     * 推荐配件处理
     */
    public function accHandle()
    {
        $act = I('GET.act');
        switch ($act) {
            case 'add':
                $data = I('POST.');
                $data['create_time'] = time();
                $data['update_time'] = time();
                $ret = M('goodsAccessories')->add($data);
                break;

            case 'save':
                $data = I('POST.');
                $data['update_time'] = time();
                $ret = M('goodsAccessories')->save($data);
                break;

            case 'del':
                $id  = I('POST.del_id');
                $ret = M('goodsAccessories')->where(['id'=>$id])->delete();
                break;

            case 'view':
                $id   = I('id');
                if ($id > 0) {
                    $data = M('goodsAccessories')->find($id);
                }
                if (is_array($data) && count($data) > 0) {

                    // 获取商品
                    $ids  = explode(',', $data['sub_ids']);
                    $info = M('goods')->field('id as goods_id,title,price_market,price_member,stock')->find($data['goods_id']);
                    $info['id']     = $id;
                    $info['status'] = $data['status'];
                    
                    // 获取配件列表
                    $where['id'] = ['in', $ids];
                    $list = M('goods')->field('id as goods_id,title,price_market,price_member,stock')->where($where)->select();
                }
                $this->assign('list', $list);
                $this->assign('info', $info);
                $this->display('edit');
                exit;

            default:
                # code...
                break;
        }
        $ret = intval($ret>0);
        if (IS_AJAX) {
            $this->ajaxReturn($ret);
            exit;
        }

        if ($ret) {
            $this->success('编辑成功!');
        } else {
            $this->error('编辑失败!');
        }
    }


    /**
     * 最佳组合
     */
    public function combo()
    {
        $where = array();
        $goods_id = I('goods_id', -1, 'intval');
        if ($goods_id==-1 || empty($goods_id)) {
            $goods_id = '';
        } else {
            $where['goods_id'] = $goods_id;
        }

        $count= M('goodsCombo')->where($where)->count();
        $page = new Page($count, PAGE_SIZE);
        $list = M('goodsCombo')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        if (is_array($list) && count($list)) {
            foreach ($list as &$val) {
                $sub_ids = explode(',', $val['sub_ids']);
                $val['number'] = count($sub_ids);
            }
        }

        $this->assign('goods_id', $goods_id);
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display('combo_list');
    }


    /**
     * 组合处理
     */
    public function comboHandle()
    {
        $act = I('GET.act', '');
        if (empty($act)) {
            $act = I('POST.act', '');
        }
        switch ($act) {
            case 'add':
                $data = I('POST.');
                $data['create_time'] = time();
                $data['update_time'] = time();
                $ret = M('goodsCombo')->add($data);
                break;

            case 'save':
                $data = I('POST.');
                $data['update_time'] = time();
                $ret = M('goodsCombo')->save($data);
                break;

            case 'del':
                $id  = I('POST.del_id');
                $ret = M('goodsCombo')->where(['id'=>$id])->delete();
                break;

            case 'view':
                $id = I('id');
                if ($id > 0) {
                    $data = M('goodsCombo')->find($id);
                }
                if (is_array($data) && count($data) > 0) {

                    // 获取商品
                    $ids  = explode(',', $data['sub_ids']);
                    $info = M('goods')->field('id as goods_id,title,price_market,price_member,stock')->find($data['goods_id']);
                    $info['id']     = $id;
                    $info['status'] = $data['status'];
                    
                    // 获取配件列表
                    $where['id'] = ['in', $ids];
                    $list = M('goods')->field('id as goods_id,title,price_market,price_member,stock')->where($where)->select();
                }
                $this->assign('list', $list);
                $this->assign('info', $info);
                $this->display('combo_edit');
                exit;

            default:
                # code...
                break;
        }
        $ret = intval($ret>0);
        if (IS_AJAX) {
            $this->ajaxReturn($ret);
            exit;
        }

        if ($ret) {
            $this->success('编辑成功!');
        } else {
            $this->error('编辑失败!');
        }
    }


    /**
     * 优惠套餐
     * 查询某一个商品具有的套餐
     * 多对多关系,一个商品可能在多个套餐中
     */
    public function package()
    {
        $where    = '';
        $goods_id = I('goods_id', '', 'intval');

        // 通过商品id反查package
        if ($goods_id > 0) {
            $ids = M('goodsPackageSub')->field('package_id')->where(['goods_id'=>$goods_id])->select();
            foreach ($ids as $id) {
                $str .= ','.$id['package_id'];
            }
            if (!empty($ids)) {
                $where = ' id IN ('.trim($str, ',').') ';
            }
        }
        $count = M('goodsPackage')->where($where)->count();
        $page  = new Page($count, PAGE_SIZE);
        $sql   = 'select p.*, (select count(1) from db_goods_package_sub as s where p.id=s.package_id)'
            .' as number from db_goods_package as p ';
        $limit = ' LIMIT '.$page->firstRow.','.$page->listRows;
        $order = ' ORDER BY id DESC';
        $where = empty($where) ? '' : ' WHERE '.$where;
        $sql = $sql.$where.$order.$limit;
        $list  = M()->query($sql);

        $this->assign('goods_id', $goods_id);
        $this->assign('list', $list);
        $this->assign('page', $page->show());
        $this->display('package_list');
    }


    /**
     * 套餐处理
     * 套餐中的主商品价格一并保存得到:套餐商品表
     */
    public function packageHandle()
    {
        $act = I('GET.act');
        $act = empty($act) ? I('POST.act') : $act;
        switch ($act) {
            case 'add':
                $data = I('POST.');
                $data['create_time'] = time();
                $data['update_time'] = time();

                M()->startTrans();
                $insertID = M('goodsPackage')->add($data);
                if ($insertID < 1) {
                    M()->rollback();
                    break;
                }

                // 添加套餐商品
                $sub_ids = explode(',', $data['sub_ids']);
                foreach ($sub_ids as $val) {
                    list($goods_id, $discount) = explode(':', $val);
                    $package_list[] = ['goods_id'=>$goods_id, 'discount'=>$discount];
                    $ids .= ','.$goods_id;
                }
                $discount_total = 0;
                foreach ($package_list as $val) {
                    $discount_total += $val['discount'];
                    $temp = ['package_id'=>$insertID, 'goods_id'=>$val['goods_id'], 'discount'=>$val['discount']];
                    $ret  = M('goodsPackageSub')->add($temp);
                    if ($ret < 1) {
                        M()->rollback();
                        break;
                    }
                }

                // 计算价格
                $ids = trim($ids, ',');
                $sql = "select sum(price_member) as total from db_goods where id in ($ids)";
                $ret = M()->query($sql);
                $total = $ret[0]['total'];
                $ret = M('goodsPackage')->save(['id'=>$insertID, 'total'=>$total, 'discount'=>$discount_total]);

                $ret = M()->commit();
                break;

            case 'save':
                $data                = I('POST.');
                $data['update_time'] = time();
                $package_list        = [];
                $sub_ids             = explode(',', $data['sub_ids']);
                foreach ($sub_ids as $val) {
                    list($goods_id, $discount) = explode(':', $val);
                    $package_list[] = ['goods_id'=>$goods_id, 'discount'=>$discount];
                }

                // 保存套餐数据
                M()->startTrans();
                $ret = M('goodsPackageSub')->where(['package_id'=>$data['id']])->delete();
                if ($ret < 1) {
                    M()->rollback();
                    break;
                }
                unset($data['sub_ids']);
                $ret = M('goodsPackage')->save($data);
                if ($ret < 1) {
                    M()->rollback();
                    break;
                }

                $ids = '';
                $discount = 0;
                foreach ($package_list as $val) {
                    $ids .= ','.$val['goods_id'];
                    $discount += $val['discount'];
                    $temp = ['package_id' => $data['id'], 'goods_id'=>$val['goods_id'], 'discount'=>$val['discount']];
                    $ret  = M('goodsPackageSub')->add($temp);
                    if ($ret < 1) {
                        M()->rollback();
                        break;
                    }
                }

                // 计算价格
                $ids = trim($ids, ',');
                $sql = "select sum(price_member) as total from db_goods where id in ($ids)";
                $ret = M()->query($sql);
                $total = $ret[0]['total'];
                $ret = M('goodsPackage')->save(['id'=>$data['id'], 'total'=>$total, 'discount'=>$discount]);

                $ret = M()->commit();
                break;

            case 'del':
                $id  = I('POST.del_id');
                $ret = M('goodsPackage')->where(['id'=>$id])->delete();
                $ret = M('goodsPackageSub')->where(['package_id'=>$id])->delete();
                break;

            case 'view':
                $id = I('id');
                if ($id > 0) {
                    $data = M('goodsPackage')->find($id);
                }
                if (is_array($data) && count($data) > 0) {
                    $temp = M('goodsPackageSub')->field('package_id,goods_id,discount')->where(['package_id' => $id])->select();
                    $list = array();
                    foreach ($temp as $vo) {
                        $ids .= ','.$vo['goods_id'];
                        $list[$vo['goods_id']] =$vo;
                    }

                    $total      = 0;
                    $discount   = 0;
                    $field      = 'id as goods_id,title,price_market,price_member,stock';
                    $goods_list = M('goods')->field($field)->where(['id'=>['in', trim($ids, ',')]])->select();
                    foreach ($goods_list as $vo) {
                        $total                 += $vo['price_member'];
                        $discount              += $list[$vo['goods_id']]['discount'];
                        $list[$vo['goods_id']]  = array_merge($vo, $list[$vo['goods_id']]);
                        if ($vo['goods_id'] == $data['goods_id']) {
                            $info = $list[$vo['goods_id']];
                            unset($list[$vo['goods_id']]);
                        }
                    }
                }
                $total    = sprintf('%.2f', $total);
                $discount = sprintf('%.2f', $discount);
                $this->assign('list', $list);
                $this->assign('info', $info);
                $this->assign('total', $total);
                $this->assign('discount', $discount);
                $this->assign('package_id', $id);
                $this->display('package_edit');
                exit;

            default:
                # code...
                break;
        }
        $ret = intval($ret>0);
        if (IS_AJAX) {
            $this->ajaxReturn($ret);
            exit;
        }

        if ($ret) {
            $this->success('编辑成功!');
        } else {
            $this->error('编辑失败!');
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
        
        // 获取父类商品,绑定主商品的时候需要
        $pid = I('pid', -1);
        if ($pid == -1) {
            $initWhere = array_merge($where, array($goodsModel::$pId_d => array('gt', 0)));
        } else {
            $initWhere = array_merge($where, array($goodsModel::$pId_d => 0));
        }
    
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

        
        // 配置是否可多选
        $this->multi = I('multi', 0);
        $this->eleID = I('eleID', 0);

        // 设置选中的列表
        $id_list = explode(',', I('id_list'));
        if (is_array($id_list) && count($id_list)>0) {
            foreach ($goodsData['data'] as &$goods) {
                if (in_array($goods['id'], $id_list)) {
                    $goods['selected'] = 1;
                } else {
                    $goods['selected'] = 0;
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


