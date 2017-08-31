
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>
// +----------------------------------------------------------------------
(function(){
	
	this.search = function () {
		
	}
	
})(Tool);

function open(obj,index,_input){
	var Length = obj.parents('.drop-main').children('.menu').children().length;
	if(Length <= 0)return;
	$('.drop-main .menu').eq(obj.parents('.drop-main').index()).toggleClass('active');
	$('.drop-main .drop').eq(obj.parents('.drop-main').index()).toggleClass('active');
	if(_input == 'false'){
		index.html('∧');
		index.attr('data','true');
	}else{
		index.html('∨');
		index.attr('data','false');
	}
}
$('.drop-main .drop a').on('click',function(){
	var _this = $(this);
	var _index = $(this);
	var _input = $(this).attr('data');
	open(_this,_index,_input);
});

$('.drop-main .menu').on('mouseenter','li',function(){
	$(this).addClass('hover');
}).on('mouseleave','li',function(){
	$(this).removeClass('hover');
}).on('click','li',function(){
	var obj = $(this).parents('.drop-main').find('input[type="text"]');
	
	var parentObj = $(this).parents('.drop-main');
	
	obj.val($(this).text());
	var parent = parentObj.find('.drop');
	var txt = $(this).html().replace($(this).text(), '');
	
	parent.each(function (){
		$(this).find('input[type="hidden"]').remove();
	})
	console.log(txt);
	parent.append(txt);
	
	parent.find('input[type="hidden"]').attr('disabled', false);
	
	$(this).parent().toggleClass('active');
	
	$('.drop-main .drop').eq($(this).parents('.drop-main').index()).toggleClass('active');
	
	$('.drop-main .drop a').eq($(this).parents('.drop-main').index()).html('∨');
	
	$('.drop-main .drop a').eq($(this).parents('.drop-main').index()).attr('data','false');
	if($('.drop-main .drop input').eq($(this).parents('.drop-main').index()).val() != ''){
		$('.drop-main .drop input').eq($(this).parents('.drop-main').index()).addClass('active');
	}else{
		$('.drop-main .drop input').eq($(this).parents('.drop-main').index()).removeClass('active');
	}
});


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