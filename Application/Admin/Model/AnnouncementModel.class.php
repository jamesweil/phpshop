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
namespace Admin\Model;


use Think\Model;

/**
 * 后台 公告模型
 * @version 1.0.1
 * @copyright  Copyright © 2003-2023 亿速网络
 * @link www.yisu.cn
 */
class AnnouncementModel extends Model
{
    //主键
    public static $id_d;

    //公告标题
    public static $title_d;

    //公告作者
    public static $adminAccount_d;

    //公告简介
    public static $intro_d;

    //公告内容
    public static $content_d;

    //创建时间
    public static $createTime_d;

    //修改时间
    public static $UpdateTime_d;

    //公告类型
    public static $type_d;

    //公告状态
    public static $status_d;

    //排序
    public static $sort_d;


    protected $patchValidate = true;
    protected $_validate = [
        ['title','require','公告标题不能为空'],
        ['sort','number',"排序只能是数字"],
        ['content','require',"公告内容不能为空"],
    ];

    /**
     * 添加前操作
     */
    protected function _before_insert(&$data,$options)
    {
        $data['admin_account'] = session('account');
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