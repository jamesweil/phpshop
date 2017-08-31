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
use Think\Controller;

class BwardController extends CartController{
	private $groupId = 51;//分组id
	private $Public_pid='10000000';//默认收益账号
	/**
	 * 我的类型是合伙人的提成逻辑
	 */
	public function sVipLogic($id,$mon=array()){
		ini_set("max_execution_time", 0);
		$company_month=M('company_month','vip_');
		$person_month=M('person_month','vip_');
		$year=$mon[0];
		$month=$mon[1];
		$member=M('member','vip_');
		//如果是合伙人
		//增加公司合伙人人数
		$this->makeCompanyMonth($year,$month);
		$company_month->where(array('year'=>$year,'month'=>$month))->setInc('add_hhr_num',1);//
		$company_month->where(array('year'=>$year,'month'=>$month))->setInc('add_hhr_money',30000);
		$pid=$member->where(array('id'=>$id))->getField('pid');
		//个人月度
		$this->makeMonth($pid,$year,$month);
		$person_month->where(array('member_id'=>$pid,'year'=>$year,'month'=>$month))->setInc('add_hhr1_num',1);
		$bfb=$this->getPercent(array(5,1));
		$person_month->where(array('member_id'=>$pid,'year'=>$year,'month'=>$month))->setInc('add_hhr1_money',$bfb*30000);
		$person_month->where(array('member_id'=>$pid,'year'=>$year,'month'=>$month))->setInc('hhr1_jmf',30000);
		//$person_month->where(array('member_id'=>$pid,'year'=>$year,'month'=>$month))->save(array('add_hhr1_per'=>$bfb));
		$ppid=$member->where(array('id'=>$pid))->getField('pid');
		if($ppid){
			$this->makeMonth($ppid,$year,$month);
			$person_month->where(array('member_id'=>$ppid,'year'=>$year,'month'=>$month))->setInc('add_hhr2_num',1);
			$bfb=$this->getPercent(array(5,2));
			$person_month->where(array('member_id'=>$ppid,'year'=>$year,'month'=>$month))->setInc('add_hhr2_money',$bfb*30000);
			$person_month->where(array('member_id'=>$ppid,'year'=>$year,'month'=>$month))->setInc('hhr2_jmf',30000);
			//$person_month->where(array('member_id'=>$ppid,'year'=>$year,'month'=>$month))->save(array('add_hhr2_per'=>$bfb));
		}
		$pppid=$member->where(array('id'=>$ppid))->getField('pid');
		if($pppid){
			$this->makeMonth($pppid,$year,$month);
			$person_month->where(array('member_id'=>$pppid,'year'=>$year,'month'=>$month))->setInc('add_hhr3_num',1);
			$bfb=$this->getPercent(array(5,3));
			$person_month->where(array('member_id'=>$pppid,'year'=>$year,'month'=>$month))->setInc('add_hhr3_money',$bfb*30000);
			$person_month->where(array('member_id'=>$pppid,'year'=>$year,'month'=>$month))->setInc('hhr3_jmf',30000);
			//$person_month->where(array('member_id'=>$pppid,'year'=>$year,'month'=>$month))->save(array('add_hhr3_per'=>$bfb));
		}
		//公司部分
		//判断上级是否在默认收益账户3级之内
		$bfb1=$this->getPercent(array(5,1));
		$bfb2=$this->getPercent(array(5,2));
		$bfb3=$this->getPercent(array(5,3));
		if($pid==$this->Public_pid && $pid!=0){
			$hhr_pay=0;
		}elseif($ppid==$this->Public_pid && $ppid!=0){
			$hhr_pay=$bfb1*30000;
		}elseif($pppid==$this->Public_pid  && $pppid!=0){
			$hhr_pay=30000*($bfb1+$bfb2);
		}else{

			$hhr_pay=30000*($bfb1+$bfb2+$bfb3);
		}
		$company_month->where(array('year'=>$year,'month'=>$month))->setInc('add_heji',30000);
		$company_month->where(array('year'=>$year,'month'=>$month))->setInc('hhr_pay',$hhr_pay);
		$company_month->where(array('year'=>$year,'month'=>$month))->setInc('pay_heji',$hhr_pay);
	}
	public function vipLogic($id=10000876,$year=2016,$month=9){
		ini_set("max_execution_time", 0);
		echo $id;
		//exit;
        $m=M('member','vip_');
		$person=M('person_month','vip_');
		$company=M('company_month','vip_');
		   $my=$m->where(array('id'=>$id))->find();
		   $pid=$my['pid'];
		   //个人的数据
		   $parent=$m->where(array('id'=>$pid))->find();
		   $pparent=$m->where(array('id'=>$parent['pid']))->find();
		   $ppparent=$m->where(array('id'=>$pparent['pid']))->find();
		   $pppparent=$m->where(array('id'=>$ppparent['pid']))->find();
		  //先增加公司部分的收益
			//判断数据库里面是否有该月份数据
			$this->makeCompanyMonth($year,$month);
			$company->where(array('year'=>$year,'month'=>$month))->setInc('add_hy_num',1);
			$company->where(array('year'=>$year,'month'=>$month))->setInc('add_hy_money',365);
			$company->where(array('year'=>$year,'month'=>$month))->setInc('add_heji',365);

		   //判断上级是合伙人还是会员
		   if($parent['grade_name']=='合伙人'){
			    $this->makeMonth($parent['id'],$year,$month);
			    $bfb=$this->getPercent(array(4,1));
				$person->where(array('member_id'=>$parent['id'],'year'=>$year,'month'=>$month))->setInc('add_hy1_num',1);
			    $person->where(array('member_id'=>$parent['id'],'year'=>$year,'month'=>$month))->setInc('add_hy1_money',$bfb*365);
			    $person->where(array('member_id'=>$parent['id'],'year'=>$year,'month'=>$month))->setInc('hy1_jmf',365);
			        $old_bfb=$bfb;
			   		$bfb=$this->getPercent(array(11,1));
			    $person->where(array('member_id'=>$parent['id'],'year'=>$year,'month'=>$month))->setInc('hhr_hytd_zhf',$bfb*365);
			   //个人账户余额
			   $this->addMyMoney($parent['id'],array('surplus_money'=>$bfb*365));
			   $this->addMyMoney($parent['id'],array(' Gross_income'=>$bfb*365));
			   if($parent['id']!=10000000 && $parent['id']!=false){
				   $this->saveCompanyData($year,$month,array('hy_pay',$bfb*365+$old_bfb*365));
				   $this->saveCompanyData($year,$month,array('pay_heji',$bfb*365+$old_bfb*365));
			   }
			   $this->makeMonth($pparent['id'],$year,$month);
			   $bfb=$this->getPercent(array(8,1));
			   $person->where(array('member_id'=>$pparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr1_hy_num',1);
			   $person->where(array('member_id'=>$pparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr1_hy_jl',$bfb*365);
			   $person->where(array('member_id'=>$pparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr1_hy_money',365);
			   //个人账户余额
			   $this->addMyMoney($pparent['id'],array('surplus_money'=>$bfb*365));
			   $this->addMyMoney($pparent['id'],array(' Gross_income'=>$bfb*365));
			   if($pparent['id']!=10000000 && $pparent['id']!=false){
				   $this->saveCompanyData($year,$month,array('hy_pay',$bfb*365));
				   $this->saveCompanyData($year,$month,array('pay_heji',$bfb*365));
			   }
			   $this->makeMonth($ppparent['id'],$year,$month);
			   $bfb=$this->getPercent(array(8,2));
			   $person->where(array('member_id'=>$ppparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr2_hy_num',1);
			   $person->where(array('member_id'=>$ppparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr2_hy_jl',$bfb*365);
			   $person->where(array('member_id'=>$ppparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr2_hy_money',365);
			   //个人账户余额
			   $this->addMyMoney($ppparent['id'],array('surplus_money'=>$bfb*365));
			   $this->addMyMoney($ppparent['id'],array(' Gross_income'=>$bfb*365));
			   if($ppparent['id']!=10000000 && $ppparent['id']!=false){
				   $this->saveCompanyData($year,$month,array('hy_pay',$bfb*365));
				   $this->saveCompanyData($year,$month,array('pay_heji',$bfb*365));
			   }
				   $this->makeMonth($pppparent['id'], $year, $month);
			   $bfb=$this->getPercent(array(8,3));
			   $person->where(array('member_id'=>$pppparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr3_hy_num',1);
			   $person->where(array('member_id'=>$pppparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr3_hy_jl',$bfb*365);
			   $person->where(array('member_id'=>$pppparent['id'],'year'=>$year,'month'=>$month))->setInc('sxhhr3_hy_money',365);
			   //个人账户余额
			   $this->addMyMoney($pppparent['id'],array('surplus_money'=>$bfb*365));
			   $this->addMyMoney($pppparent['id'],array(' Gross_income'=>$bfb*365));
			   if($pppparent['id']!=10000000 && $pppparent['id']!=false) {
				   $this->saveCompanyData($year, $month, array('hy_pay', $bfb * 365));
				   $this->saveCompanyData($year, $month, array('pay_heji', $bfb * 365));
			   }
		   }elseif($parent['grade_name']=='会员') {
			   //先处理所有上级合伙人
			   //先找到最近的合伙人,然后往上数3个人增加收益
			   $hhr1_id = $this->searchParent($my['id']);
			   $hhr2_id = $this->searchParent($hhr1_id);
			   $hhr3_id = $this->searchParent($hhr2_id);
			   $hhr4_id = $this->searchParent($hhr3_id);
			   if ($hhr1_id) {
				   //提成直辖会员部分收益
				   $idLength = $this->idLength($hhr1_id, $my['id']);//两者之间的关系层级
				   $this->makeMonth($hhr1_id,$year,$month);
				   //上级是会员，最靠近的合伙人至少差2个等级
//				   if($idLength==1){
//					   $person->where(array('member_id'=>$hhr1_id,'year'=>$year,'month'=>$month))->setInc('sxhhr1_hy_num',1);
//					   $person->where(array('member_id'=>$hhr1_id,'year'=>$year,'month'=>$month))->setInc('sxhhr1_hy_jl',0.38*365);
//					   $person->where(array('member_id'=>$hhr1_id,'year'=>$year,'month'=>$month))->setInc('sxhhr1_hy_money',365);//
//				   }else
				   if ($idLength == 2) {
					   $bfb=$this->getPercent(array(4,2));
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('add_hy2_num', 1);
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('add_hy2_money', $bfb * 365);
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('hy2_jmf', 365);//
					   $bfb2=$this->getPercent(array(11,1));
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('hhr_hytd_zhf', $bfb2 * 365);//
					   //个人账户余额
					   $this->addMyMoney($hhr1_id,array('surplus_money'=>$bfb*365+$bfb2*365));
					   $this->addMyMoney($hhr1_id,array(' Gross_income'=>$bfb*365+$bfb2*365));
					   if($hhr1_id!=10000000 && $hhr1_id!=false) {
						   $this->saveCompanyData($year, $month, array('hy_pay', $bfb * 365+$bfb2*365));
						   $this->saveCompanyData($year, $month, array('pay_heji', $bfb * 365+$bfb2*365));
					   }
				   } elseif ($idLength == 3) {
					   $bfb=$this->getPercent(array(4,3));
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('add_hy3_num', 1);
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('add_hy3_money', $bfb * 365);
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('hy3_jmf', 365);//
					   $bfb2=$this->getPercent(array(11,1));
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('hhr_hytd_zhf', $bfb2 * 365);//
					   //个人账户余额
					   $this->addMyMoney($hhr1_id,array('surplus_money'=>$bfb*365+$bfb2 * 365));
					   $this->addMyMoney($hhr1_id,array(' Gross_income'=>$bfb*365+$bfb2 * 365));
					   if($hhr1_id!=10000000 && $hhr1_id!=false) {
						   $this->saveCompanyData($year, $month, array('hy_pay', $bfb * 365+$bfb2 * 365));
						   $this->saveCompanyData($year, $month, array('pay_heji', $bfb * 365+$bfb2 * 365));
					   }
				   }elseif ($idLength >= 4){
					   $bfb=$this->getPercent(array(11,1));
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('hhr_hytd_zhf', $bfb * 365);
					   $person->where(array('member_id' => $hhr1_id, 'year' => $year, 'month' => $month))->setInc('hhr_hytd_num', 1);
					   //个人账户余额
					   $this->addMyMoney($hhr1_id,array('surplus_money'=>$bfb*365));
					   $this->addMyMoney($hhr1_id,array(' Gross_income'=>$bfb*365));
					   if($hhr1_id!=10000000 && $hhr1_id!=false) {
						   $this->saveCompanyData($year, $month, array('hy_pay', $bfb * 365));
						   $this->saveCompanyData($year, $month, array('pay_heji', $bfb * 365));
					   }

				   }
			   }
			   if ($hhr2_id) {
				   $this->makeMonth($hhr2_id,$year,$month);
				   $bfb=$this->getPercent(array(8,1));
				   $person->where(array('member_id' => $hhr2_id, 'year' => $year, 'month' => $month))->setInc('sxhhr1_hy_num', 1);
				   $person->where(array('member_id' => $hhr2_id, 'year' => $year, 'month' => $month))->setInc('sxhhr1_hy_jl', $bfb * 365);
				   $person->where(array('member_id' => $hhr2_id, 'year' => $year, 'month' => $month))->setInc('sxhhr1_hy_money', 365);//
				   $this->addMyMoney($hhr2_id,array('surplus_money'=>$bfb*365));
				   $this->addMyMoney($hhr2_id,array(' Gross_income'=>$bfb*365));
				   if($hhr2_id!=10000000 && $hhr2_id!=false) {
					   $this->saveCompanyData($year, $month, array('hy_pay', $bfb * 365));
					   $this->saveCompanyData($year, $month, array('pay_heji', $bfb * 365));
				   }
			   }
			   if ($hhr3_id) {
				   $this->makeMonth($hhr3_id,$year,$month);
				   $bfb=$this->getPercent(array(8,2));
				   $person->where(array('member_id' => $hhr3_id, 'year' => $year, 'month' => $month))->setInc('sxhhr2_hy_num', 1);
				   $person->where(array('member_id' => $hhr3_id, 'year' => $year, 'month' => $month))->setInc('sxhhr2_hy_jl', $bfb * 365);
				   $person->where(array('member_id' => $hhr3_id, 'year' => $year, 'month' => $month))->setInc('sxhhr2_hy_money', 365);//
				   $this->addMyMoney($hhr3_id,array('surplus_money'=>$bfb*365));
				   $this->addMyMoney($hhr3_id,array(' Gross_income'=>$bfb*365));
				   if($hhr3_id!=10000000 && $hhr3_id!=false) {
					   $this->saveCompanyData($year, $month, array('hy_pay', $bfb * 365));
					   $this->saveCompanyData($year, $month, array('pay_heji', $bfb * 365));
				   }
			   }

			   if ($hhr4_id) {
				   $this->makeMonth($hhr4_id,$year,$month);
				   $bfb=$this->getPercent(array(8,3));
				   $person->where(array('member_id' => $hhr4_id, 'year' => $year, 'month' => $month))->setInc('sxhhr3_hy_num', 1);
				   $person->where(array('member_id' => $hhr4_id, 'year' => $year, 'month' => $month))->setInc('sxhhr3_hy_jl', $bfb * 365);
				   $person->where(array('member_id' => $hhr4_id, 'year' => $year, 'month' => $month))->setInc('sxhhr3_hy_money', 365);
				   $this->addMyMoney($hhr4_id,array('surplus_money'=>$bfb*365));
				   $this->addMyMoney($hhr4_id,array(' Gross_income'=>$bfb*365));
				   if($hhr4_id!=10000000 && $hhr4_id!=false) {
					   $this->saveCompanyData($year, $month, array('hy_pay', $bfb * 365));
					   $this->saveCompanyData($year, $month, array('pay_heji', $bfb * 365));
				   }
			   }
			   $myParentVip = $this->searchParentVip($my['id']);
			   //上面第一个会员    id是否正确
			   if($this->isVipEnd($parent['id'])){
			   $bfb=$this->getPercent(array(1,1));
			   $this->makeMonth($parent['id'],$year,$month);
			   $person->where(array('member_id' => $parent['id'], 'year' => $year, 'month' => $month))->setInc('add_hy1_num', 1);
			   $person->where(array('member_id' => $parent['id'], 'year' => $year, 'month' => $month))->setInc('hy1_jf', $bfb * 365);
			   $person->where(array('member_id' => $parent['id'], 'year' => $year, 'month' => $month))->setInc('hy1_jmf', 365);
			   $person->where(array('member_id' => $parent['id'], 'year' => $year, 'month' => $month))->setInc('add_jf_currency', $bfb * 365/2);
			   $person->where(array('member_id' => $parent['id'], 'year' => $year, 'month' => $month))->setInc('add_jf_limit', $bfb * 365/2);
			   $this->addMyMoney($parent['id'],array('add_jf_currency'=>$bfb*365/2));
			   $this->addMyMoney($parent['id'],array('add_jf_limit'=>$bfb*365/2));
			   $this->shop_jf($parent['id'],$bfb*365);
			   
			   if($parent['id']!=10000000 && $parent['id']!=false) {
				   $this->saveCompanyData($year,$month,array('hy_jifen_pay',$bfb*365));
				   $this->saveCompanyData($year,$month,array('jifen_heji',$bfb*365));
			   }
			   }
			   //上面第二个会员
			   if ($myParentVip[1] && $this->isVipEnd($myParentVip[1])) {
				   $this->makeMonth($myParentVip[1],$year,$month);
				   $bfb=$this->getPercent(array(1,2));
				   $person->where(array('member_id' => $myParentVip[1], 'year' => $year, 'month' => $month))->setInc('add_hy2_num', 1);
				   $person->where(array('member_id' => $myParentVip[1], 'year' => $year, 'month' => $month))->setInc('hy2_jf', $bfb* 365);
				   $person->where(array('member_id' => $myParentVip[1], 'year' => $year, 'month' => $month))->setInc('hy2_jmf', 365);
				   $person->where(array('member_id' => $myParentVip[1], 'year' => $year, 'month' => $month))->setInc('add_jf_currency', $bfb * 365/2);
			       $person->where(array('member_id' => $myParentVip[1], 'year' => $year, 'month' => $month))->setInc('add_jf_limit', $bfb * 365/2);
				   $this->addMyMoney( $myParentVip[1],array('add_jf_currency'=>$bfb*365/2));
				   $this->addMyMoney( $myParentVip[1],array('add_jf_limit'=>$bfb*365/2));
				   $this->shop_jf($myParentVip[1],$bfb*365);
				   if($myParentVip[1]!=10000000 && $myParentVip[1]!=false) {
					   $this->saveCompanyData($year, $month, array('hy_jifen_pay', $bfb * 365));
					   $this->saveCompanyData($year, $month, array('jifen_heji', $bfb * 365));
				   }
			   }
			   if ($myParentVip[2] && $this->isVipEnd($myParentVip[2])) {
				   //上面第三个会员
				   $this->makeMonth($myParentVip[2],$year,$month);
				   $bfb=$this->getPercent(array(1,3));
				   $person->where(array('member_id' => $myParentVip[2], 'year' => $year, 'month' => $month))->setInc('add_hy3_num', 1);
				   $person->where(array('member_id' => $myParentVip[2], 'year' => $year, 'month' => $month))->setInc('hy3_jf', $bfb * 365);
				   $person->where(array('member_id' => $myParentVip[2], 'year' => $year, 'month' => $month))->setInc('hy3_jmf', 365);
				   $person->where(array('member_id' => $myParentVip[2], 'year' => $year, 'month' => $month))->setInc('add_jf_currency', $bfb * 365/2);
			       $person->where(array('member_id' =>$myParentVip[2], 'year' => $year, 'month' => $month))->setInc('add_jf_limit', $bfb * 365/2);
				   $this->addMyMoney( $myParentVip[2],array('add_jf_currency'=>$bfb*365/2));
				   $this->addMyMoney( $myParentVip[2],array('add_jf_limit'=>$bfb*365/2));
				   $this->shop_jf($myParentVip[2],$bfb*365);
				   if($myParentVip[2]!=10000000 && $myParentVip[2]!=false) {
					   $this->saveCompanyData($year, $month, array('hy_jifen_pay', $bfb * 365));
					   $this->saveCompanyData($year, $month, array('jifen_heji', $bfb * 365));
				   }
			   }

		   }
    }
	private function makeCompanyMonth($year,$month){
		$model=M('company_month','vip_');
		if(!$model->where(array('year'=>$year,'month'=>$month))->count()){
			$model->add(array('year'=>$year,'month'=>$month,'create_time'=>NOW_TIME));
		}
	}
	public function saveCompanyData($year,$month,$data=array()){
		$this->makeCompanyMonth($year,$month);
		$model=M('company_month','vip_');
		$model->where(array('year'=>$year,'month'=>$month))->setInc($data[0],$data[1]);
	}
	/**
	 * @param int $id
	 * @param int $year
	 * @param int $month
	 * 判断用户月份记录是否存在,如果没有就新增一条
	 */
	private function makeMonth($id,$year,$month) {
//	public function makeMonth() {
//		$id=10000000;$year = 2016;$month = 7;
		if ($id) {
			$m = M('person_month','vip_');
			$arr=array('member_id' => $id, 'year' => $year, 'month' => $month,'create_time'=>NOW_TIME);
			for($i=1;$i<=8;++$i){
				for($k=1;$k<=3;++$k){
					$arr['per_'.$i.'_'.$k]=$this->getPercent(array($i,$k));
				}
			}
			$arr['per_9']=$this->getPercent(array(9,1));
			$arr['per_10']=$this->getPercent(array(10,1));
			$arr['per_11']=$this->getPercent(array(11,1));
			for($i=12;$i<=13;++$i){
				for($k=1;$k<=3;++$k){
					$arr['per_'.$i.'_'.$k]=$this->getPercent(array($i,$k));
				}
			}
			if (!$m -> where(array('member_id' => $id, 'year' => $year, 'month' => $month)) -> count()) {
				$m -> add($arr);
        }
    }
	}
	/**
	 * 获取提成比例
	 * @param $a提成比例的坐标array(),或者说明文字
	 * @return mixed
	 */
	public function getPercent($a){
		$model=M('member_proportion','vip_');
		//传入的是汉字说明
		if(is_string($a)){
			if($model->where(array('tcbl_nane'=>$a))->count()){
				return $model->where(array('tcbl_nane'=>$a))->find();
			}else{
				return $model->where(array('proportion_name'=>$a))->find();
			}
		}else{
		if($a[1]==1){
			$level="first";
		}elseif($a[1]==2){
			$level="second";
		}else{
			$level="third";
		}
		return $model->where(array('id'=>$a[0]))->getField($level);
	}
	}
	/**
	 * @param $member_id   用户id
	 * @param array $field  需要增加的字段和增加值的字段 例如:array('jifenA'=>10,'jifenB'=>22)
	 */
	private function addMyMoney($member_id,$field=array()){
		$member_profit=M('member_profit','vip_');
		$member=M('member','vip_');
		if(!$member_profit->where(array('member_id'=>$member_id))->count() && $member->where(array('id'=>$member_id))->count()){
			$member_profit->add(array('member_id'=>$member_id));
			echo $member_profit->getLastSql();
			echo "<br/>";
		}
		foreach($field as $k=>$v){
			$member_profit->where(array('member_id'=>$member_id))->setInc($k,$v);
		}
	}
	/**
	 * 查找上级链中最靠近的合伙人
	 */
	private function searchParent($id){
		$m=M('member','vip_');
		$rst=$m->where(array('id'=>$id))->getField('path');
		$arr=explode('-',$rst);
		array_pop($arr);
		array_pop($arr);
		$arr=array_reverse($arr,false);
		$i=0;
		foreach($arr as $v){
			++$i;
			$temp=$m->where(array('id'=>$v))->getField('grade_name');
			//一定至少存在一个合伙人(默认收益账户)
			if($temp=='合伙人'){
				return $v;
			}
			if($i==count($arr)){
				return 0;
			}
		}
	}

	/**
	 * @param $id  自己的id
	 * @return array  所有上级为会员的id数组
	 */
private function searchParentVip($id){
	$m=M('member','vip_');
	$rst=$m->where(array('id'=>$id))->getField('path');
	$arr=explode('-',$rst);
	array_pop($arr);
	array_pop($arr);
	$arr=array_reverse($arr,false);
	$vipArray=array();
	foreach($arr as $v){
		$temp=$m->where(array('id'=>$v))->getField('grade_name');
		if($temp=='合伙人'){
			return $vipArray;
		}
		$vipArray[]=$v;
	}
	return $vipArray;
}
	private function idLength($id1,$id2){
		$m=M('member','vip_');
		if($id1>$id2){
			$temp=$id2;
			$id2=$id1;
			$id1=$temp;
		}
		$rst=$m->where(array('id'=>$id2))->getField('path');
		$arr=explode('-',$rst);
		$id1_pos=array_search($id1,$arr);
		$id2_pos=array_search($id2,$arr);
		return $id2_pos-$id1_pos;
	}
	/**
	 *
	 */
	private function isVipEnd($member_id){
		$endtime=M('member','vip_')->where(array('id'=>$member_id))->getField('vip_end');
		if($endtime-NOW_TIME>0){
			return 1;
		}else{
			return 0;
		}
	}
	//同步商城那边的积分
	protected function shop_jf($id,$jf){
		$grade_name=M('member','vip_')->where(array('user_id'=>$id))->getField('grade_name');
		$user=M('user');
		$user->where(array('id'=>$id))->setInc('integral',$jf);
		if($grade_name=='合伙人'){
				$user->where(array('id'=>$id))->setInc('add_jf_currency',$jf);
		}elseif($grade_name=='会员'){
			$user->where(array('id'=>$id))->setInc('add_jf_currency',$jf/2);
			$user->where(array('id'=>$id))->setInc('add_jf_limit',$jf/2);
		}
		
	}
}