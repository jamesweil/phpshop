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

namespace Home\Controller;

use Home\Model\HotWordsModel;
use Home\Model\GoodsModel;
use Common\Tool\Tool;

class SearchController extends BaseController
{
    public function index()
    {
        
        
        Tool::checkPost($_GET, array('is_numeric' => array('id')), true , array('id')) ? true : $this->error('灌水机制已打开');
        
        //连接驱动
        Tool::connect('Mosaic');
        //获取关键词
        $data = HotWordsModel::getInitnation()->parseData(array(
            'where' => array('is_hide' => '0'),
            'field' => array('id', 'hot_words', 'goods_class_id')
        ),new \Think\Model('goods_class'));
        
        //Uploads/goods/2016-08-26/58097a19bb50a.jpg
        //传递给商品模型
        $receiveGoods = GoodsModel::getInitation()->getGoodsData($data);
        $this->result = $receiveGoods;
        $this->intnetTitle = $this->intnetTitle.' - '.C('internetTitle.search');
        $this->display('Index/search');
    }
}