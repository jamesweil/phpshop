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
 * 日志内容模型 
 * @author 王强
 * @version 1.0.1
 */
class LogContentModel extends BaseModel
{
    private static  $obj;

	public static $id_d;	//主键

	public static $logId_d;	//日志主表编号

	public static $key_d;	//日志键

	public static $value_d;	//日志数据

	public static $currentValue_d;	//以前的值

	public static $comment_d;	//

	public static $createTime_d;	//创建时间

    /**
     * 获取类的实例
     * @return \Admin\Model\LogContentModel
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    //添加前操作
    protected function _before_insert(&$data, $options) {
        $data[static::$createTime_d] = time();
        return $data;
    }
    
    /**
     * 添加日志内容
     * @param array $logData 日志内容数据
     * @return boolean
     */
    public function addLogContentAnyOne(array $logData) 
    {
        if (!$this->isEmpty($logData)) {
            return false;
        }
        
        if (empty(self::$primaryId)) {
            return false;
        }
        
        //查询修改信息
        $obj = static::$cloneObj;
        //获取表字段注释
        
        $cloumNote = $obj->getComment();
        $this->isLogObj = true;
        //获取表数据【更新后】
        $data = $logData['edit_colums'];
     
        if (empty($data)) {//没有数据
            $this->rollback();
            return false;
        }
        $beforeData = static::$values;
        
        if (empty($beforeData)) {
            $this->rollback();
            return false;
        }
        
        $tempData = array();
        $i = 0;
        foreach ($data as $key => $value) {
            $tempData[$i][static::$logId_d] = static::$primaryId;
            $tempData[$i][static::$key_d]     = $key;
            $tempData[$i][static::$value_d] = !isset($beforeData[$key]) ? ' ' : $beforeData[$key];
            $tempData[$i][static::$currentValue_d] = $value;
            $tempData[$i][static::$comment_d] = $cloumNote[$key];
            $tempData[$i][static::$createTime_d] = time();
            $i++;
        }
       
        $status = $this->addAll($tempData);
        if (!$this->traceStation($status)) {
            return false;
        }
        
        return $status;
    }
    
    /**
     * 获取日志详细内容 
     */
    public function getNoteLog($id)
    {
        if (($id = (int)$id) === 0) {
            return array();
        }
        
        return $this->field([static::$createTime_d, static::$logId_d], true)->where(static::$logId_d.'=%d', $id)->select();
        
    }
    
    /**
     * 在其他表 添加数据时 记录日志 
     */
    public function otherTableAddDataByThisAddLog(array $param)
    {
        if (!$this->isEmpty($param)) {
            return false;
        }
        
        if (empty(self::$primaryId)) {
            return false;
        }
        
        //查询修改信息
        $obj = static::$cloneObj;
        //获取表字段注释
       
        $cloumNote = $obj->getComment();
        
        $data = $param['edit_colums'];
        
        if (empty($data)) {
            $this->rollback();
            return false;
        }
        $tempData = array();
        $i = 0;
        
        foreach ($data as $key => $value) {
            $tempData[$i][static::$logId_d] = static::$primaryId;
            $tempData[$i][static::$key_d]     = $key;
            $tempData[$i][static::$value_d] = ' ';
            $tempData[$i][static::$currentValue_d] = empty($value) ? ' ': $value;
            $tempData[$i][static::$comment_d] = $cloumNote[$key];
            $tempData[$i][static::$createTime_d] = time();
            $i++;
        }
        
        $status = $this->addAll($tempData);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        
        return $status;
    }
    
    /**
     * 根据主表编号 删除 从表数据 
     * @param int $id 日志主表编号
     * @return bool
     */
    public function deleteByLogId ($id)
    {
        $this->isLogObj = true;
        if (($id = intval($id)) === 0) {
            $this->rollback();
            return false;
        }
        
        $status = $this->where(static::$logId_d.'=%d', $id)->delete();
        
        if (!$this->traceStation($status)) {
            return false;
        }
        
        $this->commit();
        
        return $status;
    }
    /**
     * 根据主表编号 删除 从表数据
     * @param int $id 日志从表编号
     * @return bool
     */
    public function deleteById($id)
    {
        $this->isLogObj = true;
        if (($id = intval($id)) === 0) {
            return false;
        }
        
        $status = $this->delete($id);
        
        return $status;
    }
}