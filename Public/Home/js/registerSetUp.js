$(function(){
	//验证码
	//var N = 60;
	//var timer = null;
	//var bFlag = false;
	//$('#V-btn').on('click',function(){
	//	var tel = $('#tel').val();
	//	var _this = $(this);
	//
	//	if(!/^1[34578]\d{9}$/.test(tel)){
	//  		layer.tips('请输入正确的手机号', '#tel');
	//  		bFlag = false;
	//		return false;
	//	}
	//	$.post("/index.php/Home/Public/mobile_check", {'tel':tel}, function (a) {
	//		if (a==1) {
     //           layer.tips('该手机号已注册!', '#tel');
	//			return false;
	//		}else{
	//			if(bFlag == true)return;
	//			bFlag = true;
	//			//发送验证码的接口
	//			var url = "/index.php/Home/ApiPhone/reg";
	//			var data = {tel: $('#tel').val()};
	//			$.post(url, data, function (response) {
	//				console.log(response);
	//			});
	//			_this.addClass('hover');
	//			timer = setInterval(function(){
	//				N--;
	//				if(N <= 0){
	//					N = 60;
	//					clearInterval(timer);
	//					_this.html('重新获取验证码');
	//					_this.removeClass('hover');
	//					bFlag = false;
    //
	//				}else{
	//					_this.html(N+'秒后重试');
	//				}
    //
	//			},1000);
	//		}
	//
	//	});
	//});



	//检测值并存值
	$('#user-btn').on('click',function(){
		var email = $('#email').val();
		var password = $('#password').val();
		var password1 = $('#password1').val();
		var user_name = $('#user_name').val();
		var tel = $('#tel').val();
		var code = $('#old_code').val();
		var get_php_code=$('#get_php_code').val();
		if(user_name == ''){
			layer.msg('请填写会员名');
			return false;
		}
		//if(email == ''){
		//	layer.msg('请填写邮箱');
		//	return false;
		//}
		//邮箱选填
		if(!/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/.test(email) && email!=''){
			layer.msg('请填写正确邮箱');
			return false;
		}
		if(password == ''){
			layer.msg('请填写密码');
			return false;
		}
		if(!/^(\w){6,20}$/.test(password)){
			layer.tips('密码格式不对!', '#password');
			return false;
		}
		if(password1 == ''){
			layer.msg('请再次填写密码');
			return false;
		}
		if(password!=password1){
			layer.msg('两次输的密码不一致');
			return false;
		}
		var code_num=$('#old_code').val();
		if(code_num==''){
			layer.msg('请输入验证码');
			return false;
		}else{
			$.ajax({
				type:"POST",
				url:"check_php_code",
				data:"code="+code_num,
				success:function(msg){
					if(msg['msg']!=1){
						layer.msg('验证码输入有误');
					}
				}
			})
		}

		var mobile_check=$("input[name=mobile]").val();
		//在未开启登录功能,手机号选填
		//开启的时候
		if($('input[name=regester_type]').val()==1 && $('input[name=regester_type]').length!=0) {

			if(mobile_check==''){
				layer.msg('请输入手机号');
				return false;
			}else{
				if(!/^1[34578]\d{9}$/.test(mobile_check)){
					layer.msg('请输入正确的手机号');
					return false;
				}
			}
		}else{
			//未开启的情况手机号选填
			if(!/^1[34578]\d{9}$/.test(mobile_check) && mobile_check!='')
			{
				layer.msg('请输入正确的手机号');
				return false;
			}
		}
			console.log($('input[name=regester_type]').val());
		if($('input[name=regester_type]').val()==1 && $('input[name=regester_type]').length!=0) {
			//var mobile_code = $("#code").val();
			//if (mobile_code == '') {
			//	layer.msg('请填写手机验证码');
			//	return false;
			//} else {
			//	if (mobile_code != $('input[name=get_code]').val()) {
			//		layer.msg('验证码不正确,请重试');
			//		return false;
			//	}
			//}
			var mobile_code = $("#code").val();
			if (mobile_code == '') {
				layer.msg('请填写手机验证码');
				return false;
			}
			$.ajax({
				type: "POST",
				url: "check_tel_codestatus",
				data: 'code=' + $('#code').val() + '&print_code=' + $('input[name=get_code]').val(),
				success: function (data) {
					if (data['status'] == 1) {
					}
					else if (data['status'] == 0) {
						layer.msg('该手机已注册');
						return false;
					}
				}
			})
		}


		$.ajax({
						type: "POST",
						url: "add_user_info",
						data: 'user_name=' + user_name + '&email=' + email + '&password=' + password1 + '&mobile=' + mobile_check,
						success: function (data) {
							//注册添加用户信息
							//add 31 添加成功  20注册失败
							//mobile 2 特殊原因被删除的客户 1已注册（@为1时为重复判断，以防出错）
							// user_name 1已经存在的用户
							if (data['add_status']['user_name'] == 1)
								layer.msg('该用户名已存在');
							else if (data['add_status']['mobile'] == 1)
								layer.msg('该手机号已注册111');
							else if (data['add_status']['mobile'] == 2)
								layer.msg('该手机号可能存在特殊问题,请联系客服');
							else if (data['add_status']['add'] == 31) {
								layer.msg('注册成功,请登录');
								window.location.href = 'login';
							}
							else if (data['add_status']['add'] == 20)
								layer.msg('注册失败,请重试');
						}
					})





			//if($(this).val()!=$('input[name=get_code]').val() || $(this).val()=='') {
			//	layer.msg('验证码不正确,请重试');
			//	return false;
			//}
		//$.post('/index.php/Home/Public/reg_person',{'email':email,"user_name":user_name},function(data){
		//	if(data.code == 1){
		//		$('.regiup-dataColumn li').removeClass('active').eq(2).addClass('active');
		//		$('.regiup-dataColumn li em').eq(2).addClass('active');
		//		$('.regiup-form-main').removeClass('active').eq(2).addClass('active');
		//	}else if(data.code == 2){
		//		layer.tips(data.mes, '#email');
		//		return false;
		//	}else if(data.code == 4){
		//		layer.tips(data.mes, '#email');
		//		return false;
		//	}else if(data.code == 3){
		//		layer.tips(data.mes, '#email');
		//		return false;
		//	}else{
		//		layer.msg('未知错误!',{icon: 2,time: 2000},function(){
		//			return false;
		//		});
		//	}
		//},"json")
		//if(tel == ''){
        //layer.tips('请填写手机号', '#tel');
        //return false;
        //}
        //if(!/^1[34578]\d{9}$/.test(tel)){
	  	//	layer.tips('请输入正确的手机号', '#tel');
			//return false;
        //}

	    //$.post('/index.php/Home/Public/reg_account',{'mobile':tel,'rel_code':code},function(data){
         //   if(data.code == 1){
			//	var tel = data.mes;
			//	$('.regiup-form-main .container .one .mobile').html(tel);
			//	$('.regiup-dataColumn li').removeClass('active').eq(1).addClass('active');
			//	$('.regiup-dataColumn li em').eq(1).addClass('active');
			//	$('.regiup-form-main').removeClass('active').eq(1).addClass('active');    //刷新父页面
			//}else if(data.code == 2){
			//
			//	layer.tips(data.mes, '#tel');
			//	return false;
			//}else if(data.code == 3){
			//
			//	layer.tips(data.mes, '#tel');
			//    return false;
			//}else if(data.code == 4){
			//
			//	layer.tips(data.mes, '#code');
		 //       return false;
			//}else{
			//	layer.msg('未知错误!',{icon: 2,time: 2000},function(){
			//		return false;
			//	});
			//}
	    //},"json")
	});
	//$('#aout-btn').on('click',function(){
    //
	//});
	//$('#red-btn').on('click',function(){
	//	var tel = $('#tel').val();
	//    var email = $('#email').val();
	//    var password = $('#password').val();
	//    var user_name = $('#user_name').val();
	//    var recommendcode = $('#recommendcode').val();
	//    $.post('/index.php/Home/Public/reg_complete',{'mobile':tel,'email':email,'password':password,'user_name':user_name,'recommendcode':recommendcode},function(data){
     //       if(data.code == 1){
     //           $('.regiup-form-main .container-ok .bottom').html('<span>登录名 ：<b>'+data.mobile+'</b></span><span>商城会员名：<b>'+data.user_name+'</b></span>');
	//			$('.regiup-dataColumn li').removeClass('active').eq(3).addClass('active');
	//			$('.regiup-dataColumn li em').eq(3).addClass('active');
	//			$('.regiup-form-main').removeClass('active').eq(3).addClass('active');
	//		}else if(data.code == 2){
	//			layer.msg(data.mes,{icon: 2,time: 2000},function(){
	//				window.location.reload();
	//			});
	//		}else if(data.code == 3){
	//			layer.msg(data.mes,{icon: 2,time: 2000},function(){
	//				window.location.reload();
	//			});
	//		}else if(data.code == 4){
	//			layer.msg(data.mes,{icon: 2,time: 2000});
	//			return false;
	//		}else{
	//			layer.msg(data.mes,{icon: 2,time: 2000},function(){
	//				window.location.reload();
	//			});
	//		}
	//    },"json")
	//});
	//输入框
	//$('.regiup-form-main.two input').on('blur',function(){
	//	if($(this).val() == ''){
	//		$('.container .one em').eq($(this).parent().parent().index()-1).css('display','block');
	//	}else{
	//		$('.container .one em').eq($(this).parent().parent().index()-1).css('display','none');
	//	}
	//}).on('focus',function(){
	//	$('.container .one em').eq($(this).parent().parent().index()-1).css('display','none');
	//});
	//$('.regiup-form-main.two em').on('click',function(){
	//	$('.regiup-form-main.two input').eq($(this).parent().parent().index()-1).focus();
	//});
    //
    //
	//$('.regiup-form-main.three input').eq(0).on('blur',function(){
	//	if($(this).val() == ''){
	//		$('.container .one em').eq($(this).parent().parent().index()-1).css('display','block');
	//	}else{
	//		$('.container .one em').eq($(this).parent().parent().index()-1).css('display','none');
	//	}
	//}).on('focus',function(){
	//	$('.container .one em').eq($(this).parent().parent().index()-1).css('display','none');
	//});
	//$('.regiup-form-main.three em').on('click',function(){
	//	$('.regiup-form-main.three input').eq($(this).parent().parent().index()).focus();
	//});
})