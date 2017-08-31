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

use Common\Model\BaseModel;
use Common\Model\IsExitsModel;

/**
 * 自定义页面模型 
 * @author 王强
 */
class CustomPageModel extends BaseModel implements IsExitsModel
{
    private static $obj;

	public static $id_d;	//

	public static $groupId_d;	//自定义页面分类【编号】

	public static $name_d;	//页面名称

	public static $path_d;	//静态页面路径

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 时间输入 【插入前】
     */
    protected function _before_insert(& $data, $options)
    {
        if (empty($data[static::$path_d])) {
            return false;
        }
        
        $data[static::$createTime_d] = time();
        
        $data[static::$updateTime_d] = time();
        
        $data[static::$path_d] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$data[static::$path_d];
        
        return $data;
    }
    
    /**
     * 更新前 时间更新
     */
    protected function _before_update(& $data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$name_d, $data[static::$name_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$name_d].'】';
            return false;
        }
        $data[static::$updateTime_d] = time();
        
        return $data;
    }
    
    /**
     * 添加页面处理 
     */
    public function parseInsrtHtml($option)
    {
        //获取显示字段的注释
        $columNotes = $this->getComment([
            static::$id_d,
            static::$updateTime_d,
            static::$createTime_d,
            static::$path_d
        ]);
        
        $this->setColum($columNotes);
        
        $type = [
            static::$name_d => [
                'tag'         => 'input',
                'type'        =>  'text',
                'value'       =>  '',
                'closeTag'    => ''
            ],
            static::$groupId_d => [
                'tag'         => 'select',
                'type'        =>  '',
                'value'       =>  '',
                'option'      => $option,
                'closeTag'    => '</select>'
            ]
        ];
        return  $this->buildHTML($type);
    }
    
    /**
     * 输出静态页面
     */
    public function buildStaticHtml (array & $post, $path)
    {
        if (!$this->isEmpty($post) || empty($path) || empty($post['detail'])) {
            $this->error = '参数错误';
            return false;
        }
    
        if ($this->IsExits($post)) {
            //已存在
            $this->error = '已存在该文件名';
            return false;
        }
        
        $pathNews = $path.'/'.$post['group_name'];
        
        if (!is_dir($pathNews)) {
            $isMake = mkdir($path.'/'.$post['group_name'], 0777, true);
        }
       
        $fileName = $pathNews.'/'.$post[static::$name_d].'.html';
        $post['detail'] = mb_convert_encoding($post['detail'], "UTF-8");
        $status = file_put_contents($fileName, $post['detail']);
        if (!$status) {
            $this->error = '输出文件失败';
        }
        $post[static::$path_d] = $fileName;
        return $status;
    }
    /**
     * {@inheritDoc}
     * @see \Common\Model\IsExitsModel::IsExits()
     */
    public function IsExits($post)
    {
        // TODO Auto-generated method stub
        
        if (empty($post[static::$name_d])) {
            return true;
        }
        return empty($this->getDataByName($post[static::$name_d])) ? false : true;
    }
    
    public function getDataByName ($name)
    {
        if (empty($name)) {
            return array();
        }
        return $this->where(static::$name_d.' = "%s"', $name)->select();
    }

}