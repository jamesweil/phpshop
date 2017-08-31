<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <13052079525>
// +----------------------------------------------------------------------
// |简单与丰富！让外表简单一点，内涵就会更丰富一点。
// +----------------------------------------------------------------------
// |让需求简单一点，心灵就会更丰富一点。
// +----------------------------------------------------------------------
// |让言语简单一点，沟通就会更丰富一点。
// +----------------------------------------------------------------------
// |让私心简单一点，友情就会更丰富一点。
// +----------------------------------------------------------------------
// |让情绪简单一点，人生就会更丰富一点。
// +----------------------------------------------------------------------
// |让环境简单一点，空间就会更丰富一点。
// +----------------------------------------------------------------------
// |让爱情简单一点，幸福就会更丰富一点。
// +----------------------------------------------------------------------
namespace Home\Controller;

use Common\Tool\Tool;
use Home\Logical\Model\GoodsCartLogic;
use Common\Controller\ProductController;
use Common\TraitClass\IsLoginTrait;

class AjaxAddCartController
{
    use IsLoginTrait;
    private $controllerObj;
    
    private $args;
    
    public function __construct($args)
    {
        $this->args = $args;
        
        $this->controllerObj = new ProductController();
        
        $this->isLogin(true);
    }
    
    /**
     * 多商品购买
     */
    public function addCartByManyGoods()
    {
        $args = $this->args;
    
        Tool::checkPost($args, [], false, ['data']) ?: $this->ajaxReturnData(null, 0, '商品数据有误');
        Tool::connect('parseString');
    
        $cartModel = GoodsCartLogic::getInstance($args['data']);
    
        $status = $cartModel->getResult();
    
        $this->controllerObj->updateClient($status, '添加');
    
    }
}