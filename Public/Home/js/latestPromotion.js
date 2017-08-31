$(function(){
	//广告图关闭
	$('.adt .delete').on('click',function(){
		$('.lates-centent .adt').remove();
	});
	//内容选择
	$('#selected1 .title ul li').on('mouseenter',function(){
		$('#selected1 .title ul li').removeClass('hover').eq($(this).index()).addClass('hover');
		$('#selected1 .con-mian').removeClass('block').eq($(this).index()).addClass('block');
	});
	$('#selected2 .title ul li').on('mouseenter',function(){
		$('#selected2 .title ul li').removeClass('hover').eq($(this).index()).addClass('hover');
		$('#selected2 .con-mian').removeClass('block').eq($(this).index()).addClass('block');
	});
	//内容效果
	$('.promotion .two .con').on('mouseenter',function(){
		$('.promotion .two .conFr').eq($(this).index()).stop().animate({right:30},500)
	}).on('mouseleave',function(){
		$('.promotion .two .conFr').eq($(this).index()).stop().animate({right:10},500)
	});
})