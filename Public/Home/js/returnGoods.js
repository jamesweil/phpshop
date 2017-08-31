$(function(){
	
	//退款声明实时输入
	var oB = parseInt($('.layout-main .apply-main .panel-entry .realTime b').html());
	$('.layout-main .apply-main .panel-entry textarea').on('input',function(){
		var iNow = $(this).val().length;
		if(iNow >= oB){
			$('.layout-main .apply-main .panel-entry.active .realTime').html('输入以超过上限');
			$('.layout-main .apply-main .panel-entry.active .realTime').css('color','red');
		}else{
			$('.layout-main .apply-main .panel-entry.active .realTime').html('还可以输入<b>2</b>字');
			$('.layout-main .apply-main .panel-entry.active .realTime b').html(oB - iNow);
			$('.layout-main .apply-main .panel-entry.active .realTime').css('color','#999');
		}
	});
	//退货方式选择
	$('.layout-main .column-main a').eq(0).on('click',function(){
		$('.layout-main .column-main').addClass('active');
		$('.layout-main .apply-main').addClass('active');
		$('.layout-main .apply-main .panel-entry').removeClass('active').eq($(this).index()).addClass('active');
		$('.layout-main .apply-main .fn-tab li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.layout-main .column-main a').eq(1).on('click',function(){
		$('.layout-main .column-main').addClass('active');
		$('.layout-main .apply-main').addClass('active');
		$('.layout-main .apply-main .panel-entry').removeClass('active').eq($(this).index()).addClass('active');
		$('.layout-main .apply-main .fn-tab li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.layout-main .apply-main .fn-tab li').on('click',function(){
		$('.layout-main .apply-main .fn-tab li').removeClass('active').eq($(this).index()).addClass('active');
		$('.layout-main .apply-main .panel-entry').removeClass('active').eq($(this).index()).addClass('active');
	});
	//导航隐藏层弹出
	$('.public-header1 .center-parent .nav li').on('mouseenter',function(){
		$('.public-header1 .center-parent .nav li').eq($(this).index()).addClass('active');
	}).on('mouseleave',function(){
		$('.public-header1 .center-parent .nav li').removeClass('active');
	});
});