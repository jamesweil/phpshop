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
use Common\Tool\Tool;
use Common\Tool\Extend\UnlinkPicture;
use Common\Tool\Event;
use Common\TraitClass\ThumbNailTrait;
use Common\TypeParse\AbstractParse;
use Common\TraitClass\MethodTrait;

class GoodsImagesModel extends BaseModel
{
    use ThumbNailTrait;
    use MethodTrait;
    //主键
    public static $id_d;
    
    //商品编号
    public static $goodsId_d;
    
    //商品图片
    public static $picUrl_d;
    
    //商品状态
    public static $status_d;
    
    //缩略图前缀
    
    private  $thumbPerfix = 'thumb_';
    
    const OriginalImageNoThumb = 0; // 数据表原来的图片【不是缩略图】
    
    const OriginalImageThumb   = 1; // 数据表原来的图片【缩略图】
    
    private static  $obj;
    
    private $imageWidth = 400;
  
    private $imageHeight = 400;

	public static $isThumb_d;	//缩略图【1是 0否】



    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    /**
     * 重写方法
     * {@inheritDoc}
     * @see \Think\Model::addAll()
     */
    public function addAll($dataList, $options = '', $replace = FALSE)
    {
        if (empty($dataList) || !$dataList = $this->create($dataList))
        {
            $this->rollback();
            return false;
        }
        $arr = array();
        foreach ($dataList[static::$picUrl_d] as $key => &$value) {
            $arr[$key][static::$goodsId_d] = $dataList[static::$goodsId_d];
            $arr[$key][static::$picUrl_d]  = $value;
            $arr[$key][static::$status_d]  = 1; 
            $arr[$key][static::$isThumb_d] = false !== strpos($value, $this->thumbPerfix) ? 1 : 0;
        }
        sort($arr);
        unset($dataList);
        $status =  parent::addAll($arr, $options, $replace);
        
        if ($status === false) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return $status;
    }
    
    /**
     * 修改图片 
     * @param array $data post 数据
     * @param string $key 商品编号键
     * @return bool
     */
    public function editPicture(array $data, $key = 'goods_id')
    {
        $pic = $data[static::$picUrl_d];
        if (empty($pic) || !is_array($data) || empty($data[$key]))
        {
            return false;
        }
        $id = (int)$data[$key];
        
        $isExitsThumb = $this->where(static::$goodsId_d.' = %d', $id)->select();
        
        //分拣
        $this->mergeKey = self::$isThumb_d;
        
        $pic = $this->parsePictureByEdit($isExitsThumb);
        
        $temp = empty($pic[self::OriginalImageNoThumb]) ? [] : $pic[self::OriginalImageNoThumb];
        
        if (empty($pic[self::OriginalImageThumb])) {//没有缩略图时生成缩略图

            $imageSource =  AbstractParse::getInstance($data[static::$picUrl_d])->actionRun();
            $this->imageSource[] = $imageSource;
            
            $thumbImageArray = $this->buildThumbImage(intval($this->imageWidth), intval($this->imageHeight));
            
            $data[static::$picUrl_d] = array_merge($data[static::$picUrl_d], $thumbImageArray);
        } 
        
        //比较是否添加
        
        $receive= [];
        
        //判断是不是要添加
        $this->arrayData = $data[static::$picUrl_d];
        
        $this->byNameSplit      =  self::$picUrl_d; 
        
        $receive = $this->parseArrayByArbitrarily($temp);
        
        $receive = $this->compareDataByArray($receive);
           
       
        if (empty($receive)) { // 没有 要添加的
            return true;
        }
        
        $data[static::$picUrl_d] =  $receive; //要添加的图片
        
       
        $this->startTrans();
        return $this->addAll($data);
    }
    
    /**
     * 删除图片 
     */
    public function deletePicture ($id)
    {
        if (($id = intval($id)) === 0) {
            $this->rollback();
            return false;
        }
      
        $img = $this->where(static::$goodsId_d.' = %d', $id)->getField(static::$id_d.','.static::$picUrl_d);
        
        if (empty($img)) {
            $this->commit();
            return true;
        }
        $status = $this->where(static::$goodsId_d.' = %d', $id)->delete();
        
        //删除本地图片
        
        //添加删除缩略图监听
        Event::insetListen('thumbImage', function (array & $param){
          
            if (empty($param)) {
                return false;
            }
            $thumb =  $tmp = null;
            foreach ($param as $key => $value) {
                 
                $tmp = substr($value, strrpos($value, '/')+1);
        
                $thumb = 'thumb_'.$tmp;
                $value = './'.str_replace($tmp, $thumb, $value);
                 
                if (!is_file($value)) {
                    continue;
                }
                unlink($value);
                unset($param[$key]);
            }
        });
        
        Tool::partten($img, UnlinkPicture::class);
        if ($status !== false) {
            $this->commit();
            return true;
        }
        return false;
    }
    
    /**
     * 删除图片 
     */
    public function deleteManyPicture ($fileName)
    {
        if (empty($fileName)) {
            return false;
        }
        $thumbFileName = $this->thumbPerfix.$fileName;
        
        $status = $this->where(static::$picUrl_d.' in ("'.$fileName.'", "'.$thumbFileName.'")')->delete();
        return $status;
    }
    
    /**
     * @return the $thumbPerfix
     */
    public function getThumbPerfix()
    {
        return $this->thumbPerfix;
    }
    
    /**
     * @param string $thumbPerfix
     */
    public function setThumbPerfix($thumbPerfix)
    {
        $this->thumbPerfix = $thumbPerfix;
    }
    

    /**
     * @return the $imageWidth
     */
    public function getImageWidth()
    {
        return $this->imageWidth;
    }
    
    /**
     * @return the $imageHeight
     */
    public function getImageHeight()
    {
        return $this->imageHeight;
    }
    
    /**
     * @param number $imageWidth
     */
    public function setImageWidth($imageWidth)
    {
        $this->imageWidth = $imageWidth;
    }
    
    /**
     * @param number $imageHeight
     */
    public function setImageHeight($imageHeight)
    {
        $this->imageHeight = $imageHeight;
    }
}