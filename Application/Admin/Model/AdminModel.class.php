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
use Common\Model\IsExitsModel;

/**
 * 后台 管理员模型 
 * @version 1.0.1
 * @copyright  Copyright © 2003-2023 亿速网络 
 * @link www.yisu.cn
 */
class AdminModel extends BaseModel implements IsExitsModel
{
    private static $obj;

	private $adminIdKey = null; //管理员键
	
	public static $id_d;	//管理员ID

	public static $account_d;	//管理员账号

	public static $password_d;	//管理员密码

	public static $loginTime_d;	//最后登录时间

	public static $loginCount_d;	//登录次数

	public static $status_d;	//账户状态，禁用为0   启用为1

	public static $groupId_d;	//所属分组的ID

	public static $createTime_d;	//创建时间

	/**
	 * 获取当前模型的实例
	 * @return \Admin\Model\AdminModel
	 */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 添加前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected function _before_insert(&$data, $options) 
    {
        $data[static::$createTime_d] = time();
        return $data;
    }
    
    /**
     * 保存管理员数据 
     * @param array $post post数据
     * @return boolean
     */
    public function saveEdit(array $post)
    {
        if (!$this->isEmpty($post)) {
            $this->rollback();
            return false;
        }
        
        $status = $this->save($post);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        $this->commit();
        return $status !==false;
    }
    
    /**
     * 获取管理员数据 
     * @param array $data 其他模型数据
     * @param string $split 分割数据的键
     */
    public function getAdminUserData (array $data, $split)
    {
        if (!$this->isEmpty($data) || empty($split)) {
            return array();
        }
        
        return $this->getDataByOtherModel($data, $split, [
            static::$id_d,
            static::$account_d
        ], static::$id_d);
    }
    
    /**
     * 添加管理员 
     */
    public function addAdminUser (array $post)
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        if ($this->IsExits($post[static::$account_d])) { 
            $this->error = '已存在该管理员';
            return false;
        }
        $this->startTrans();
        $post[static::$password_d] = md5($post[static::$password_d]);
        
        $insertId = $this->add($post);
        
        if (!$this->traceStation($insertId)) {
            return false;
        }
        return $insertId;
        
    }
    
    /**
     * 获取后台日志用户
     * @param array $array 日志搜索条件
     * @return array;
     */
    public function getAdminUser (array $data)
    {
        if (empty($data[$this->adminIdKey])) {
            return array();
        }
        
        $param = array();
        
        $param[static::$account_d] = array('like', $data[$this->adminIdKey].'%');
        
        $idArray = $this->where($param)->getField(static::$id_d.','.static::$account_d);
        return $idArray;
    }
    
    /**
     * 获取后台日志用户搜索条件  
     * @param array $data 日志搜索条件
     * @return array
     */
    public function getAdminUserWhere (array &$data)
    {
        $idArray = $this->getAdminUser($data);
        
        if (empty($idArray)) {
            return array();
        }
        
        $param = array();
        
        $inWhere = implode(',', array_keys($idArray));
        
        $param[$this->adminIdKey] = array('in', [$inWhere]);
        unset($data[$this->adminIdKey]);
        return $param;
        
    }
    
    /**
     * 是否存在 该管理员账号
     * {@inheritDoc}
     * @see \Common\Model\IsExitsModel::IsExits()
     */
    public function IsExits($post)
    {
        // TODO Auto-generated method stub
        return $this->where(static::$account_d.' = "%s"', $post)->getField(static::$id_d) ? true : false;
    }
    
    /**
     * 获取管理员键名
     * @return string $adminIdKey
     */
    public function getAdminId()
    {
        return $this->adminIdKey;
    }
    
    /**
     * 设置管理员键名
     * @param string $adminId
     */
    public function setAdminId($adminIdKey)
    {
        $this->adminIdKey = $adminIdKey;
    }
}