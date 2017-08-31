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

namespace Common\TraitClass;

use Think\Image;

/**
 * 缩略图处理 插件
 * @version 1.0.1
 * @author 王强
 */
trait ThumbNailTrait
{
    /**
     * @desc 图片资源
     * @var array
     */
    protected $imageSource = [];
    
    protected static $thumbObj;
    
    /**
     * @param float $thumbWith
     * @param float $thumbHeight
     * @return array
     */
    public function buildThumbImage ($thumbWith, $thumbHeight)
    {
        if (empty($this->imageSource) || !is_numeric($thumbHeight) || !is_numeric($thumbHeight)) {
            return array();   
        }
        
        self::newIntanceImageObj();
        
        $obj = self::$thumbObj;
        
        $thumbArray = [];
        
        $width = $height = 0;
        
        $source = null;
        
        //图片名字
        $imageThumbName = $imageName = null;
        
        foreach ($this->imageSource as $value)
        {
           $value = substr($value, 1);
          
           $source = $obj->open($value);
           
           $width   = $source->width();
           
           $height = $source->height();
           
           $imageName = substr($value, strrpos($value, '/')+1);
           
           $imageThumbName = 'thumb_'.$imageName;
           
           $value = str_replace($imageName, $imageThumbName, $value);
           
           $source->thumb($thumbWith, $thumbHeight)->save($value);
           
           $thumbArray[] = '/'.$value;
        }
        return $thumbArray;
        
    }
    
    public static function newIntanceImageObj ()
    {
        self::$thumbObj = !(self::$thumbObj instanceof Image) ? new Image() : self::$thumbObj;
    }
}