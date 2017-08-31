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

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/2
 * Time: 13:06
 */

namespace Admin\Model;
use Think\Model;

class ArticleCategoryModel extends Model
{
    protected $patchValidate = true;
    protected $_validate = [
        ['name','require','文章分类名称不能为空'],
        ['sort','number',"排序只能是数字"],
    ];
    
    /**
     * 获取 日志分类列表
     * @return array
     */
    public function getList(){
        $rows = $this->field('id,name')->where(['status'=>1])->select();
        return $rows;
    }
}