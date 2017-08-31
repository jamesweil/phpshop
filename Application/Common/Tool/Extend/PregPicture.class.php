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
 * 正则匹配 删除图片
 * @copyright 版权所有©亿速网络 
 */
class PregPicture extends Tool implements Picture
{
    
    public function __construct($imageFilePath)
    {
        $this->imageFilePath = $imageFilePath;
    }
    
    /**
     * {@inheritDoc}
     * @see \Common\Tool\Intface\Picture::delPicture()
     */
    public function delPicture( $isPartten = false, $parttenCondition = 'imgSrc')
    {
        // TODO Auto-generated method stub
        $imageFile = $this->imageFilePath;
      
        if (!array_key_exists($parttenCondition, self::$partten) || empty($imageFile))
        {  showData($imageFile);
            return false;
        }
        
        if (is_string($imageFile)) {
            
            return $this->deleImageFile($parttenCondition);
        }
        
        if (!is_array($imageFile)) {
            return false;
        }
        
        $status = false;
        
        foreach ($imageFile as $file) {
            
            if (!($status = $this->deleImageFile($parttenCondition))) {
                $this->errorFile = $file;
                return false;
            }
        }
        
        return $status;
        
    }
    
    protected function deleImageFile ($parttenCondition = 'imgSrc')
    {
        $isSuccess = preg_match_all(parent::$partten[$parttenCondition], $this->imageFilePath, $parseData);
        
        if ($isSuccess && !empty($parseData[1]))
        {
            $flg = 0;
            foreach ($parseData[1] as $key => &$file)
            {
                //本地文件的删除
                is_file('./'.$file) ? unlink('./'.$file) : $flg++;
            }
            return $flg === 0 ? true : false;
        }
        else
        {
            return false;
        }
        
    }
}