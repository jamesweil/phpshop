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
namespace Home\Logical\ActivityDetail;



use Home\Logical\ActivityIntface\ActivityInterface;

class NoActivity implements ActivityInterface
{
    //商品数据
    private $goods = [];
    
    const  GOODS_HTML = 'normalShipping'; 
    
    public function __construct(array $goods) {
        $this->goods = $goods;
    }
    
    /**
     * {@inheritDoc}
     * @see \Home\Logical\ActivityIntface\NoActivityInterface::getResult()
     */
    public function getResult()
    {
        // TODO Auto-generated method stub
    }
    /**
     * {@inheritDoc}
     * @see \Home\Logical\ActivityIntface\ActivityInterface::getResultByManyArrays()
     */
    public function getResultByManyArrays()
    {
        // TODO Auto-generated method stub
        
        $goods = $this->goods;
        
        if (empty($goods)) {
            return array();
        }
        
        //没有活动 修改状态以免引起冲突
        foreach ($goods as $key => &$value) {
            $value['poopStatus'] = 1000000;
        }
        return $goods;
    }
    /**
     * {@inheritDoc}
     * @see \Home\Logical\ActivityIntface\ActivityInterface::getHtmlName()
     */
    public function getHtmlName()
    {
        // TODO Auto-generated method stub
        return self::GOODS_HTML;
    }
}