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

namespace Home\Controller;
use Home\Model\OrderModel;
use Home\Model\GoodsModel;
use Home\Model\BrandModel;
use Common\Model\BaseModel;
//商品中心
class GoodsCenterController extends BaseController{
	//商品搜索
	public function goods_search(){
		//查询所有商品
		$goods = GoodsModel::getGoodsAll();
		////查询对应的商品品牌
	    $data = BrandModel::getBrandByData($goods['res']);
	    foreach ($data as $key => $value) {
	    	$goods_id .= $value['id'].',';
	    }
	    if (!empty($goods_id)) {
	    	$id = substr($goods_id,0,-1);
	    }
	    $this->assign('id',$id);
        $page = $goods['page'];
	    $this->assign('data',$data);
	    $this->assign('page',$page);
		$this->display();
	}
	//查找
	public function search(){
		if (IS_POST) {
			$code = I('post.code');
			if (!empty($code)) {
				$goods = M('Goods')->where('code='.$code)->select();
	        ////查询对应的商品品牌
			    $data = BrandModel::getBrandByData($goods);
			    foreach ($data as $key => $value) {
			    	$goods_id .= $value['id'].',';
			    }
			    if (!empty($goods_id)) {
			    	$id = substr($goods_id,0,-1);
			    }
			    $this->assign('id',$id);
			    $page = '';
			    $this->assign('data',$data);
			    $this->assign('page',$page);
			    $this->display('goods_search');
			}
		}
	}

	public function exportExcel($expTitle,$expCellName,$expTableData){
		$xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
		$fileName = $expTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
		$cellNum = count($expCellName);
		$dataNum = count($expTableData);
		vendor("PHPExcel.PHPExcel");

		$objPHPExcel = new \PHPExcel();
		$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

		$objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
		// $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
		for($i=0;$i<$cellNum;$i++){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
		}
		// Miscellaneous glyphs, UTF-8
		for($i=0;$i<$dataNum;$i++){
			for($j=0;$j<$cellNum;$j++){
				$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
			}
		}
		ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
		header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	/**
	 * 全部导出excel
	 * 当前页导出execl
	 *
	 * 通过当前页数（p）来进行判断是全部导出还是当前页导出
	 *     1.如果有p参数，就是当前页导出、
	 *     2.如果没有p参数，就是全部导出
	 */
	public function expGoods(){
		$tj_value = json_decode($_GET['tj_value'],true);
		$id = $tj_value['id'];
		//获取p参数
		$xlsName  = "goods";
		$xlsCell  = array(
			array('brand_id','品牌'),
			array('id','商品编码'),
			array('code','商品型号'),
			array('title','商品名称'),
			array('price_market','标准价格'),
			array('price_member','会员价'),
		);
		$xlsModel = M('Goods');
		if($id){//当前页导出excel
			$where['id'] = array('IN',$id);
			$xlsData  = $xlsModel
				->field('brand_id,id,code,title,price_market,price_member')
				->where($where)
				->select();
		}
		foreach($xlsData as &$v){
			//用商品分类表里面的class_name来替换class_id
			$v['brand_id'] = M('brand')->where(['id'=>$v['brand_id']])->getField('brand_name');
		};
		$this->exportExcel($xlsName,$xlsCell,$xlsData);

	}

}