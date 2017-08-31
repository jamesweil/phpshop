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
/**
 * 序列化商品删除
 * @author 王强
 * @version 1.0.1
 */
class SerializePicture extends Tool implements Picture
{
    
    public function __construct($imageFilePath)
    {
        $this->imageFilePath = $imageFilePath;
    }
    
    /**
     * {@inheritDoc}
     * @see \Common\Tool\Intface\Picture::delPicture()
     */
    public function delPicture($isPartten = false, $parttenCondition = 'imgSrc')
    {
        $imageFile = $this->imageFilePath;
        if (empty($imageFile)) {
            return false;
        }
        
        if (is_string($imageFile) || !$this->isSerialized($imageFile)) {
            
            // TODO Auto-generated method stub
            $data = unserialize($imageFile);
            
            return $this->deleFile($data);
            
        }
        
        if (!is_array($imageFile)) {
            return false;
        }
        
        $status = false;
        
        foreach ($imageFile as & $value) {
           $value = unserialize($value);
           
           $status = $this->deleFile($value);
        }
        
        return $status;
    }
    
    protected  function deleFile (array $data)
    {
        if (empty($data)) {
            return false;
        }
        
        $flag = 0;
        foreach ($data as $key => &$value)
        {
        
            if (!is_file($value)) {
        
                $this->errorFile = $value;
        
                return false;
            }
        
            $flag = unlink($value);
        
        }
        return $flag;
    }
    
}