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
 * 用户权限组 模型
 */
class AuthGroupAccessModel extends BaseModel
{
    private static $obj;

	public static $uid_d;	//管理员编号

	public static $groupId_d;	//组编号

	protected $receivePost = [];
    
    /**
     * 获取类的实例 
     */
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * @return the $receivePost
     */
    public function getReceivePost()
    {
        return $this->receivePost;
    }
    
    /**
     * @param multitype: $receivePost
     */
    public function setReceivePost($receivePost)
    {
        $this->receivePost = $receivePost;
    }
    
    /**
     * 添加用户到组 
     * @param int $inertId 管理员编号
     * @return boolean
     */
    public function addGroupAccess ($inertId)
    {
        if (($inertId = intval($inertId)) === 0 || !$this->isEmpty($this->receivePost)) {
            $this->rollback();
            return false;
        }
        
        $post = $this->receivePost;
        
        $post[static::$uid_d] = $inertId;
        
        $status = $this->add($post);
        
        if (!$this->traceStation($status)) {
            return false;
        }
        
        $this->commit();
        
        return true;
    }
}
