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

namespace  Home\Model;

use Common\Model\BaseModel;
use Common\Tool\Tool;

/**
 * 购物车 模型 
 */
class GoodsCartModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//

	public static $userId_d;	//用户ID

	public static $goodsId_d;	//产品ID

	public static $goodsNum_d;	//商品数量

	public static $attributeId_d;	//商品属性编号

	public static $priceNew_d;	//套餐价

	public static $integralRebate_d;	//返利积分

	public static $updateTime_d;	//

	public static $createTime_d;	//

	public static $isDel_d;	//是否删除1-是，0否

	public static $buyType_d;	//购买类型, 1单品购买 2,套餐购买

	public static $wareId_d;	//发货仓库
    
	private $packageArray = array();


    public static function getInitnation()
    {
        $class = __CLASS__;
        return !(self::$obj instanceof $class) ? self::$obj = new self() : self::$obj;
    }
    
    // 添加购物车
    public function addCart(array $data)
    {
        
        if (empty($data) || !is_array($data))
        {
            return array();
        }
        $result = $this-> getAttribute(array(
            'field' => array(
                self::$id_d,
                self::$goodsNum_d,
                self::$isDel_d
            ),
            'where' => array(
                self::$userId_d => $_SESSION['user_id'],
                self::$goodsId_d  => $data[self::$goodsId_d]
            )
        ), false, 'find');
       
        // 购物车中无商品，添加一条新信息，购物车中已有且不是删除状态信息，则累加数量
        $id = 0;
       
        $data[self::$userId_d] = $_SESSION['user_id'];
      
        if(empty($result)){
            // 获取商品价格
            if ($data['buy_type'] == 2) {
                $temp = M('goodsPackage')->field('discount')->find($data['goods_id']);
                $temp = empty($temp['discount']) ? 0 : $temp['discount'];
            } else {
                $temp = M('goods')->field('price_member')->find($data['goods_id']);
                $temp = empty($temp['price_member']) ? 0 : $temp['price_member'];
            }
            $data['price_new'] = $temp;
            $id = $this->add($data);

        }else{
            if ($result[self::$isDel_d] == 1) { // 如果是删除状态,则修改状态,不需要再累加数量
                $data[self::$isDel_d]    = 0;
            } else { // 不是删除状态,累加之前的数据
                $data[self::$goodsNum_d] = $result[self::$goodsNum_d] + $data[self::$goodsNum_d];
            }
            
            $id = $this->where(self::$id_d.'="'.$result[self::$id_d].'"')->save($data);
        }
        return empty($id) ? false : true;
    }
    
    protected function _before_insert(& $data, $options)
    {
        $data[self::$updateTime_d] = time();
        $data[self::$createTime_d] = time();
        return $data;
    }
    
    protected function _before_update(& $data, $options)
    {
        $data[self::$updateTime_d] = time();
        return $data;
    }
    
    
    /**
     * 获取购物车数量 
     */
    public function getCartCount(array $options)
    {
       $isSuccess =  \Common\Tool\Tool::checkPost($options);
       
       if (!$isSuccess) {
           return false;
       }
       
       $count = $this->where($options)->count();
       
       return $count;
    }


    /**
     * 获取最后添加的一个商品
     */
    public function getLastGoods(array $options, BaseModel $model)
    {
        $isSuccess =  \Common\Tool\Tool::checkPost($options);
        if (!$isSuccess || !($model instanceof BaseModel)) {
            return false;
        }
        
        $data =  $this->find($options);
       
        if (!empty($data)) {
           $goods = $model->field('title,description')->where('id = "'.$data['goods_id'].'"')->find();
           $data = array_merge((array)$goods, $data);
        }
        return $data;
    }
   

    /**
     * 获取购物车商品
     * @param  int    $user_id  用ID
     * @param  int    $buy_type 购买类型 1为普通购买 2套餐购买
     * @return array
     */
    public function getCartGoods($user_id, $buy_type = 1)
    {
        $ids = '';
        $field = 'id as cart_id, goods_id, price_new, goods_num';
        $where = ['user_id'=>$user_id, 'buy_type'=>$buy_type, 'is_del'=>0];
        $temp  = $this->field($field)->where($where)->select();
        foreach ($temp as $vo) {
            $ids .= ','.$vo['goods_id'];
            $list[$vo['goods_id']] = $vo;
        }
        $where = ['id' => ['in', trim($ids, ',')]];
        switch ($buy_type) {
            case 1:
                $field = 'id, title, price_market, price_member, stock, status';
                $temp  = M('goods')->field($field)->where($where)->select();
                foreach ($temp as $vo) {
                    if ($vo['status'] == 3) {
                        unset($list[$vo['id']]);
                    } else {
                        $vo['pic_url'] = D('goods')->image($vo['id']);
                        $list[$vo['id']] = array_merge($vo, $list[$vo['id']]);
                    }
                }
                break;

            case 2:
                $field = 'id,total,discount';
                $package_list = M('goodsPackage')->field($field)->where($where)->select();
                $field = 'id as goods_id, title, price_market, price_member, stock, status';
                foreach ($package_list as $vo) {
                    $list[$vo['id']] = array_merge($vo, $list[$vo['id']]);

                    $str   = '';
                    $temp1 = [];
                    $temp2 = M('goodsPackageSub')->where(['package_id'=>$vo['id']])->select();
                    foreach ($temp2 as $vo2) {
                        $str .= ',' . $vo2['goods_id'];
                        $temp1[$vo2['goods_id']] = $vo2;
                    }

                    // 套餐商品列表
                    $temp3 = M('goods')->field($field)->where(['id'=>['in', trim($str, ',')]])->select();
                    foreach ($temp3 as $vo3) {
                        // 规格
                        $vo3['spec']    = D('goods')->spec($vo3['goods_id']);

                        // 图片
                        $vo3['pic_url'] = D('goods')->image($vo3['goods_id']);

                        // 保存商品信息
                        $temp1[$vo3['goods_id']] = array_merge($vo3, $temp1[$vo3['goods_id']]);
                    }
                    $list[$vo['id']]['sub'] = $temp1;
                }
                break;

            default:
                # code...
                break;
        }
        return $list;
    }


    /**
     * 获取最近一个删除的商品
     * @param  integer $user_id 用户id
     * @return array
     */
    public function getLastDelete($user_id = 1)
    {
        $where = [
            'user_id'     => $user_id,
            'is_del'      => 1,
            'update_time' => ['gt', strtotime('-1 month')]
        ];
        $field = 'id as cart_id, goods_id, goods_num, price_new, buy_type';
        $info  = $this->field($field)->where($where)->order('update_time DESC')->find();
        $buy_type = $info['buy_type'];
        if ($buy_type == 1) {
            $field = 'id as goods_id, title, price_market, price_member, stock, status';
            $data  = M('goods')->field($field)->find($info['goods_id']);
            $info  = array_merge($info, $data);
        } elseif ($buy_type == 2) {
            $data = M('goodsPackage')->field('total,discount')->find($info['goods_id']);
            $info['title']        = '套餐';
            $info['price_market'] = $data['total'];
            $info['price_member'] = $data['discount'];
        }
        return $info;
    }


    /**
     * 通过cart_ID获取多个商品信息
     * 需要区分购物车商品是套餐商品还是普通商品
     * 
     * @param  int|array $ids 商品信息
     * @return array
     */
    public function getCartGoodsById($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        // 获取普通商品
        $field = 'c.id as cart_id ,c.goods_id, c.price_new, g.title, g.price_market, g.price_member, g.stock, g.min_yunfei,c.goods_num,g.status,g.description, c.ware_id';
        $where = 'c.id in ('.$ids.') and c.buy_type=1';
        $data  = $this->alias('c')->join('__GOODS__ as g ON g.id=c.goods_id')->where($where)
            ->field($field)->order('c.update_time DESC')->limit($limit)->select();

        // 获取套餐商品
        $sql = 'select c.id as cart_id,p.id as package_id,discount,total,goods_num from db_goods_cart as c,db_goods_package as p'
            .' where c.goods_id=p.id and c.buy_type=2 and c.id in ('.$ids.')';
        $temp = M()->query($sql);
        if (is_array($temp) && count($temp) > 0) {
            $list = array();
            $ids  = '';
            foreach ($temp as &$vo) {
                $ids .= ','.$vo['package_id'];
                $list[$vo['package_id']] = $vo;
            }
            $ids = trim($ids, ',');
            $sql = 'select package_id,goods_id,title,discount,description,price_member,price_market from db_goods_package_sub as s,db_goods as g '
                .'where s.goods_id=g.id and s.package_id in ('.$ids.')';
            $temp = M()->query($sql);
            foreach ($temp as $vo) {
                $list[$vo['package_id']]['sub'][] = $vo;
            }
        }
        return ['common'=>$data, 'package'=>$list];
    }
    
    /**根据购物车编号 查询数据 
     * @param array $cartData 购物车数组
     * @param integer $userId 用户编号
     */
    public function getCartDataByUserId( $cartId, $filter = FALSE)
    {
        if (empty($cartId)) {
            return array();
        }
        
        
        $field = $this->selectColums;
        
        $field = empty($field) ? $this->getDbFields() : $field;
        
        $data = $this->field($field, $filter)->where(self::$id_d .' in ('.addslashes($cartId).')')->select();
       
        if (empty($data)) {
            $this->error = '没有购物车数据';
            return array();
        }
        $flag = array();
        $total = $number =  0;
       
        foreach ($data as $key => & $value) {
            if ($value[self::$buyType_d] == 2) {//套餐购买
                $flag[$key] = $value;
                unset($data[$key]);
            } else {
                $number += $value[self::$goodsNum_d];
            }
            $total  += $value[self::$priceNew_d] * $value[self::$goodsNum_d];
            $value[self::$priceNew_d] = (float)$value[self::$priceNew_d];
        } 
        self::$totalMonery += sprintf('%01.2f', $total*$_SESSION['discount']/100);
        $this->packageArray = $flag;
        self::$number = $number;
        return $data;
    }

    /**
     * 将购物车商品移动到收藏夹
     * @param  integer $goods_id   商品ID
     * @param  integer $user_id 用户ID
     * @return boolean|integral
     */
    public function moveCollection($goods_id,$user_id)
    {
        $model = D('collection');
        $where = ['goods_id'=>$goods_id,'user_id'=>$user_id];

        // 检测收藏夹是否有改商品
        $info  = $model->field('id')->where($where)->find();
        if (is_array($info) && count($info) > 0) {
            return $info['id'];
        }

        // 保存到收藏夹,且从购物车移除改商品
        $this->startTrans();
        $goods = D('goods')->field('id,title,class_id')->where(['id'=>$goods_id])->find();
        $data = [
            'goods_id'   => $goods['id'],
            'user_id'    => $user_id,
            'goods_name' => $goods['title'],
            'class_id'   => $goods['class_id'],
            'add_time'   => time()
        ];
        $ret = $model->add($data);
        if (!$ret) {
            $this->rollback();
            return false;
        }
        $ret = $this->where($where)->delete();
        if (!$ret) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return $ret;
    }


    /**
     * 修改购物车商品数量
     * @param  integer $cart_id   商品ID
     * @param  integer $goods_num 商品
     * @return boolean
     */
    public function update_num($cart_id, $goods_num)
    {
        if ($goods_num < 1) {
            $goods_num = 1;
        }
        return $this->where(['id'=>$cart_id])->save(['goods_num'=>$goods_num]);
    }


    /**
     * 获取购物车列表
     * @param  integer $cart_id   商品ID
     * @param  integer $goods_num 商品
     * @return boolean
     */
    public function getCartGoodsByUser($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        $where['user_id'] = $user_id;
        $where['is_del']  = '0';
        $data = M('GoodsCart')->where($where)->select();
        return $data;
    }


    /**
     * 计算邮费
     * @param  array  $ids     购物车ID
     * @param  boolean $expand 是否显示每一件商品的邮费
     * @return array
     */
    public function postage($ids, $expand=false)
    {
        // 1.获取商品列表
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        $field = 'c.id as cart_id, c.goods_id, c.ware_id, c.goods_num, g.title, g.min_yunfei';
        $goods_list = $this->field($field)->alias('c')->join('__GOODS__ as g ON c.goods_id=g.id')->where(['c.id'=>['in', $ids]])->select();

        // 2.到运费方式表里查询获取模板编号
        $data  = [];
        $total = 0.00;
        foreach ($goods_list as $val) {
            if (intval($val['min_yunfei'])) { // 包邮
                $temp['price'] = 0;
                
            } else { // 不包邮
                $temp['price'] = 10;
            }
            $total += $temp['price'];
            if ($expand) {
                $temp['cart_id']  = $val['cart_id'];
                $temp['goods_id'] = $val['goods_id'];
                $data[]           = $temp;
            }
        }

        if ($expand == false) {
            return $total;
        }

        return ['list'=>$data, 'total'=>$total];

        // TODO:: 计算方式待定

        // 先到运费方式表里查询获取模板编号；
        // 再到运费模板里确认【是按件 、还是按积、重量】默认按件；
        // 再确认是否包邮，包邮运费为0 就不需要在计算了；
        // 再确认是否指定条件包邮【是】
        // {
        //      再根据 收货地址 确认 是否在包邮地区内，是的话
        //      根据 包邮条件表计算 包邮费用
        // }
        // 不是的话，去配送地区表 查看收货地址是否包含在内 ，是的话 再去 计算运费
        // 没有 就没有配送方式。【附链接http://www.cnblogs.com/lintao0823/p/4230425.html】
    }


    /**
     * 获取套餐列表
     * @param  integer $package_id 商品id
     * @return array
     */
    public function package_list($package_id)
    {
        // 获取套餐信息
        $field = 'id as package_id,total,discount,create_time,update_time';
        $info  = M('goodsPackage')->field($field)->find($package_id);
        if (!is_array($info) || count($info) < 1) {
            return [];
        }

        // 获取商品
        $list  = [];
        $field = 'goods_id,discount';
        $temp  = M('goodsPackageSub')->field($field)->where(['package_id'=>$package_id])->select();
        foreach ($temp as $vo) {
            $ids .= ','.$vo['goods_id'];
            $list[$vo['goods_id']] = $vo;
        }
        $field = 'id as goods_id, title, price_market, price_member, stock, min_yunfei';
        $goods_list = M('goods')->field($field)->where(['id' => ['in', trim($ids, ',')]])->select();

        foreach ($goods_list as $vo) {
            // 规格
            $vo['spec']    = D('goods')->spec($vo['goods_id']);
            // 图片
            $vo['pic_url'] = D('goods')->image($vo['goods_id']);
            // 合并数据
            $list[$vo['goods_id']] = array_merge($vo, $list[$vo['goods_id']]);
        }

        $info['sub'] = $list;
        return $info;
    }
    
    /**
     * 删除购物车数据 
     */
    public function delCart(array $cart)
    {
        if (!$this->isEmpty($cart)) {
            $this->rollback();
            return false;
        }
       
        $status = $this->where(self::$id_d .' in ('.implode(',', $cart).')')->delete();
        
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return $status;
    }
    /**
     * @return the $packageArray
     */
    public function getPackageArray()
    {
        return $this->packageArray;
    }
    
    /**
     * @param multitype: $packageArray
     */
    public function setPackageArray($packageArray)
    {
        $this->packageArray = $packageArray;
    }
}
