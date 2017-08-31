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

/**
 * 自定义页面分组
 * 
 * @author 王强
 * @version
 *
 */
class CustomPageClassModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//

	public static $nameChina_d;	//中文分组名称

	public static $nameEnglish_d;	//英文分组名称


    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = ! (static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 获取 分组 数据 
     */
    public function getCustomData ()
    {
        return $this->cache(50)->getField(static::$id_d.','.static::$nameChina_d);
    }
    
    /**
     * 生成option 
     */
    public function buildSelectOption ()
    {
        $customData = $this->getCustomData();
        
        if ( !$this->isEmpty($customData) ) {
            return null;
        }
        
        $str = '';
        
        foreach ($customData as $key => $value) {
            $str .= '<option value="'.$key.'">'.$value.'</option>'."\n";
        }
        return $str;
    }
    
    /**
     * 获取分组文件名称
     * @param integer $id
     */
    public function getGroupNameById ($id)
    {
        if (($id = (int)$id) === 0) {
            return null;
        }
        
        return $this->where(static::$id_d.'=%d', $id)->getField(static::$nameEnglish_d);
    }
    
}
