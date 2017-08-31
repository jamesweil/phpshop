$(function(){
	//精简筛选打开关闭
	$('.rangeSearch .search .cndo').on('click',function(){
		$('.rangeSearch .more-part').toggle();
	});
	//下拉菜单
	$('.rangeSearch .more-part .more-part-top .type .typeCh').on('click',function(){
		$('.rangeSearch .more-part .more-part-top .type .rc-select-dropdown-menu').addClass('active');
		$('.rangeSearch .more-part .more-part-bottom .type .rc-select-dropdown-menu').removeClass('active');
		$('.rangeSearch .more-part .more-part-bottom .type1 .rc-select-dropdown-menu').removeClass('active');
	});
	$('.rangeSearch .more-part .more-part-top .type .rc-select-dropdown-menu li').on('click',function(){
		$('.rangeSearch .more-part .more-part-top .type .typeCh').html($(this).html());
		$(this).parent().removeClass('active');
		$('.rangeSearch .more-part .more-part-top .type .rc-select-dropdown-menu li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.rangeSearch .more-part .more-part-bottom .type .typeCh').on('click',function(){
		$('.rangeSearch .more-part .more-part-bottom .type .rc-select-dropdown-menu').addClass('active');
		$('.rangeSearch .more-part .more-part-top .type .rc-select-dropdown-menu').removeClass('active');
		$('.rangeSearch .more-part .more-part-bottom .type1 .rc-select-dropdown-menu').removeClass('active');
	});
	$('.rangeSearch .more-part .more-part-bottom .type .rc-select-dropdown-menu li').on('click',function(){
		$('.rangeSearch .more-part .more-part-bottom .type .typeCh').html($(this).html());
		$(this).parent().removeClass('active');
		$('.rangeSearch .more-part .more-part-bottom .type .rc-select-dropdown-menu li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.rangeSearch .more-part .more-part-bottom .type1 .typeCh').on('click',function(){
		$('.rangeSearch .more-part .more-part-bottom .type .rc-select-dropdown-menu').removeClass('active');
		$('.rangeSearch .more-part .more-part-top .type .rc-select-dropdown-menu').removeClass('active');
		$('.rangeSearch .more-part .more-part-bottom .type1 .rc-select-dropdown-menu').addClass('active');
	});
	$('.rangeSearch .more-part .more-part-bottom .type1 .rc-select-dropdown-menu li').on('click',function(){
		$('.rangeSearch .more-part .more-part-bottom .type1 .typeCh').html($(this).html());
		$(this).parent().removeClass('active');
		$('.rangeSearch .more-part .more-part-bottom .type1 .rc-select-dropdown-menu li').removeClass('active').eq($(this).index()).addClass('active');
	});
});