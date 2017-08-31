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
 * 用户审批 保存模型
 */
class ApprovalUserModel extends BaseModel implements IsExitsModel
{
    private static $obj;

	public static $id_d;	//

	public static $enterpriseId_d;	//审批编号

	public static $isExpired_d;	//是否过期【0：过期，1未过期】

	public static $approvalTime_d;	//授权日期

	public static $beOverdue_d;	//有效期

	public static $createTime_d;	//创建时间

	public static $updateTime_d;	//更新时间


	public static $effective_d;	//是否有效【0 无效，1有效】

    
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
    protected function _before_insert(&$data, $options) {
        $data[static::$createTime_d] = time();
        $data[static::$updateTime_d] = time();
        $data[static::$approvalTime_d] = time();
        $data[static::$isExpired_d] = 1;//未过期
        $data[static::$effective_d] = 1;//有效
        return $data;
    }
    /**
     * 更新前操作
     * {@inheritDoc}
     * @see \Think\Model::_before_update()
     */
    protected function _before_update(&$data, $options) {//更新时间
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    
    /**
     *  用户审批 
     * @param array $post       post 数据
     * @param string $statusKey 状态的字段名
     * @return boolean
     */
    public function addApproval(array $post, $statusKey)
    {
        if (!$this->isEmpty($post) || empty($statusKey)) {
            $this->rollback();
            $this->error = '数据错误';
            return false;
        }
        
        $enterprise = (int)$post[static::$enterpriseId_d];
        
        
        if ($post[$statusKey] != 1) {
            
            $status = $this->where(static::$enterpriseId_d.'= %d', $enterprise)->save([static::$effective_d => 0]);
            
            $status === true ? $this->commit(): $this->rollback() ;
            return $status;
        }
        
        $post[static::$beOverdue_d] = !isset($post[static::$beOverdue_d]) ? 0 : (int) sprintf('%u', $post[static::$beOverdue_d]);
        //检测存在 且是否过期 如果 过期 重新添加
        $isExpired = $this->IsExits($post[static::$enterpriseId_d]);
       
        if ($isExpired) {//存在
            $post[static::$effective_d] = 1;
            $status = $this->where(static::$enterpriseId_d.'=%d and '.static::$isExpired_d.' = 1', $enterprise)->save($post);
        } else {
            $status = $this->add($post);
        }
        if ($status === false) {
            $this->error = '添加失败';
            $this->rollback();
            return $status;
        }
        $this->commit();
        return $status;
    }
    /**
     * 是否存在
     * {@inheritDoc}
     * @see \Common\Model\IsExitsModel::IsExits()
     */
    public function IsExits($post)
    {
        // TODO Auto-generated method stub
        if (($post = intval($post)) === 0) {
            return true;//错误记存在
        }
        
        $data = $this->field([
                static::$updateTime_d,
                static::$createTime_d
              ], true)->where(static::$enterpriseId_d.'=%d and '.static::$isExpired_d.'=1 ', $post)->find(); 
        
        if (empty($data)) {//不存在
            return false;
        }
        //存在 【判断是否过期】
        //计算过期时间
        $isExpiredTime = strtotime('+'.$data[static::$beOverdue_d].'day', $data[static::$approvalTime_d]);
        if ($isExpiredTime -time() < 0) {//过期
            $status = $this->where(static::$enterpriseId_d.'=%d', $post)->save([static::$isExpired_d => 0]);//更新状态已过期
            //过期既不存在
            return false;
        }
        return true;
    }
    /**
     * 根据客户信息 获取 审批信息 
     */
    public function getApprovalDataByUser (array $data, $key)
    {
        if (!$this->isEmpty($data)) {
            return array();
        }
        
        $data = $this->getDataByOtherModel($data, $key, [
            static::$id_d,
            static::$enterpriseId_d,
            static::$beOverdue_d,
            static::$approvalTime_d,
        ], static::$enterpriseId_d);
        
        return $data;
    }
}