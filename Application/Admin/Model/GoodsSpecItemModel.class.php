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


namespace Admin\Model;


use Think\Model;
use Common\Model\BaseModel;

/**
 * 商品规格 模型
 * @author 王强
 * @version 1.0.0
 */
class GoodsSpecItemModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//规格项id

	public static $specId_d;	//规格id

	public static $item_d;	//规格项
    
	private $titleKey;         // 商品标题键
	
	private $title ; //商品标题
   

    /**
     * 获取类的实例
     * @return \Admin\Model\GoodsSpecItemModel
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 根据编号获取数据
     * @param int $spec_id
     */
    public function getSpecItem($spec_id){
        $arr = $this->where("spec_id = $spec_id")->order('id')->select();
        $arr = get_id_val($arr, 'id','item');
        return $arr;
    }
    /**
     * 根据规格生成对应得 商品名称
     * @param array $data 商品规格数据
     * @return array
     */
    public function getGoodsNameByItem(array $data)
    {
        if (empty($data)) {
            return array();
        }
        
        $itemIds = str_replace('_', ',', implode('_', array_keys($data)));
     
        if (empty($itemIds)) {
            return array();
        }
        
        $itemData = $this->where( array(static::$id_d => array('in', $itemIds)) )->getField(static::$id_d.','.static::$item_d);
        
        if (empty($itemData)) {
            return array();
        }
        
        $flag = null;
        
        $name = null;
        
        $titleKey = $this->titleKey;
        $title    = $this->title;
        
        foreach ($data as $key => & $value) {
            
            $flag = explode('_', $key);
            
            foreach ($flag as $itemValue) {
                
                if (!array_key_exists($itemValue, $itemData)) {
                    continue;
                }
                $name .= ' '.$itemData[$itemValue];
                $value[$titleKey] = $title . ' '.substr($name, 1);
            }
            $name = null;
        }
        return $data;
    }
    
    /**
     * @param field_type $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    

    /**
     * @param field_type $titleKey
     */
    public function setTitleKey($titleKey)
    {
        $this->titleKey = $titleKey;
    }
    
}