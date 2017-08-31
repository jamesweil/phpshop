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

namespace Common\Controller;

use Think\Controller;
use Common\Tool\Tool;
use Common\Model\ConfigChildrenModel;
use Common\Model\SystemConfigModel;

trait CommonController
{
    /**
     * 获取系统配置
     */
    public function getConfig($key = null)
    {
        $configData = S('configData');
        if (!$configData)
        {
            //获取字表数据
            $children    = ConfigChildrenModel::getInitnation()->getAllConfig();
            //获取配置值
            $configValue = SystemConfigModel::getInitnation()->getAllConfig();
            //组合数据
            Tool::connect('ArrayParse', array('children' => $children, 'configValue'=> $configValue));
            
            $configData = array();
            
            $data = Tool::buildConfig()->parseConfig()->oneArray($configData);
            S('configData', $configData, 100);
        }
        
        return $key === null  || !isset($configData[$key]) ? $configData : $configData[$key];
    }
    
}