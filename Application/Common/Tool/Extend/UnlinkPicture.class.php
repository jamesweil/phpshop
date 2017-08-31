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

namespace Common\Tool\Extend;

use Common\Tool\Tool;
use Common\Tool\Intface\Picture;
use Common\Tool\Event;
/**
 * 纯粹图片删除
 * @copyright 版权所有©亿速网络 
 * @version 1.0.1
 * @author  王强
 */
class UnlinkPicture extends Tool implements Picture
{
    
    public function __construct($imageFilePath)
    {
        $this->imageFilePath = $imageFilePath;
    }
    
    /**
     * @return the $imageFilePath
     */
    public function getImageFilePath()
    {
        return $this->imageFilePath;
    }

    /**
     * @param Ambigous <string, unknown> $imageFilePath
     */
    public function setImageFilePath($imageFilePath)
    {
        $this->imageFilePath = $imageFilePath;
    }

    /**
     * {@inheritDoc}
     * @see \Common\Tool\Intface\Picture::delPicture()
     */
    public function delPicture($isPartten = false, $parttenCondition = 'imgSrc')
    {
        // TODO Auto-generated method stub
        $imageFile = $this->imageFilePath;
       
        if (empty($imageFile)) {
            return false;
        }
        
        if (is_string($imageFile)) {
            
            return is_file('./'. $this->imageFilePath)  ? unlink('./'. $this->imageFilePath) : false;
        }
        
        //数组
        if (!is_array($imageFile)) {
            return false;
        }
       
        Event::listen('thumbImage', $imageFile);
        
        if (empty($imageFile)) {
            return true;
        }
        $status = false;
        $tmp = null;
        foreach ($imageFile as $file) {
            $tmp = './'.$file;
            if (!is_file($tmp)) {
                $this->errorFile = $file;
                return false;
            }
            $status = unlink($tmp);
        }
        return $status;
    }
}