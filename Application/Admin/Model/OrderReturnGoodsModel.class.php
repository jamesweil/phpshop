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
use Common\Tool\Tool;

/**
 * @author 王强【订单退货模型】
 */
class OrderReturnGoodsModel extends BaseModel
{
    private static $obj;

	public static $id_d;	//

	public static $orderId_d;	//订单id

	public static $tuihuoCase_d;	//退货理由

	public static $createTime_d;	//申请时间

	public static $revocationTime_d;	//撤销时间

	public static $updateTime_d;	//修改时间

	public static $goodsId_d;	//退货的商品id

	public static $explain_d;	//退货(退款)说明

	public static $price_d;	//退货(退款)金额

	public static $voucher_d;	//凭证

	public static $isReceive_d;	//退款时是否收到货1未收到2收到

	public static $type_d;	//2退款1退货

	public static $status_d;	//0审核中1审核失败2审核通过3退货中4退款中5完成6.已撤销

	public static $userId_d;	//用户编号

	public static $number_d;	//申请数量

	public static $applyImg_d;	//申请图片

	public static $message_d;	//审核留言

	public static $auditor_d;	//审核人

	public static $content_d;	//审核内容

    /**
     * 获取类的实例
     * @return \Admin\Model\OrderReturnGoodsModel
     */
    public static function getInitnation()
    {
        $class = __CLASS__;
        
        return static::$obj= !(static::$obj instanceof $class) ? new static() : static::$obj;
    }
    
    /**
     * 获取退货列表数据 
     */
    public function getContent($order, array $where = array())
    {
        $dbFields = $this->getDbFields();
        if (!in_array($order['orderBy'], $dbFields, true) || 
            empty($order['sort']) || 
            !in_array($order['sort'], [static::asc, static::desc], true)
          ) 
        {
            return array();
        }
        
        if (!empty($where[static::$orderId_d])) {
            $where[static::$orderId_d] = ['in', str_replace('"', null, $where[static::$orderId_d])];
        } elseif (isset($where[static::$orderId_d])) {
            unset($where[static::$orderId_d]);
        }
        
        $cache = S('ORDER_RETURN_CACHE_DATA');
       
        if (empty($cache)) {
            
            $cache = $this->getDataByPage([
                'field' => [
                    static::$id_d,
                    static::$orderId_d,
                    static::$goodsId_d,
                    static::$createTime_d,
                    static::$type_d,
                    static::$status_d,
                    static::$isReceive_d
                ],
                'where' => $where,
                'order' => $order['orderBy'].' '.$order['sort'],
            ], 10, false, AjaxPage::class);
          
            if (empty($cache)) {
                return array();
            }
          
            S('ORDER_RETURN_CACHE_DATA', $cache, 2);
        }
        return $cache;
    }
    /**
     * 退货详情 
     */
    public function getReturnDetail($id)
    {
        //版权所有©亿速网络
        if ( ($id = intval($id)) === 0 ) {
            return array();
        }
        
        $data = $this->field($this->getDbFields())->where(static::$id_d.'=%d', $id)->find();
        return $data;
    }
    
    /**
     * 获取退款数据 
     */
    public function getReturnData ($id, $type)
    {
        if (($id = intval($id)) === 0 || ($type = intval($type))===0 ) {
            return array();
        }
        
        $data = $this->field([
            static::$orderId_d,
            static::$goodsId_d,
            static::$userId_d,
            static::$isReceive_d,
            static::$status_d,
            static::$type_d
        ])->where(static::$id_d.'=%d', $id)->find();
      
        if (empty($data) ) {//退款申请
            $this->error = '数据有误 请仔细核对';
            return array();
        }
      
        switch (intval($data[$type])) {//类型【2退款1退货0换货】
            case 0:
            case 1:
                if ($data[static::$isReceive_d] == 1) {
                    $this->error = '未收到货';
                    return null;
                }
                return $data;
                break;
            case 2:
                return $data;
                break;
        }
    }
    
    protected function _before_update(&$data, $options) {
        $data[static::$updateTime_d] = time();
        return $data;
    }
    
    /**
     * 修改状态 
     */
    public function saveStatus ($id)
    {
        if ( ($id = intval($id)) === 0) {
            return false;
        }
        
        $this->startTrans();
        
        $status = $this->save([
            static::$id_d => $id,
            static::$status_d => 5,
        ]);
        
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        
        return $status;
    }
    
    /**
     * 修改退货状态【退款成功后】 
     * @param int $orderId
     * @param array $goodsIdArray
     */
    public function editReturnStatus($orderId, array & $goodsIdArray)
    {
        file_put_contents("./Uploads/ssfsd.txt", $goodsIdArray);
        file_put_contents("./Uploads/ssfgsd.txt", $orderId);
        if ( ($orderId = intval($orderId)) === 0 || !$this->isEmpty($goodsIdArray)) {
            return false;
        }
        
        $goodsIdArray = Tool::characterJoin($goodsIdArray, $this->split);
        
       file_put_contents("./Uploads/sssd.txt", $goodsIdArray);
       
       file_put_contents("./Uploads/ssswssd.txt", $this->split);
        
        if (empty($goodsIdArray)) {
            return FALSE;
        }
        
        $this->startTrans();
        
        $status = $this->where(static::$orderId_d.'=%d and '.static::$goodsId_d.' in ('.$goodsIdArray.')', $orderId)->save([
            static::$status_d => 5,
        ]);
        file_put_contents("./Uploads/daf.txt", $this->getLastSql());
        if (!$this->traceStation($status)) {
            $this->rollback();
            return false;
        }
        return $status;
    }
}