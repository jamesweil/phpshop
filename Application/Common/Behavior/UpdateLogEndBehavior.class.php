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

namespace Common\Behavior;

use Think\Behavior;
use Common\Model\BaseModel;
use Admin\Model\LogContentModel;

/**
 * 更新结束行为
 */
class UpdateLogEndBehavior extends Behavior
{
    /**
     * {@inheritDoc}
     * @see \Think\Behavior::run()
     */
    public function run(&$params)
    {
        // TODO Auto-generated method stub
        $logContentModel = BaseModel::getInstance(LogContentModel::class);
        
        $status = $logContentModel->addLogContentAnyOne($params);
        
        return $status;
    }

    
}

