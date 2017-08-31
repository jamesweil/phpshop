$(function(){
	$('#cart').on('mouseenter',function(){
		$('#cart .catr_none').css('display','block');
		$('#cart .catr_block').addClass('active');
	}).on('mouseleave',function(){
		$('#cart .catr_none').css('display','none');
		$('#cart .catr_block').removeClass('active');
	});
	$('#nav_li1').on('mouseenter',function(){
		$(this).addClass('active');
		$('#menu').css('display','block');
	}).on('mouseleave',function(){
		$(this).removeClass('active');
		$('#menu').css('display','none');
	});
	$('#menu li').on('mouseenter',function(){
		$('#menu li').removeClass('active');
		$(this).addClass('active');
		$('#menu li ul').eq($(this).index()).css('display','block');
	}).on('mouseleave',function(){
		$('#menu li ul').css('display','none');
	})
	$('#menu').on('mouseleave',function(){
		$('#option li').css('display','none');
		$('#menu li').removeClass('active');
	});
});