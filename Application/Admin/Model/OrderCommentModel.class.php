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

use Think\Model;

/**
 * 订单评论
 */
class OrderCommentModel extends Model
{

    /**
     * 搜索订单评论
     * @param  array $where 搜索条件,不传入参数按时间降序排列
     * @return array
     */
    public function search(array $where, $limit='0,10')
    {
        $field  = 'id,goods_id,order_id,user_id,anonymous,score,level,content,labels,show_pic,create_time,status';
        $result = $this->field($field)->where($where)->order('create_time DESC')
            ->limit($limit)->select();
        if (is_array($result)) {
            foreach ($result as &$val) {
                $text = $val['content'];
                if (mb_strlen($text, 'UTF-8') > 25) {
                    $text  = mb_substr($text, 0, 25, 'UTF-8');
                    $text .= '...';
                } 
                $val['content'] = $text;
            }
        }
        return is_array($result) ? $result : [];
    }
    
    /**
     * 未审核评论数
     */
    public function getNoAudit ()
    {
        return $this->where('status = 0')->count();
    }

}