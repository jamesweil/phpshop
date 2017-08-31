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
use Common\Tool\QRcode;
use Common\Tool\Tool;
use Common\Tool\Extend\UnlinkPicture;

/**
 * 系统配置模型
 * @author Administrator
 * @version 1.0.1
 */
class SystemConfigModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//id

	public static $configValue_d;	//配置值

	public static $classId_d;	//所属分类

	public static $parentKey_d;	//父级key

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

	private $initURL = null;
  
	private $logoPath  = null;

   

    public static function getInitnation()
    {
        $class = __CLASS__;
    
        return static::$obj = !(static::$obj instanceof $class) ? new static() : static::$obj;
    }
    
    /**
     * 保存配置
     * @param array $data
     * @return boolean
     */
    public function saveData(array $data)
    {
        if (empty($data[static::$classId_d]))
        {
            return false;
        }
      
        if (!empty($data['logo_name']) && $this->logoPath !== $data['logo_name']) {
            Tool::partten($this->logoPath, UnlinkPicture::class);
        }
        
        //生成二维码图片
        $data = $this->buildQrCode($data);

        $classId = $data[static::$classId_d];
        
        $parentKey =  $data[static::$parentKey_d];
        
        unset($data[static::$classId_d], $data[static::$parentKey_d]);
        
        $sesData = serialize($data);
        
        $isHave = $this->where(static::$classId_d.'= "%s"', $classId)->find();
       
        if (empty($isHave))
        {
            $isSuccess = $this->add(array(
                static::$classId_d     => $classId,
                static::$configValue_d => $sesData,
                static::$parentKey_d   => $parentKey
            ));
        } else {
           $isSuccess =  $this->where(static::$classId_d.'= %d', (int)$classId)->save(array(
                static::$configValue_d => $sesData,
            ));
        }
        return $isSuccess;
    }
    
    /**
     * 生成二维码图片
     */
    protected function buildQrCode(array $post)
    {
        
        if (empty($post['internet_url'])) {
            return $post;
        }
    
        if ( $post['internet_url'] === $this->initURL) {  
            return $post;
        }
        
        $url = false !== strpos($post['internet_url'], 'http://') ? $post['internet_url'] : 'http://'.$post['internet_url'];
        include_once  COMMON_PATH.'Tool/QRcode.class.php';
        $path = C('qr_image').time().rand(0, 100000).'.png';
        
        \QRcode::png($url, $path, QR_ECLEVEL_H, 4);
       
        Tool::partten($post['init_qr_code'], UnlinkPicture::class);
        
        $this->addWater($path);
        $post['init_qr_code'] = substr($path, 1);
        return $post;
    }
    /**
     * 添加水印
     * @param unknown $path
     */
    private function addWater ($path)
    {
        $QR = imagecreatefromstring(file_get_contents($path));
        $logo = imagecreatefromstring(file_get_contents(C('water')));
        $QR_width = imagesx($QR);//二维码图片宽度
        $QR_height = imagesy($QR);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width/$logo_qr_width;
        $logo_qr_height = $logo_height/$scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
            $logo_qr_height, $logo_width, $logo_height);
        
        //输出图片
        imagepng($QR, $path);
    }
    
    /**
     * 更新前数据
     * {@inheritDoc}
     * @see \Think\Model::_before_update()
     */
    protected function _before_update(&$data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$parentKey_d, $data[static::$parentKey_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$parentKey_d].'】';
            return false;
        }
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    protected function _before_insert(&$data, $options)
    {
        $data[static::$updateTime_d] = time();
        $data[static::$createTime_d] = time();
        return $data;
    }
    
    /**
     * 获取配置值
     * @param array $options 条件
     * @return array
     */
    public function getValue(array $options = null)
    {
        $data = $this->field(static::$createTime_d.','.static::$updateTime_d, true)->where($options)->select();
        
        if (empty($data))
        {
            return array();
        }
        
        foreach ($data as $key => &$value)
        {
            if (!empty($value[static::$configValue_d]))
            {
                $unData = unserialize($value[static::$configValue_d]);
                unset($data[$key][static::$configValue_d]);
                $value = array_merge($value, $unData);
            }
        }
        return $data;
    }
    

    /**
     * @param 设置网站url $initURL
     */
    public function setInitURL($initURL)
    {
        $this->initURL = $initURL;
    }
    
    /**
     * @param field_type $qrPath
     */
    public function setLogoPath($qrPath)
    {
        $this->logoPath = $qrPath;
    }
}