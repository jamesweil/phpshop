<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>
// +----------------------------------------------------------------------
namespace Common\TypeParse\SonType;

use Common\TypeParse\AbstractParse;

class NULLType extends AbstractParse 
{
    /**
     * 处理多商品加入购物车
     * @param array $data
     */
    public function parseGoodsCart(array $data)
    {
      
        if (empty($data)) {
            return false;
        }
        
        $model = $this->getModel();
       
        return $model->addAll($data);
    }
    
}

