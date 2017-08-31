
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
 * 会员相关js
 */
window.onload = function() {
	
	(function(){
		
		function User() {
			
			this.isCheckPwd = false;												
			
			this.addUserLevel = function (id, url) {
				
				var length = document.getElementById(id);
				
				if(typeof length == 'undefined') {
					layer.msg('未知错误');
					return false;
				}
				
				var form = $('#'+id).formToArray();
				
				var flag = 0;
				
				for( var i in form) {
					if(!form[i].value) {
						layer.msg('数据错误');
						return false;
					} else {
						flag ++ ;
					}
				}
				
				if (flag === 0) {
					layer.msg('数据错误');
					return false;
				}
				
				return this.ajax(url, form, function (res) {
					if (res.hasOwnProperty('status') && res.status == '1') {
						layer.msg(res.message);
						return Tool.closeWindow();
					} 
					return layer.msg(res.message);
				});
			}
			
			
			
			/**
			 * 添加会员
			 */
			this.addUser = function(formId, url) {
				var obj = $('#' + formId);
				
				var rule = {};
				var phone = $('#mobile').attr('name');
				var pwd = $('#pwsK').attr('name');
				var message = {};
				
				rule[phone] = {required:true, checkMobile : true};
				
				message[phone] = {required:'请输入数据', checkMobile:'手机号码格式不正确'};
				
				$('.validate').each(function () {//特殊字符验证
					rule[$(this).attr('name')] = {required: true, specialCharFilter:true};
					message[$(this).attr('name')] = {required:'请输入数据',specialCharFilter :'不允许出现特殊字符'};
				})
				
				rule[pwd] = {required:true, pwdIsTrue : true};
				message[pwd] = {required:'请输入数据'};
				return obj.validate({
					rules : rule,
					messages : message,
					submitHandler : function() {
						var data = obj.formToArray();
						return Tool.ajax(url, data, function(res) {
							Tool.notice(res);
						});
					}
				})
			}
			
			
			/**
			 * 删除用户等级 
			 */
			this.deleteLevel = function (url, id) {
				
				var isExe = this.isNumer(id);
				
				if(!isExe) {
					layer.msg('恶意攻击，将负法律责任');
					
					return false;
				}
				
				this.ajax(url, {id:id}, function(res){
					if (res.hasOwnProperty('status') && res.status == '1') {
						 layer.msg(res.message);
						 return UserLevel.closeWindow();
					} 
					
					return layer.msg(res.message);
				});
			}
		}
		
		User.prototype = Tool;
		window.UserLevel = new User();
		
		return window.UserLevel;
	})(window);
}
