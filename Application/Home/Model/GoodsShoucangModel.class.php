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

namespace Home\Model;
use Think\Model;
/**
 * 收藏模型 
 */
class GoodsShoucangModel extends Model
{
    /**
     * 添加收藏 
     */
    public function addCollection(array $array)
    {
        if (empty($array) || !is_array($array))
        {
            return false;
        }
        $result = $this->where('goods_id = "'.$_POST['goods_id'].'" and user_id = "'.$_SESSION['user_id'].'"')->getField('id');
        if(!empty($result)){
            return false;
        }
        $_POST['user_id'] = $_SESSION['user_id'];
        $_POST['create_time'] = time();

        $data = $this->create();
        
        $res = $this->add($data);
        return !empty($res) ? true : false;
    }
} 