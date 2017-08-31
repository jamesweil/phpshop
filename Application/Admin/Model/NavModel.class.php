<?php

// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>\n
// +----------------------------------------------------------------------


namespace Admin\Model;


use Think\Model;
/**
 * 导航模型
 * @author Administrator
 * @version 0.9.9
 */
class NavModel extends Model
{
    protected $patchValidate = true;
    protected $_validate = [
        ['nav_titile','require','导航菜单名称不能为空'],
        ['sort','number','排序只能是数字'],
    ];
    
    /**
     * 添加前操作
     */
    protected function _before_insert(&$data,$options)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        return $data;
    }
    //更新前操作
    protected function _before_update(&$data, $options)
    {
        
        $data['update_time'] = time();
        return $data;
    }

}