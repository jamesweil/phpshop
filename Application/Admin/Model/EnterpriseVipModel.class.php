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
use Common\Tool\Event;
                
/**
 * VIP 合约客户模型 
 * @author 王强
 */
class EnterpriseVipModel extends BaseModel
{
    private static $obj;
    
	private $status = [
	  '已申请',
	  '已通过',
	  '拒绝'
	];

	public static $id_d;	//vip合约用户申请表

	public static $userId_d;	//用户id

	public static $companyName_d;	//公司名称

	public static $provId_d;	//省

	public static $city_d;	//市

	public static $dist_d;	//县

	public static $address_d;	//详细地址

	public static $applytel_d;	//申请手机

	public static $applyName_d;	//申请人名字

	public static $respontel_d;	//负责人手机

	public static $responName_d;	//负责人名字

	public static $estimate_d;	//预计每月采购办公用品金额【0:2000-5000  1:5000-10000 2:10000以上】

	public static $remarks_d;	//备注

	public static $status_d;	//是否通过【0：已申请 1：已通过 2：拒绝】

	public static $createTime_d;	//创建时间


    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
    /**
     * 获取审核列表 
     */
    public function getApprovalList($number, $isFilter = FALSE)
    {
        if (($number = intval($number)) === 0) {
            return array();
        }
        
        $field = [
            static::$id_d,
            static::$applyName_d,
            static::$companyName_d,
            static::$responName_d,
            static::$status_d,
            static::$userId_d,
            static::$createTime_d
        ];
        
        Event::listen('listenField', $field);
        
        $option = [
          'field' => $field,
          'where' => $this->where,
          'order' => $this->order
        ];
        
        $data = $this->getDataByPage($option, $number, $isFilter, AjaxPage::class);
        
        return $data;
        
    }
    
    
    /**
     * 保存修改 
     */
    public function saveData (array $post)
    {
        if (!$this->isEmpty($post)) {
            $this->error = '数据错误';
            return false;
        }
        if (($post[static::$status_d]) != 1) {//审核未通过
            return $this->save($post);
        }
        $this->setIsOpenTranstion(true);//审核通过 向表里添加数据
        
        $status = $this->save($post);
       
        if (!$this->traceStation($status)) {
            return false;
        }
        static::$approvalPuss = true;
       
        return $status;
    }
    
    /**
     * @return the $status
     */
    public function getStatus()
    {
        return $this->status;
    }
}
