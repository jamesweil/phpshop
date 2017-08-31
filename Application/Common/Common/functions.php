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

/**
 * TODO 基础分页的相同代码封装，使前台的代码更少
 * @param $count 要分页的总记录数
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getpage($count, $pagesize = 10) {
	$p = new Think\Page($count, $pagesize);
	$p->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
	$p->setConfig('prev', '上一页');
	$p->setConfig('next', '下一页');
	$p->setConfig('last', '末页');
	$p->setConfig('first', '首页');
	$p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
	$p->lastSuffix = false;//最后一页不显示为总页数
	return $p;
}

function substr_cut($user_name){//只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
	$strlen = mb_strlen($user_name, 'utf-8');
	$firstStr = mb_substr($user_name, 0, 1, 'utf-8');
	$lastStr = mb_substr($user_name, -1, 1, 'utf-8');
	return $strlen == 3 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 3) . $lastStr;
}

function send_newsms($mobile,$content){//短信验证码
	header("Content-Type: text/html; charset=UTF-8");
	$flag = 0;
	$params='';//要post的数据
	//$verify = rand(100000, 999999);//获取随机验证码
	//以下信息自己填以下
	//$mobile='';//手机号
	$argv = array(
			'name'=>'dxwzzy',     //必填参数。用户账号
			'pwd'=>'C51E7F585764DAECF71FF62520E3',     //必填参数。（web平台：基本资料中的接口密码）
			'content'=>$content,
			//'content'=>'掌中游短信验证码为：'.$verify.'，请勿将验证码提供给他人。',   //必填参数。发送内容（1-500 个汉字）UTF-8编码
			'mobile'=>$mobile,   //必填参数。手机号码。多个以英文逗号隔开
			'stime'=>'',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
			'sign'=>'dxwzzy',    //必填参数。用户签名。
			'type'=>'pt',  //必填参数。固定值 pt
			'extno'=>''    //可选参数，扩展码，用户定义扩展码，只能为数字
	);
	//print_r($argv);exit;
	//构造要post的字符串
	//echo $argv['content'];
	foreach ($argv as $key=>$value) {
		if ($flag!=0) {
			$params .= "&";
			$flag = 1;
		}
		$params.= $key."="; $params.= urlencode($value);// urlencode($value);
		$flag = 1;
	}
	$url = "http://web.duanxinwang.cc/asmx/smsservice.aspx?".$params; //提交的url地址
	$con= substr( file_get_contents($url), 0, 1 );  //获取信息发送后的状态
	if($con == '0'){
		//return true;
		echo "<script>alert('发送成功!');</script>";
	}else{
		//return false;
		echo "<script>alert('发送失败!');history.back();</script>";
	}
}
