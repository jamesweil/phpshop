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
 * 积分使用表
 */
class IntegralUseModel extends Model
{

    /**
     * 获取有效积分,使用时间限制
     * @param  int     $user_id 用户id
     * @param  int     $time    限制时间,积分至少得到了多少天后才有效.0是兑换既就有效
     * @param  boolean $detail  显示可用积分列表
     * @return 数组
     */
    public function valid($user_id, $time = 0, $detail = false)
    {
        // 获取还在用户手中的积分,有可能是过期的,但是状态没有改变
        $field = 'id,user_id,integral,goods_id,trading_time,remarks,type,status,used';
        $where = ['type'=>1, 'status'=>1, 'user_id'=>$user_id];
        $list  = $this->field($field)->where($where)->order('id')->select();

        // 更新过期的积分,从现在算起,领取积分一年内有效
        $total    = 0;
        $validate = [];
        if (is_array($list) && count($list) > 0) {
            $ids     = [];
            $overdue = time() - (365 * 24 * 60 * 60);
            $delayed = strtotime('-'.$time.' day');
            foreach ($list as $val) {
                
                // 过期
                if ($val['trading_time'] < $overdue) {
                    $ids[] = $val['id'];
                    continue;
                }

                // 限制时间未到
                if ($val['trading_time'] > $delayed) {
                    continue;
                }

                // 有效的,减去用过的部分
                $total += $val['integral'];
                if ($val['used'] != 0) {
                    $total -= $val['used'];
                }

                // 保存详情
                if ($detail) {
                    $validate[] = $val;
                }
            }

            // 使用过期
            if (is_array($ids) && count($ids) > 0) {
                $ids = implode(',', $ids);
                $this->where(['id' => ['in', $ids]])->save(['status' => 3]);
            }
        }

        // 计算可用的积分券
        return $detail ? ['total'=>$total, 'list'=>$validate] : $total;
    }


    /**
     * 使用优惠券,添加标记
     * @param  integer $user_id  用户id
     * @param  integer $integral 使用的过得积分
     * @param  integer $time     使用时间限制
     * @param  array   $data     数据库附带参数:goods_id,remarks
     * @return boolean
     */
    public function used($user_id, $integral, $time = 0, $data = [])
    {
        $ids   = '';
        $total = 0;
        $used  = [];
        $list  = $this->valid($user_id, $time, true);
        if ($integral > $list['total']) {
            return false;
        }
        if (is_array($list['list'])) {
            foreach ($list['list'] as $vo) {

                // 能用的,包括使用了部分的
                $temp = $total + $vo['integral'];
                if ($vo['used'] != 0) {
                    $temp -= $vo['used'];
                }

                // 刚好够
                if ($temp == $integral) {
                    $ids .= ','.$vo['id'];
                    $total += $vo['integral'];
                    break;
                }

                // 需要用到部分
                if ($temp > $integral) {
                    $used['id']   = $vo['id'];
                    $used['used'] = $integral - $total + $vo['used'];
                    break;
                }

                // 不够继续循环
                $ids   .= ','.$vo['id'];
                $total += $vo['integral'];
            }
        }

        // 添加记录
        if (!empty($ids)) {
            $this->where(['id'=>['in', ltrim($ids, ',')]])->save(['status'=>2, 'used'=>0]);
        }

        // 修改用掉一些的表
        if (count($used) > 0) {
            $this->where(['id'=>$used['id']])->save(['used'=>$used['used']]);
        }

        // 添加一条使用记录
        $info = [
            'user_id'      => $user_id,
            'integral'     => $integral,
            'goods_id'     => $data['goods_id'],
            'trading_time' => time(),
            'remarks'      => $data['remarks'],
            'type'         => 2
        ];
        return $this->add($info);
    }
}