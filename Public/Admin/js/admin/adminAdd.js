
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
 * 添加管理员
 */
$(function() {
	$('#account').keyup(function() {
		var account = $('#account').val();
		if (account.length >= 5) {
			$.get(CHECK_USER_EXIT, {
				"account" : account
			}, function(data) {
				if (data == 1) {
					$('#account_trips').html(' × 账号已存在');
					$('#account_trips').css('color', 'red');
					$('#account_hidden').val(1);
				} else {
					$('#account_trips').html(' √ 账号可用');
					$('#account_trips').css('color', 'blue');
					$('#account_hidden').val(0);
				}
			}, "json");
		}
	});
});

function admin_add() {
	var account = $('#account').val();
	var password = $('#password').val();
	var password2 = $('#password2').val();
	var group_id = $('#group_id').val();
	var account_hidden = $('#account_hidden').val();
	if (group_id == '') {
		layer.tips('请选择用户组', '#group_id');
		return false;
	}
	if (account == '') {
		layer.tips('请输入账号', '#account');
		return false;
	}
	if (password == '') {
		layer.tips('请输入密码', '#password');
		return false;
	}
	if (password2 == '') {
		layer.tips('请输入新密码', '#password2');
		return false;
	}
	if (password != password2) {
		layer.msg('两次密码必须一样');
		return false;
	}
	if (account_hidden == 1) {
		layer.msg('账号重复，请重新输入');
		return false;
	}
	$.post(ADMIN_ADD_USER, {
		"account" : account,
		"password" : password,
		"group_id" : group_id
	}, function(res) {
		console.log(res);
		if (res.status == 1) {
			layer.msg('添加成功，正在跳转中...', {
				icon : 1,
				time : 2000,
				shade : 0.5
			}, function() {
				window.location.reload(); // 刷新父页面
			});
		}  else {
			layer.msg('添加失败，请重新输入', {
				icon : 2,
				time : 2000
			}, function() {
				window.location.reload();
			});
		}
	}, "json");
}

config_save_mysql=function()
{
	var obj = $("#my_form").serializeArray();
	var data = {};
	$.each(obj,function(){
		data[this.name] = this.value;
	})
	$.post(ADMIN_ADD_USER,data,function(e){
		if(e.status == '1'){
			layer.msg('修改成功');
			parent.location.reload();
		}else if(e.status == '0'){
			layer.msg(e.data,'更新出错');
		}
	})

}

