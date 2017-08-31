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
 * 广告模型
 */
class AdModel extends Model
{

    private static $obj;

    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = ! (self::$obj instanceof $name) ? new self() : self::$obj;
    }

    /**
     * 获取 首页右边新品广告图
     */
    public function rightAdByIndex()
    {
        // 右边新品广告图
        $adByRight = S('AD_BY_RIGHT');
        
        if (empty($adByRight)) {
            $newThree = [];
            $newThree['enabled'] = 1;
            $newThree['ad_space_id'] = 35;
            $nowtime = time();
            $newThree['start_time'] = array(
                'elt',
                $nowtime
            );
            $newThree['end_time'] = array(
                'egt',
                $nowtime
            );
            $adByRight = $this->where($newThree)
                ->order('sort_num')
                ->limit(3)
                ->select();
        } else {
            return $adByRight;
        }
        
        if (empty($adByRight)) {
            return [];
        }
        
        S('AD_BY_RIGHT', $adByRight, 15);
        
        return $adByRight;
    }

    /**
     * 每日推荐 的12个图
     */
    public function recommendByEveryDay()
    {
        $adByRecommend = S('AD_BY_RECOMMEND');
        
        if (empty($adByRight)) {
            
            // 每日推荐十二个广告图
            $newThree['enabled'] = 1;
            $newThree['ad_space_id'] = 36;
            $nowtime = time();
            $newThree['start_time'] = array(
                'elt',
                $nowtime
            );
            $newThree['end_time'] = array(
                'egt',
                $nowtime
            );
            $adByRecommend = M('ad')->where($newThree)
                ->order('sort_num')
                ->limit(12)
                ->select();
        } else {
            return $adByRecommend;
        }
        
        if (empty($adByRecommend)) {
            return [];
        }
        
        S('AD_BY_RECOMMEND', $adByRecommend, 17);
        
        return $adByRecommend;
    }

    
    /**
     * 首页楼层 商品中间大图
     */
    public function getIndexMiddlePicture($limit = 1)
    {
        $adSpecId = C('ad_space_id');
    
        $query = 'select pic_url, ad_link from '.$this->trueTableName.' where ad_space_id = '.$adSpecId.' and enabled = 1 order by sort_num DESC limit '.$limit.', 1';
    
        $data = $this->query($query);
    
        return empty($data) ? [] : $data[0];
    }
    
    /**
     * 获取banner图
     * 
     * @param
     *            $str导航的名字
     * @return mixed
     */
    public function getNavBanner($str)
    {
        // 查出广告位置管理
        $ad_space_id = M("AdSpace")->where([
            'name' => $str
        ])->getField('id');
        $ad_details = M("Ad")->field("ad_link,pic_url,id")
            ->where([
            'ad_space_id' => $ad_space_id
        ])
            ->limit(4)
            ->order("id")
            ->select();
        return $ad_details;
    }
} 