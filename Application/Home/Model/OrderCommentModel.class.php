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
 * 订单模型 
 */
class OrderCommentModel extends Model
{

    /**
     * 添加评价
     * @param  array  $data 数据项
     * @return integer
     */
    public function submit(array $data)
    {
        if (empty($data)) {
            return false;
        }

        // 主观评价好评,中评,差评
        switch (intval($data['score'])) {
            case 1:
            case 2:
                $data['level'] = 1;
                break;
            case 3:
            case 4:
                $data['level'] = 2;
                break;
            case 5:
                $data['level'] = 3;
                break;
            default:
                $data['score'] = 5;
                $data['level'] = 3;
                break;
        }
        $data['create_time'] = time();
        //return $this->add($data);
        //添加评论成功后，同时在商品表中的评论字段加1
        $goods_id = $data['goods_id'];
        $result = $this->add($data);
        if($result){
            $goodsModel = M("Goods");
            $comment_member = $goodsModel->where(['id'=>$goods_id])->getField("comment_member");
            if(!$comment_member){
                $comment_member = 1;
                $goodsModel->where(['id'=>$goods_id])->setField('comment_member',$comment_member);
                return true;
            }else{
                $comment_member++;
                $goodsModel->where(['id'=>$goods_id])->setField('comment_member',$comment_member);
                return true;
            }
        }
    }


    /**
     * 根据商品ID获取评论
     * @param  array|string $ids  商品is
     * @param  integer $type 0,全部评价 1,晒图 2,好评 3,中评 4,差评 5,只看当前评论
     * @param  boolean $autoPage 自动分页参数,默认分页,使用该项参数,$limit 参数无效
     * @param  string  $limit 需要$autoPage=false 方才有效
     * @return array
     */
    public function commentByGoodsId($ids, $type, $autoPage=true, $limit='0,10')
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        $where['goods_id'] = ['in', $ids];
        $where['status'] = 1;
        switch ($type) {
            case 1:
                break;
            case 2: // 好评
                $where['level'] = 3;
                break;
            case 3: // 中评
                $where['level'] = 2;
                break;
            case 4: // 差评
                $where['level'] = 1;
                break;
            case 5: 
                break;
            default:
                # code...
                break;
        }

        if ($autoPage === true) {
            $count = $this->field('id')->where($where)->count();
            $page  = new \Think\Page($count, PAGE_SIZE);
            $limit = $page->firstRow.','.$page->listRows;
        }

        $user_model  = M('user');
        $goods_model = D('goods');
        $field       = 'id,goods_id,order_id,user_id,anonymous,score,level,content,labels,show_pic,create_time';
        $data        = $this->field($field)->where($where)->limit($limit)->order('create_time DESC')->select();
        if (is_array($data)) {
            foreach ($data as &$comment) {
                // 获取图片
                if ($comment['show_pic']) {
                    $images = $this->commentPic($comment['show_pic']);
                } else {
                    $images = array();
                }
                $comment['images'] = $images;

                // 获取印象
                if ($comment['labels']) {
                    $feel   = $this->feel($comment['labels']);
                } else {
                    $feel   = array();
                }
                $comment['feel'] = $feel;

                // 获取用户信息
                $user = $user_model->field('user_name,nick_name')->find($comment['user_id']);
                if ($user) {
                    $comment['user_name'] = $user['user_name'];
                    $comment['nick_name'] = $user['nick_name'];
                    $show_name            = empty($user['nick_name'])?$user['user_name']:$user['nick_name'];
                    if ($comment['anonymous']) {
                        $show_name = $this->format_name($show_name);
                    }
                    $comment['show_name'] = $show_name;
                }

                // 获取商品规格
                $comment['spec'] = $goods_model->spec($comment['goods_id']);

            }
        }
        if ($autoPage === true) {
            $result['list'] = $data;
            $result['page'] = $page->show();
        }
        return $autoPage ? $result : $data;
    }


    /**
     * 输出格式化
     */
    public function format_name($str='')
    {
        if (empty($str)) {
            return '';
        }
        $len = mb_strlen($str,'utf-8');
        if($len>=6){
            $str1 = mb_substr($str,0,2,'utf-8');
            $str2 = mb_substr($str,$len-2,2,'utf-8');
        } else {
            $str1 = mb_substr($str,0,1,'utf-8');
            $str2 = mb_substr($str,$len-1,1,'utf-8');            
        }
        return $str1.'***'.$str2;
    }


    /**
     * 计算某个商品的 好评度
     * @param  string $ids     商品ids
     * @param  boolean $format 格式化百分比
     * @return array
     */
    public function commentPraise($ids, $format=true)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        // 评论等级
        $sql  = 'select level,count(1) as num from __ORDER_COMMENT__ where goods_id in ('.$ids.') AND status=1 group by level';
        $data = M()->query($sql);
        if (is_array($data)) {
            $info  = [];
            $total = 0;
            foreach ($data as $vo) {
                $total += $vo['num'];
                $info['level_'.$vo['level']]['number'] = $vo['num'];
            }
            foreach ($data as $vo) {
                $percent = sprintf('%.2f', $vo['num']/$total);
                $percent = (100 * $percent);
                $info['level_'.$vo['level']]['percent'] = $percent;
            }
        }
        $info['total'] = $total;
        
        // 统计图片数量
        $sql = "select sum((select count(1) from __IMAGES__ as i where FIND_IN_SET(i.id, c.show_pic))) as num"
            ." from db_order_comment as c where c.goods_id in ($ids)";
        $ret = M()->query($sql);
        $info['total_pic'] = $ret[0]['num'];

        return $info;
    }


    /**
     * 对某个商品的印象
     * @param  array|string $ids id数组
     * @param  boolean $detail 显示详细
     * @return array
     */
    public function feel($ids, $detail=false)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        if ($detail) {
            $field = 'id,class_id,title,create_time,update_time';
        } else {
            $field = 'title';
        }
        $data = M('classFeel')->field($field)->where(['id'=>['in', $ids]])->select();
        return is_array($data) ? $data : [];
    }


    /**
     * 统计,卖家印象
     * @param  string $ids 商品ids,必须是一个父类商品下的子商品才有意义
     * @return array
     */
    public function statistical($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        $sql  = 'select f.id,f.title, count(1) as num from __CLASS_FEEL__ as f,__ORDER_COMMENT__ as c '
            . 'where  FIND_IN_SET(f.id, (c.labels)) AND c.goods_id in ('.$ids.') and c.status=1 group by f.id';
        $data = M()->query($sql);
        return is_array($data) ? $data : [];
    }


    /**
     * 获取品论图片
     * @param  integer $cid 评论ID
     * @return array
     */
    public function commentPic($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        $data = M('images')->field('path')->where(['id'=>['in', $ids]])->select();
        return is_array($data) ? $data : [];
    }

    /**
     * 获取商品图片
     * @param  string|array $ids 商品id
     * @return array
     */
    public function picByGoods($ids, $limit='0,10')
    {
        if (is_integer($ids)) {
            $ids = $ids + '';
        } else if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        // 评论列表
        $comment = $this->field('goods_id,show_pic')->where(['goods_id'=>['in', $ids]])->limit($limit)->select();
        $data    = [];
        if (is_array($comment)) {

            $images_model = M('images');
            foreach ($comment as &$value) {
                if (empty($value['show_pic'])) {
                    continue;
                }
                $list = $images_model->field('path')->where(['id'=>['in', $value['show_pic']]])->select();
                foreach ($list as $vo) {
                    $data[] = $vo['path'];
                }
            }
        }

        return $data;
    }


    /**
     * 统计商品有多少条评论
     * @param  integer $ids 商品ids(需要一个主商品下的商品id)
     * @return integer
     */
    public function sum($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        $sum = 0;
        $ret = $this->field('count(1) as sum')->where(['goods_id'=>['in', $ids]])->select();

        if (is_numeric($ret[0]['sum'])) {
            $sum = $ret[0]['sum'];
        }
        return $sum;
    }


    /**
     * 处理图片
     */
    public function uploadImage($config)
    {
        $upload = new \Think\Upload($config);
        $info   = $upload->upload();

        if (is_array($info)) {
            $data  = [];
            $model = M('images');
            $path  = '/'.trim(UPLOAD_PATH, '/');
            $time  = time();
            $ids   = [];
            foreach ($info as $vo) {
                $data = [
                    'path'        => $path.$vo['savepath'].$vo['savename'],
                    'create_time' => $time
                ];
                $ids[] = $model->add($data);
            }
        }  
        return $ids;     
    }

    //查询待晒单商品列表
    public function getWaitingListByComment($user_id){
        $user_id = $_SESSION['user_id'];
        if (empty($user_id)) {
            return false;
        }
        $where['user_id'] = $user_id;
        $where['show_pic'] = '';      
        $filed = 'id,goods_id,order_id,anonymous,score,content,labels,show_pic';
        $res = M('OrderComment')->field($filed)->where($where)->select();
        $count = M('OrderComment')->where($where)->count();
        return array('res'=>$res,'count'=>$count);
    }
    //查询已评价商品列表
        public function getAlreadyCommentByComment($user_id){
        $user_id = $_SESSION['user_id']; 
        if (empty($user_id)) {
            return false;
        }
        $where['user_id'] = $user_id;
        $where['show_pic'] = array('neq','');
        $filed = 'id,goods_id,order_id,anonymous,score,content,labels,show_pic,create_time';
        $res = M('OrderComment')->field($filed)->where($where)->order('create_time DESC')->select();       
        return $res;
    }
    //查询商品评论标签
    public function getCommentFeelByComment(array $data){
        if (empty($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            $labels = $value['labels'];
            $where['id'] = array('IN',$labels);
            $feel = M('class_feel')->field('title')->where($where)->select();
            $data[$key]['feel'] = $feel;
        }
        return $data;
    }
    //查询单条评论
    public function getCommentByCommentId($comment_id){
        if (empty($comment_id)) {
            return false;
        }
        $where['id'] = $comment_id;
        $filed = 'id,goods_id,order_id,anonymous,score,content,labels,show_pic,create_time';
        $res = M('OrderComment')->field($filed)->where($where)->find();
        return $res;
    }
    //查询单条商品评论标签
    public function getCommentFeelByCommentId(array $data){
        if (empty($data)) {
            return false;
        }
        $labels = $data['labels'];
        $where['id'] = array('IN',$labels);
        $feel = M('class_feel')->field('title')->where($where)->select();
        $data['feel'] = $feel;       
        return $data;
    }
}