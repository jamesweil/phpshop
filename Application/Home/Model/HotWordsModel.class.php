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
 * 关键词模型 
 */
class HotWordsModel extends Model
{
    private static $obj;
    
    /**
     * 查询 单个 关键词数据
     */
    public function getHotWord($options = array(), Model $model)
    {
        if (empty($options) || !is_array($options) || !($model instanceof Model))
        {
            return array();
        }
        
        $data = parent::find($options);
        
        if (!empty($data))
        {
            $data['children'] = $model->field( $model->getPk() )->where('fid = "'.$data['goods_class_id'].'"')->select();
        }
        
        return $data;
       
    }
    
    /**
     * 处理多级数 
     */
    public function parseData($options=array(), Model $model)
    {
        $data = $this->getHotWord($options, $model);
        
        $data = \Common\Tool\Tool::join($data);
       
        return $data;
    }
    
    public static function getInitnation()
    {
       return  self::$obj = !(self::$obj instanceof HotWordsModel) ? new self() : self::$obj;
    }
    
    /**
     * 获取关键词 
     */
    public function getKeyWord ()
    {
        if (! $hotWords = S('hot_words')) {
            $hotWords = $this->field("id,hot_words")
            ->where([
                'is_hide' => '0'
            ])
            ->order('create_time desc')
            ->limit(10)
            ->select();
            S('hot_words', $hotWords, 60);
        }
        
        return $hotWords;
    }
}