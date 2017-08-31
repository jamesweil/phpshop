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

namespace Admin\Controller;
use Common\Controller\AuthController;
use Think\Auth;

//后台管理员
class DownloadController extends AuthController{
	
	private function order_goods($id){
		$goods_orders_record=M('goods_orders_record');
		$rows=$goods_orders_record->where(array('goods_orders_id'=>$id))->select();
		//echo $goods_orders_record->getLastSql();
		return $rows;
		
	}
	public function order(){
		$goods_orders=M('goods_orders');
		$id=I('get.id');
		$order_info=$goods_orders->where(array('id'=>$id))->find();
		if($id==''){
			exit("非法请求!");
		}
		//dump($order_info);exit;
		//exit("该功能未做!");
		//dump($id);exit;
		$this->excel($id,$order_info);
	}
	private function excel($id,$info){
		$rows=$this->order_goods($id);
		/* dump($rows);
		exit; */
        vendor('PHPExcel');
        vendor('PHPExcel.Writer.Excel2007');
        vendor('PHPExcel.IOFactory');
        //$objPHPExcel = new \PHPExcel();
        if (!file_exists("./Public/Admin/excel/order.xlsx")) {
            exit("Excel模板文件丢失.\n");
        }
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load("./Public/Admin/excel/order.xlsx");
		$i=5;
		$objPHPExcel->getActiveSheet()->setCellValue('A2',$id);
		$objPHPExcel->getActiveSheet()->setCellValue('B2',$info['orders_num']);
		$objPHPExcel->getActiveSheet()->setCellValue('C2',$info['user_id']);
		$objPHPExcel->getActiveSheet()->setCellValue('D2',$info['coupons_id']);
		$objPHPExcel->getActiveSheet()->setCellValue('E2',$info['money_youhui']);
		$objPHPExcel->getActiveSheet()->setCellValue('F2',$info['price_sum']);
		$objPHPExcel->getActiveSheet()->setCellValue('G2',$info['price_shiji']);
		$objPHPExcel->getActiveSheet()->setCellValue('H2',$info['user_address']);
		$objPHPExcel->getActiveSheet()->setCellValue('I2',$info['realname']);
		$objPHPExcel->getActiveSheet()->setCellValue('J2',$info['mobile']);
		$objPHPExcel->getActiveSheet()->setCellValue('K2',date("Y-m-d H:i:s",$info['pay_time']));
		if($info['pay_status']==1){
			$objPHPExcel->getActiveSheet()->setCellValue('L2',"已支付");
		}else{
			$objPHPExcel->getActiveSheet()->setCellValue('L2',"未支付");
		}
		
		$objPHPExcel->getActiveSheet()->setCellValue('M2',date("Y-m-d H:i:s",$info['update_time']));
		$objPHPExcel->getActiveSheet()->setCellValue('N2',$info['kuaidi_name']);
		$objPHPExcel->getActiveSheet()->setCellValue('O2',$info['kuaidi_num']);
		if($info['orders_status']==0){
			$zt="待支付";//orders_status    0 订单默认状态    2  已发货      3 已签收    4 已申请退货   5 已完成
		}elseif($info['orders_status']==1){
			$zt="待发货";
		}elseif($info['orders_status']==2){
			$zt="已发货";
		}elseif($info['orders_status']==3){
			$zt="已签收";
		}elseif($info['orders_status']==4){
			$zt="已申请退货";
		}elseif($info['orders_status']==5){
			$zt="已完成";
		}
		$objPHPExcel->getActiveSheet()->setCellValue('P2',$zt);
		$objPHPExcel->getActiveSheet()->setCellValue('Q2',date("Y-m-d H:i:s",$info['fahuo_time']));
		//$objPHPExcel->getActiveSheet()->setCellValue('R2',date("Y-m-d H:i:s",$info['create_time']));
		$objPHPExcel->getActiveSheet()->setCellValue('R2',$info['num_sum']);
		$objPHPExcel->getActiveSheet()->setCellValue('S2',date("Y-m-d H:i:s",$info['rate_time']));
		$objPHPExcel->getActiveSheet()->setCellValue('T2',$info['rate_status']);
		
		if($info['order_type']==0){
			$objPHPExcel->getActiveSheet()->setCellValue('U2',"商品");
		}elseif($info['order_type']==1){
			$objPHPExcel->getActiveSheet()->setCellValue('U2',"旅游");
		}elseif($info['order_type']==2){
			$objPHPExcel->getActiveSheet()->setCellValue('U2',"购买会员");
		}elseif($info['order_type']==3){
			$objPHPExcel->getActiveSheet()->setCellValue('U2',"购买合伙人");
		}
		$objPHPExcel->getActiveSheet()->setCellValue('V2',$info['use_jf_currency']);
		$objPHPExcel->getActiveSheet()->setCellValue('W2',$info['use_jf_limit']);
		$objPHPExcel->getActiveSheet()->setCellValue('X2',$info['order_remarks']);
		$objPHPExcel->getActiveSheet()->setCellValue('Y2',date("Y-m-d H:i:s",$info['tuihuo_time']));
		$objPHPExcel->getActiveSheet()->setCellValue('Z2',date("Y-m-d H:i:s",$info['shouhuo_time']));
		$objPHPExcel->getActiveSheet()->setCellValue('AA2',date("Y-m-d H:i:s",$info['pingjia_time']));
		$objPHPExcel->getActiveSheet()->setCellValue('AB2',date("Y-m-d H:i:s",$info['tuihuo_chuli_time']));
		$objPHPExcel->getActiveSheet()->setCellValue('AC2',$info['tuihuo_chuli_status']);
		
		$objPHPExcel->getActiveSheet()->setCellValue('AD2',$info['fanli_jifen']);
		$objPHPExcel->getActiveSheet()->setCellValue('AE2',$info['yunfei']);
		$objPHPExcel->getActiveSheet()->setCellValue('AF2',$info['chufa_address']);
		$objPHPExcel->getActiveSheet()->setCellValue('AG2',$info['fanli_action']);
		$objPHPExcel->getActiveSheet()->setCellValue('AH2',$info['tuihuo_zhiqian_status']);
		if($info['is_used']==1){
			$is_use="是";
		}else{
			$is_use="否";
		}
		$pid=$this->getPid($v['user_id']);
		$objPHPExcel->getActiveSheet()->setCellValue('AI2',$is_use);
		$objPHPExcel->getActiveSheet()->setCellValue('AJ2',$pid);
		
		//$objPHPExcel->getActiveSheet()->setCellValue('B2',$info['tuihuo_chuli_status']);
		 $objPHPExcel->getActiveSheet()->setCellValueExplicit('B2',$info['orders_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('B2')->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('O2',$info['kuaidi_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('O2')->getNumberFormat()->setFormatCode("@");
		foreach($rows as $k=>$v){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$v['goods_id']);//
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$v['goods_title']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$v['taocan_name']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$v['goods_num']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$v['price_new']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$v['price_new']*$v['goods_num']);
			++$i;
		}
        
           /*  ->setCellValue('E27', '填表日期：   '.date("Y年m月d日",mktime())); */
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="订单明细表'.$info['orders_num'].'.xlsx"');//总报表
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');

    }
	public function down_rows(){
		ini_set("max_execution_time",0);
		if(IS_POST){
			//dump(I("post."));
			$where['orders_status']=I("orders_status");
		if(I('post.yymm1')!="" && I('post.yymm2')!=""){
			$yymm1=I('post.yymm1');
			$yymm2=I('post.yymm2');
			$yymm1=strtotime($yymm1);
			$yymm2=strtotime($yymm2);
			
			$where['pay_time']=array("egt",$yymm1);
			$where['pay_time']=array("elt",$yymm2);
			
		}
		//echo $yymm2;
		$goods_orders=M('goods_orders');
		$data=$goods_orders->where($where)->select();
		//dump($goods_orders->getLastSql());
		/* dump($data);
		exit; */
		vendor('PHPExcel');
        vendor('PHPExcel.Writer.Excel2007');
        vendor('PHPExcel.IOFactory');
        //$objPHPExcel = new \PHPExcel();
        if (!file_exists("./Public/Admin/excel/order_all.xlsx")) {
            exit("Excel模板文件丢失.\n");
        }
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load("./Public/Admin/excel/order_all.xlsx");
		$i=2;
		foreach($data as $k=>$v){
			//$info=$goods_orders->where(array('id'=>$v['id']))->find();
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$v['id']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$v['orders_num']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$v['user_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$v['coupons_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$v['money_youhui']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$v['price_sum']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$v['price_shiji']);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$v['user_address']);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$v['realname']);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$i,$v['mobile']);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$i,date("Y-m-d H:i:s",$v['pay_time']));
			if($v['pay_status']==1){
				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i,"已经支付");
			}else{
				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i,"未支付");
			}
			
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i,date("Y-m-d H:i:s",$v['update_time']));
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$i,$v['kuaidi_name']);
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$i,$v['kuaidi_num']);
			if($v['orders_status']==0){
				$zt="待支付";//orders_status    0 订单默认状态    2  已发货      3 已签收    4 已申请退货   5 已完成
			}elseif($v['orders_status']==1){
				$zt="待发货";
			}elseif($v['orders_status']==2){
				$zt="已发货";
			}elseif($v['orders_status']==3){
				$zt="已签收";
			}elseif($v['orders_status']==4){
				$zt="已申请退货";
			}elseif($v['orders_status']==5){
				$zt="已完成";
			}
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$i,$zt);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i,date("Y-m-d H:i:s",$v['fahuo_time']));
			//$objPHPExcel->getActiveSheet()->setCellValue('R'.$i,date("Y-m-d H:i:s",$v['create_time']));
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$i,$v['num_sum']);
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$i,date("Y-m-d H:i:s",$v['rate_time']));
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$i,$v['rate_status']);
			if($v['order_type']==0){
			$objPHPExcel->getActiveSheet()->setCellValue('U'.$i,"商品");
			}elseif($v['order_type']==1){
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i,"旅游");
			}elseif($v['order_type']==2){
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i,"购买会员");
			}elseif($v['order_type']==3){
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i,"购买合伙人");
			}
			//$objPHPExcel->getActiveSheet()->setCellValue('U'.$i,$v['order_type']);
			$objPHPExcel->getActiveSheet()->setCellValue('V'.$i,$v['use_jf_currency']);
			$objPHPExcel->getActiveSheet()->setCellValue('W'.$i,$v['use_jf_limit']);
			$objPHPExcel->getActiveSheet()->setCellValue('X'.$i,$v['order_remarks']);
			$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i,date("Y-m-d H:i:s",$v['tuihuo_time']));
			$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i,date("Y-m-d H:i:s",$v['shouhuo_time']));
			$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i,date("Y-m-d H:i:s",$v['pingjia_time']));
			$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i,date("Y-m-d H:i:s",$v['tuihuo_chuli_time']));
			$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i,$v['tuihuo_chuli_status']);
			
			$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i,$v['fanli_jifen']);
			$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i,$v['yunfei']);
			$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i,$v['chufa_address']);
			$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i,$v['fanli_action']);
			$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i,$v['tuihuo_zhiqian_status']);
			if($v['is_used']==1){
				$is_use="是";
			}else{
				$is_use="否";
			}
			$pid=$this->getPid($v['user_id']);
			$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i,$is_use);
			$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i,$pid);
		
			//$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$v['tuihuo_chuli_status']);
			 $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$i,$v['orders_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getNumberFormat()->setFormatCode("@");
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('O'.$i,$v['kuaidi_num'],\PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('O'.$i)->getNumberFormat()->setFormatCode("@");
			++$i;
		}
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="订单区间查询明细表'.'.xlsx"');//总报表
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
		}else{
			$this->display();
		}
		
	}
	public function getPid($id){
		$member=M("member","vip_");
		return $member->where(array('id'=>$id))->getField("pid");
	}
}