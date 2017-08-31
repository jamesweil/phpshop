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
use Common\Tool\Event;
class GoodsAttrModel extends BaseModel
{
    
    public static $id_d;           //主键
    
    private static  $obj;
    
    protected $tempData ;
    
    protected $dbFields = [];

	public static $attributeId_d;	//商品属性编号

	public static $goodsId_d;	//商品id

	public static $attrValue_d;	//属性值

	public static $attrPrice_d;	//属性价格

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间
    
	protected $productId; //商品编号
    
	protected $varriableType = false; // 商品属性类型是否变了
	
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 生成商品属性 添加所需的HTML 
     * @param array $goodsAttributeData 商品属性数据
     * @param BaseModel $goodsAttributeModel 商品属性模型对象
     * @return string
     */
    public function buildHtmlString ($goodsAttributeData, BaseModel $goodsAttributeModel)
    {
        if (!$this->isEmpty($goodsAttributeData) || !($goodsAttributeModel instanceof BaseModel)) {
            return null;
        }
        
        $this->tempData = $goodsAttributeData;
        
        //获取属性值数据 空
        $data = $this->getAttributeValueData($goodsAttributeModel);
       
       
        
        if (empty($data)) {
            foreach ($data as $name => &$vo) { //设置默认值
                Tool::isSetDefaultValue($vo, $this->dbFields, '');
            }
           
        }
        $str = null;
        
        $addDelAttr = ''; // 加减符号
        foreach ($data as $key => $value) {
            
                $str .= "<tr class='{$value[$goodsAttributeModel::$id_d]}'>";
                $addDelAttr = '';
                // 单选属性 或者 复选属性
                if($value[$goodsAttributeModel::$attrType_d] == 1 || $value[$goodsAttributeModel::$attrType_d] == 2)
                {
//                     if($k == 0)
//                         $addDelAttr .= "<a onclick='GoodsOption.addAttribute(this)' href='javascript:void(0);'>[+]</a>&nbsp&nbsp";
//                         else
//                             $addDelAttr .= "<a onclick='GoodsOption.delAttribute(this)' href='javascript:void(0);'>[-]</a>&nbsp&nbsp";
                }
            
                $str .= "<td>$addDelAttr {$value[$goodsAttributeModel::$attrName_d]}</td> <td>";
                // 手工录入
                if ($value[$goodsAttributeModel::$inputType_d] == 0)
                {
                    $str .= "<input type='text' size='40' value='{$value[static::$attrValue_d]}' name='attr_{$goodsAttributeModel::$id_d}[{$value[$goodsAttributeModel::$id_d]}]' />";
                }
               
                // 从下面的列表中选择（一行代表一个可选值）
                if($value[$goodsAttributeModel::$inputType_d] == 1)
                {
                    $str .= "<select name='attr_{$goodsAttributeModel::$id_d}[{$value[$goodsAttributeModel::$id_d]}]'>";
                   
                    $tmpOptionVal = explode("\n", $value[$goodsAttributeModel::$attrValues_d]);
                    foreach($tmpOptionVal as $k2 => $v2)
                    {
                        // 编辑的时候 有选中值
                        $v2 = preg_replace("/\s/","",$v2);
                        if($value[static::$attrValue_d] == $v2)
                            $str .= "<option selected='selected' value='{$v2}'>{$v2}</option>";
                            else
                                $str .= "<option value='{$v2}'>{$v2}</option>";
                    }
                    $str .= "</select>";
                }
                // 多行文本框
                if($value[$goodsAttributeModel::$inputType_d] == 2)
                {
                    $str .= "<textarea cols='40' rows='3' name='attr_{$goodsAttributeModel::$id_d}[{$value[$goodsAttributeModel::$id_d]}]'>{$value[static::$attrValue_d]}</textarea>";
                }
                $str .= "</td></tr>";
        }
        return $str;
    }
    
    
    /**
     * 根据商品属性编号 获取属性值数据 
     */
    public function getAttributeValueData (BaseModel $goodsAttribute)
    {
        if (!$this->isEmpty($this->tempData)) {
            return array();
        }
        
        $field = $this->deleteFields([static::$createTime_d, static::$updateTime_d, static::$attrPrice_d]);
        
        $this->findWhere = ' and '.static::$goodsId_d.'='.$this->productId;
        
        $field[0] = static::$id_d.static::DBAS.'attrId';
        
        $attrValue = $this->getDataByOtherModel($this->tempData, $goodsAttribute::$id_d, $field, static::$attributeId_d);
        
        if (empty($attrValue)) {
            $this->dbFields = $field;
            return array();
        }
       
        return $attrValue;
    }
    
    /**
     * 添加属性值 
     */
    public function addAttributeData (array $data)
    {
        if (!$this->isEmpty($data) || empty($data['attr_id'])) {
            return false;
        }
        
        $attrValue = $data['attr_id'];
        
        $i = 0;
        $tmpData = [];
        foreach ($attrValue as $key => $value) {
            $tmpData[$i][static::$attributeId_d] = $key;
            $tmpData[$i][static::$goodsId_d]     = $data[static::$goodsId_d];
            $tmpData[$i][static::$attrValue_d]   = $value;
            $tmpData[$i][static::$createTime_d]  = time();
            $tmpData[$i][static::$updateTime_d]  = time();
            $i++;
        }
        $status =  $this->addAll($tmpData);
       
        if ($status === false) {
           $this->error = '添加失败';
           $this->rollback();
           return false;
        }
        $this->commit();
        return true;
    }
    
    /**
     * 更新商品属性 
     * <pre>
     * attr_id => Array
        (
            12 => 翻盖
            11 => 55
            10 => 33
        )
        </pre>
     */
    public function editAttributeData (array $post)
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        if ($this->varriableType) {// 类型变了 直接删除 再添加
           
            $status = $this->where(static::$goodsId_d.'=%d', (int)$post['goods_id'])->delete();
            
            return $this->addAttributeData($post);
            
        } //直接更新
            
        //获取要更新的字段
        $keyArray = [
            static::$attrValue_d,
            static::$updateTime_d
        ];
         
        $arr = array();
        $flag = null;
        foreach ($post['attr_id'] as $key => $value)
        {
            $arr[$key][] = $value;
            $arr[$key][] = time();
        }
        
        if (empty($arr)) {
            $this->rollback();
            return false;
        }
        
        //天加监听事件
        $table = $this->getTableName();
        
        $goodsId = $post['goods_id'];
        Event::insetListen('sql_update_where', function (& $where)use($goodsId) {
            
            $where .= ' and '.static::$goodsId_d.'='.$goodsId;
            
        });//修改批量更新where条件
        
        $this->fieldUpdate = static::$attributeId_d; //设置where 条件
       
        $sql = $this->buildUpdateSql($arr, $keyArray, $table);
        $status = parent::execute($sql);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        $this->commit();
        
        return $status;
    }
    /**
     * @return the $productId
     */
    public function getProductId()
    {
        return $this->productId;
    }
    
    /**
     * @param field_type $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }
    
    /**
     * @return the $varriableType
     */
    public function getVarriableType()
    {
        return $this->varriableType;
    }
    
    /**
     * @param boolean $varriableType
     */
    public function setVarriableType($varriableType)
    {
        $this->varriableType = $varriableType;
    }
     
}