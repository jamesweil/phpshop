
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
 * 搜索下拉列表 
 */

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
	
	parentObj.nextAll('.drop-main').each(function (){
		$(this).find('input[type="hidden"]').remove();
	})
	
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
	var id = $(this).parents('.drop-main').next('.drop-main').find('.drop input').attr('id');
	var number = $(this).find('input[type="hidden"]').val();
	
	if (!id) {
		return ;
	}
	GoodsOption.getClass($('#'+id).get(0), CLASS_LIST, number);
});