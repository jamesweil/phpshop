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

namespace Common\Model;

use Think\Upload;
use Common\Tool\Tool;

/**
 * 上传模型 
 */
class FileUploadModel 
{
    private static  $obj;
    
    private $imageRouse = array();
    
    private $error = null;
    
    private $widthAndHeightConfig = array();
    
    /**
     * @return the $widthAndHeightConfig
     */
    public function getWidthAndHeightConfig()
    {
        return $this->widthAndHeightConfig;
    }

    /**
     * @param multitype: $widthAndHeightConfig
     */
    public function setWidthAndHeightConfig($widthAndHeightConfig)
    {
        $this->widthAndHeightConfig = $widthAndHeightConfig;
    }

    /**
     * @return the $error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param field_type $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    
    public function UploadFile($config, $file = '', $driver = 'Local', $driverConfig = null)
    {
        $this->imageRouse = empty($file) ? $_FILES : $file;
       
        $isPass = $this->checkImageWidthAndHeight($this->widthAndHeightConfig);

        if ($isPass === false)
        {
            return array();
        }
        
        $upload = new Upload($config, $driver, $driverConfig);
       
        $file = $upload->upload($file);
    
     
        
        if (empty($file))
        {
            return array();
        }

        $file = Tool::array_depth($file) ===2 ? Tool::parseToArray($file) : $file;
        
        $filePath = str_replace('.', null,C('GOODS_UPLOAD.rootPath')).$file['savepath'].$file['savename'];
        return $filePath;
    }
    
    /**
     * 检测图片宽高 
     * @param array $config 图片宽高数组
     * @return bool
     */
    public function checkImageWidthAndHeight (array $config)
    {
        if (empty($config)) {
            return false; //不予通过检测
        }
        $imageInfor = getimagesize($this->imageRouse['Filedata']['tmp_name']);
        
        if (empty($imageInfor)) {
            return false;
        }
        
        $width = $imageInfor[0];

        $widthMinConfig = $config['min_width'];
        
        $widthMaxConfig = $config['max_width'];
        
        //最小宽度 > 实际宽度 || 最大配置宽度 < 实际宽度 
        if ($widthMinConfig > $width || $widthMaxConfig < $width) {
            $this->error = '图片宽度不符【'.$imageInfor[0].'】';
            return false;
        }
        
        $height = $imageInfor[1];
        
        $heightMinConfig = $config['min_height'];
        
        $heightMaxConfig = $config['max_height'];
        
        if ($heightMinConfig > $height || $heightMaxConfig < $height) {
            $this->error = '图片高度不符【'.$imageInfor[1].'】';
            return false;
        }
        return true;
    }
}