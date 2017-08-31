$(function(){
	//输入框提示文字输入关闭
	$('.sini-section .login-box-warp .passwordS.one input').on('focus',function(){
		$('.sini-section .login-box-warp .passwordS.one span').eq($(this).parent().index()).css('display','none');
	}).on('blur',function(){
		if($(this).val() == ''){
			$('.sini-section .login-box-warp .passwordS.one span').eq($(this).parent().index()).css('display','block');
		}else{
			$('.sini-section .login-box-warp .passwordS.one span').eq($(this).parent().index()).css('display','none');
		}
	});
	$('.sini-section .login-box-warp .passwordS.one input').eq(0).on('input',function(){
		if($(this).val().length >= 1){
			$('.sini-section .login-box-warp .passwordS.one i').css('display','block');
		}else{
			$('.sini-section .login-box-warp .passwordS.one i').css('display','none');
		}
	});
	$('.sini-section .login-box-warp .passwordS.one i').on('click',function(){
		$('.sini-section .login-box-warp .passwordS.one input').eq($(this).parent().index()).val('');
		$(this).css('display','none');
		$('.sini-section .login-box-warp .passwordS.one span').eq($(this).parent().index()).css('display','block');
	});
	$('.sini-section .login-box-warp .passwordS.one span').on('click',function(){
		$('.sini-section .login-box-warp .passwordS.one input').eq($(this).parent().index()).focus();
	});
	$('.sini-section .login-box-warp .passwordS.active span').on('click',function(){
		$('.sini-section .login-box-warp .passwordS.active input').eq($(this).parent().index()).focus();
	});
	$('.sini-section .login-box-warp .passwordS.active input').on('focus',function(){
		$('.sini-section .login-box-warp .passwordS.active span').eq($(this).parent().index()).css('display','none');
	}).on('blur',function(){
		if($(this).val() == ''){
			$('.sini-section .login-box-warp .passwordS.active span').eq($(this).parent().index()).css('display','block');
		}else{
			$('.sini-section .login-box-warp .passwordS.active span').eq($(this).parent().index()).css('display','none');
		}
	});
	//登录方式切换
	$('.sini-section .login-box-warp .loginOptions span').on('click',function(){
		$('.sini-section .login-box-warp .signInParent .passwordS').removeClass('hover').eq($(this).index()).addClass('hover');
		$('.sini-section .login-box-warp .loginOptions span').removeClass('active').eq($(this).index()).addClass('active');
		$('.sini-section .login-box-warp .loginOptions em').animate({left:$(this).index()*$('.sini-section .login-box-warp .loginOptions em').outerWidth()},300)
	});
	//扫码登录切换
	$('#signMode').on('click',function(){
		$('.login-box-warp.active').css('display','none');
		$('.login-box-warp.scanCode').css('display','block');
	});
	$('#signMode1').on('click',function(){
		$('.login-box-warp.active').css('display','block');
		$('.login-box-warp.scanCode').css('display','none');
	});
});