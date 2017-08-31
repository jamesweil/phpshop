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

namespace Home\Model;
use Think\Page;
use Common\TraitClass\callBackClass;
use Common\Tool\Tool;
use Common\Model\BaseModel;

class InvoiceGoodsModel extends BaseModel{

	public static $id_d;	//主键id

	public static $goodsId_d;	//商品编号

	public static $goodsCompany_d;	//单位

	public static $goodsNum_d;	//数量

	public static $goodsPrice_d;	//单价(含税)

	public static $goodsPrice_num_d;	//金额(含税)

	public static $goodsTax_rate_d;	//税率

	public static $goodsTax_d;	//税额

	public static $goodsPay_type_d;	//付款方式

	public static $goodsDue_date_d;	//到期日

	public static $goodsRemarks_d;	//备注

	public static $goodsOrder_id_d;	//订单id

	public static $addTime_d;	//添加时间

	public static $editTime_d;	//修改时间

	public static $invoiceId_d;	//发票id

	private static $obj;
	public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    //查询发票商品表数据
    public function getInvoiceGoodsByInvoice(array $Invoice){
    	if (empty($Invoice)) {
    		return false;
    	}
        foreach ($Invoice as $key => $value) {
        	$where['invoice_id'] = $value['id'];
        	$res = $this->where($where)->select();
        	
        	$Invoice[$key]['goods'] = $res;
        	
        }
        return $Invoice;
    }
     //根据id查询发票商品表数据
    public function getInvoiceGoodsById($id){
        if (empty($id)) {
            return false;
        }       
        $where['invoice_id'] = $id;
        $res = $this->where($where)->select();            
        return $res;
    }
}