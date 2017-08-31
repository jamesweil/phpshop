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
 * 发货地址列表 
 */
class SendAddressModel extends BaseModel
{
    private static $obj;
    
	public static $id_d;	//运送编号

	public static $addressId_d;	//发货地址编号

	public static $addressDetail_d;	//详细地址

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间

	public static $status_d;	//是否启用

	public static $stockName_d;	//仓库名称

	public static $default_d;	//是否默认


    public static function getInitnation()
    {
        $class = __CLASS__;
        return  static::$obj= !(static::$obj instanceof $class) ? new static() : static::$obj;
    }
    
    /**
     * 添加数据 
     */
    public function addAddress(array $post)
    {
        if (!$this->isEmpty($post) || empty($post[static::$addressId_d])) {
            return false;
        }
        
        $post[static::$addressId_d] = static::flag($post, static::$addressId_d);
        return $this->add($post);
    }
    
    /**
     * 保存数据 
     */
    public function saveEedit(array $post)
    {
      
        if (!$this->isEmpty($post) || empty($post[static::$addressId_d])) {
            return false;
        }
        $post[static::$addressId_d] = static::flag($post, static::$addressId_d);
       
        return $this->save($post);
    }
    /**
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected function _before_insert(& $data, $options)
    {
        $data[static::$createTime_d] = time();
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    /**
     * 更新数据
     * {@inheritDoc}
     * @see \Think\Model::_before_update()
     */
    protected function _before_update(& $data, $options)
    {
        $isExits = $this->editIsOtherExit(static::$stockName_d, $data[static::$stockName_d]);
        
        if ($isExits) {
            $this->rollback();
            $this->error = '已存在该名称：【'.$data[static::$stockName_d].'】';
            return false;
        }
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    public function getStatusOpenStock ($status)
    {
        if (($status = intval($status)) == 0) {
            return array();
        }
        
        $data = S('openSendAddress');
        if (empty($data)) {
            $data = $this->where(static::$status_d.' = %s', $status)->getField(static::$id_d.','.static::$stockName_d);
            
            S('openSendAddress', $data, 5);
        }
        return $data;
    }
    
    /**
     * 获取发货仓库信息 
     * @param int $id 仓库编号
     * @return array
     */
    public function getStockDataById ($id)
    {
        if (($id = intval($id)) === 0) {
            return array();
        }
        
        $data = $this->field(static::$updateTime_d.','.static::$createTime_d, true)->find($id);
        
        return $data;
    }
    
    /**
     * 设置默认 
     */
    public function setDefault ($post)
    {
        if (!$this->isEmpty($post) || ($status = intval($post[static::$id_d])) === 0) {
            return array();
        }
        
        $this->startTrans();
        $status = $this->where(static::$id_d.' != "%s"', $post[static::$id_d])->save(array(
            static::$default_d => 0
        ));
        
        if ($status === false) {
            $this->rollback();
            return false;
        }
        
        $status = $this->save($_POST);
        
        if ($status === false) {
            $this->rollback();
            return false;
        }
        $this->commit();
        
        return $status;
    }
}