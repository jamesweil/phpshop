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

use Think\Model;

/**
 * 购物车 模型 
 */
class CollectionModel extends Model
{

    /**
     * 拉取用户收藏夹商品
     * 
     * @param  integer $user_id 用户ID
     * @param  string $limit 分页
     * @return array
     */
    public function goodsByuser($user_id = 0, $limit = '0,6')
    {
        $field = 'c.goods_id as id, g.title, g.price_member as price';
        $where = ['c.user_id'=>$user_id , 'g.p_id'=>['neq', 0]];
        $data  = $this->alias('c')->join('__GOODS__ as g ON g.id=c.goods_id')->field($field)
            ->where($where)->order('c.add_time DESC')->limit($limit)->select();

        $model      = D('goods');
        $spec_model = M('specGoodsPrice');
        foreach ($data as &$goods) {
            $goods['pic_url'] = $model->image($goods['id']);
            $spec = $spec_model->field('price, store_count')->where(['goods_id'=>$goods['id']])->find();
            if ($spec) {
                $goods['price'] = $spec['price'];
            }
        }
        return $data;
    }


    /**
     * 添加到收藏夹
     * 注意:保存到收藏夹需要检测是否已经保存过了,保存过后返回ID
     * @param  array $data 保存到收藏夹的信息
     * @return collocationID
     */
    public function store($data)
    {
        $info = $this->field('id')->where(['goods_id'=>$data['goods_id'], 'user_id'=>$data['user_id']])->find();
        if (is_array($info) && count($info) > 0) {
            return $info['id'];
        }
        $data['add_time'] = time();
        return $this->add($data);
    }

}
