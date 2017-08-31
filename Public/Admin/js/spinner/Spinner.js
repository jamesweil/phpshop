
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
 * 下拉列表
 */
function open(obj,index,_input){
	var Length = obj.parents('.drop-main').children('.menu').children().length;
	if(Length <= 0)return;
	$('.drop-main .menu').removeClass('active');
	$('.drop-main .drop').removeClass('active');
	obj.parents('.drop-wrap').find('.drop-main:eq('+obj.parents('.drop-main').index()+') .menu').toggleClass('active');
	obj.parents('.drop-wrap').find('.drop-main:eq('+obj.parents('.drop-main').index()+') .drop').toggleClass('active');
	
	console.log(obj.parents('.drop-main').index())
	if(_input == 'false'){
		index.html('∧');
		index.attr('data','true');
	}else{
		index.html('∨');
		index.attr('data','false');
	}
}


$('.drop-main .drop input').on('input',function(){
	var _this = $(this);
	var index = $(this).parents('.drop').children('a');
	var _input = 'false';
	if($(this).value != ''){
		$(this).parents('.drop-main').children('.drop a').attr('data','false')
	}else{
		$(this).parents('.drop-main').children('.drop a').attr('data','true')
	}
	open(_this,index,_input);
	if($(this).val() != ''){
		$(this).addClass('active');
	}else{
		$(this).removeClass('active');
	}
	//输入展开
	$('.drop-main .menu').eq($(this).parents('.drop-main').index()).addClass('active');
	$('.drop-main .drop').eq($(this).parents('.drop-main').index()).addClass('active');
});

$('.drop-main .drop a').on('click',function(){
	console.log(this);
	var _this = $(this);
	var _index = $(this);
	var _input = $(this).attr('data');
	open(_this,_index,_input);
});
