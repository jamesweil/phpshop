$(function(){
	$('.join-centent .join-cententFl dd').on('click',function(){
		$('.join-centent .join-cententFl dd').removeClass('active').eq($(this).index()-1).addClass('active');
		$('.join-centent .join-cententFr .join-cententFr-child').removeClass('active').eq($(this).index()-1).addClass('active');
	})
});