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

namespace Common\TypeParse\SonType;

use Common\TypeParse\AbstractParse;
use Common\TypeParse\ActionRunInterface;

/**
 * 整形类型分析
 * @author 王强
 * @version 1.0.1
 */
class IntegerType extends AbstractParse implements ActionRunInterface
{
    /**
     * {@inheritDoc}
     * @see \Common\TypeParse\ActionRunInterface::actionRun()
     */
    public function actionRun()
    {
        // TODO Auto-generated method stub
        
    }

    /**
     * {@inheritDoc}
     * @see \Common\TypeParse\ActionRunInterface::parseDataBaseByUser()
     */
    public function parseDataBaseByUser($model = null)
    {
        // TODO Auto-generated method stub
        
        return true;
    }

    
}