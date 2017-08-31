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
 * 将模型的错误信息转换成一个有序列表
 * @param \Think\Model $model
 */
function get_error(\Think\Model $model){
	$errors = $model->getError();
	if(!is_array($errors)){
		$errors = [$errors];
	}
	$html = '<ol>';
	foreach($errors as $error){
		$html .= '<li>'.$error.'</li>';
	}
	$html .='</ol>';
	return $html;

}
/**
 * @param $arr
 * @param $key_name
 * @param $key_name2
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 数组指定列为元素 的一个数组
 */
function get_id_val($arr, $key_name,$key_name2)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val[$key_name2];
	}
	return $arr2;
}


/**
 * 加密
 * @param $password
 * @param $salt
 * @return string
 */
function salt_mcrypt($password,$salt){
	return md5(md5($password).$salt);
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
			'sign'=>'【掌中宝】',    //必填参数。用户签名。
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
	echo $url;
	
	
	$con= substr( file_get_contents($url), 0, 1 );  //获取信息发送后的状态
	if($con == '0'){
		//return true;
		echo "<script>alert('发送成功!');</script>";
	}else{
		//return false;
		echo "<script>alert('发送失败!');history.back();</script>";
	}
}


//截取字符串无乱码
	function utf8sub($str,$len) {
		if($len <= 0) {
			return '';
		}
		$length = strlen($str); //待截取的字符串字节数
		// 先取字符串的第一个字节,substr是按字节来的
		$offset = 0; // 这是截取高位字节时的偏移量
		$chars = 0; // 这是截取到的字符数
		$res = ''; // 这是截取的字符串
		while($chars < $len && $offset < $length) { //只要还没有截取到$len的长度,就继续进行
			$high = decbin(ord(substr($str,$offset,1))); // 重要突破,已经能够判断高位字节
			if(strlen($high) < 8) {
				// 截取1个字节
				$count = 1;
			} else if(substr($high,0,3) == '110') {
				// 截取2个字节
				$count = 2;
			} else if(substr($high,0,4) == '1110') {
				// 截取3个字节
				$count = 3;
			} else if(substr($high,0,5) == '11110') {
				// 截取4个字节
				$count = 4;
			} else if(substr($high,0,6) == '111110') {
				// 截取5个字节
				$count = 5;
			} else if(substr($high,0,7) == '1111110') {
				// 截取6个字节
				$count = 6;
			}
			$res .= substr($str,$offset,$count);
			$chars += 1;
			$offset += $count;
		}
		return $res;
	}
	
	function  log_result($file,$word)
	{
		$fp = fopen($file,"a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"执行日期：".strftime("%Y-%m-%d-%H：%M：%S",time())."\n".$word."\n\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/**
 * 根据传入的name和rows一个下拉列表的html
 * @param $name    表单元素的名字
 * @param $rows    下拉列表中需要的数据
 */
function arr2select($name,$rows,$defaultValue,$fieldValue='id',$fieldName='name'){
$html = "<select name='{$name}' class='{$name}'>
            <option value=''>--请选择--</option>";
foreach($rows as $row){
//根据默认值比对每一行,从而生成selected='selected',然后在option中使用.
$selected  = '';
if($row[$fieldValue]==$defaultValue){
$selected = "selected='selected'";
}
$html.="<option value='{$row[$fieldValue]}' {$selected}>{$row[$fieldName]}</option>";
}
$html.="</select>";
echo $html;
}

/**
 * 多个数组的笛卡尔积
 *
 * @param unknown_type $data
 */
function combineDika() {
	$data = func_get_args();
	$data = current($data);
	$cnt = count($data);
	$result = array();
	$arr1 = array_shift($data);
	foreach($arr1 as $key=>$item)
	{
		$result[] = array($item);
	}

	foreach($data as $key=>$item)
	{
		$result = combineArray($result,$item);
	}
	return $result;
}

/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
 */
function combineArray($arr1,$arr2) {
	$result = array();
	foreach ($arr1 as $item1)
	{
		foreach ($arr2 as $item2)
		{
			$temp = $item1;
			$temp[] = $item2;
			$result[] = $temp;
		}
	}
	return $result;
}


?>
