
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
 * 快递公司 
 */
var obj = typeof window !== "undefined" ? window : this;
(function(t){
	
	/**
	 * 设置状态
	 */
	this.isCommon = function(url, id, value,  key) {
		if(!id) {
			return false;
		}
		var json = {};
		value = value == 0 ? 1 : 0;
		json['id'] = id;
		json[key] = value;
		return Tool.ajax(url, json);
	}
	
	this.addExpress = function(id, url) {
		var obj = $('#'+id);
		if (!obj.length) {
			layer.msg('数据错误');
			return false
		}
		var attr = '' ;
		var rule = {};
		message  = {};
		obj.find('input[type="text"]').each(function (index, element) {
			attr = $(element).attr('name');
			rule[attr] = {required:true, specialCharFilter : true};
			message[attr] = {required:'请输入'+$(element).parent().siblings('.col-sm-2').text(), specialCharFilter: '请去掉特殊字符'};
		});
		
		rule.tel.checkTelphone = true;
		
		return t.submitHandle(obj, message, rule, url);
	}
	
	obj.ExpressWQ = this;
	
})(Tool);