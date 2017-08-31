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
use Think\AjaxPage;

/**
 * 日志模型 
 * @author 王强
 * @version 1.0.1
 */
class LogModel extends BaseModel 
{
  
    private static  $obj;

	public static $id_d;	//

	public static $adminId_d;	//管理员id

	public static $type_d;	//操作类型：0新增1修改2删除

	public static $tableId_d;	//表主键编号

	public static $tableName_d;	//表名

	public static $comment_d;	//表注释

	public static $createTime_d;	//创建时间

	public static $ip_d;	//IP地址


    /**
     * 获取类的实例
     * @return \Admin\Model\LogModel
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    //添加前操作
    protected function _before_insert(&$data, $options) {
        $data[static::$createTime_d] = time();
        $data[static::$ip_d] = get_client_ip();
        return $data;
    }
    
    
    /**
     * 分页查询日志 
     */
    public function getLogByPage (array $where = array(), $page = 15)
    {
        
        return $this->getDataByPage([
            'field' => $this->getDbFields(),
            'where' => $where,
            'order' => static::$createTime_d.static::DESC.','.static::$id_d.static::DESC,
        ], $page, false, AjaxPage::class);
        
    }
    
    /**
     * 添加日志 避免在控制器中添加 最少的改动  || 删除时添加日志 
     * @param array $logArray 日志数据 【抽象在高层】日志类模型需实现添加，其他模型无需实现添加
     * @return bool
     */
    public  function addLogByAnyOne(array $logArray) 
    {
        $obj = (static::$cloneObj);
        if (!$this->isEmpty($logArray) || (array)$this === (array)$obj) {//日志不需要记录自己
            return false;
        }
        
        $this->isLogObj = true;
        
        
        $status = $this->add($logArray);
        
        if ($status === false) {
            return false;
        }
        
        static::$primaryId = $status;
        static::$tbId = $logArray['table_id'];
        return $status;
    }
    
    
    /**
     * 更新日志 
     */
    public function updateLogStart( array $data)
    {
        
        if (!$this->isEmpty($data)) {
            return false;
        }
       
        $this->isLogObj = true;
        $obj = static::$cloneObj;
        //获取更新前数据
        $clientData = $obj->field(implode(',', $data['edit_colums']))->where($obj::$logPrimaryKey.'=%d', (int)$data['table_id'])->find();
        
        if (empty($clientData)) {
            return false;
        }
      
        $insertId = $this->add($data);
        if (!$this->traceStation($insertId)) {
            return false;
        }
        
        static::$primaryId = $insertId;
        
        static::$values = $clientData;
        
        static::$tbId = $data['table_id'];
        
        return $insertId;
    }
    
    /**
     * 删除日志 
     * @param int $id 数据表编号
     * @return bool
     */
    public function deleteId ($id)
    {
        $this->isLogObj = true;
        $this->startTrans();
        
        $status = $this->delete($id);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        return $status;
    }
}