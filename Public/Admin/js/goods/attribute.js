
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
 * 弹出层js
 */
function selectEdit() {
	/**
	 * 添加移除 select
	 */
	this.optionSelect = function(identification) {
		var value = $(identification + ' option:selected').val();
		var handler = $('#attr');
		if (value == 0) {
			handler.find('select[name="p_id"]').attr('disabled', 'disabled');
			handler.hide();
		} else {
			handler.find('select[name="p_id"]').attr('disabled', false);
			handler.show();
		}
	}
	
	this.addAttribute = function (id, url) {
		
		var rules = {};
		
		var message = {};
		
		var obj = $('#'+id);
		
		var name="";
		$('#'+id).find('.checkValue').each(function(index, element) {
			name = $(element).attr('name');
			rules[name]= {specialCharFilter : true};
			message[name] = {specialCharFilter  : "不要填写特殊字符"};
			
			if ($(element).attr('isMust') == 1) {
				rules[name].required = true;
				message[name].required ='请您填写信息';
			} 
		});
		
		return this.submitHandle(obj, message, rules, url);
	}
	
	this.recommend = function (obj) {
		
		var json = {};
		
		var htmlElement = obj.parentNode.parentNode.children;
		
		var oneElement, falg;
		obj.setAttribute('url', URL_PDU);
		for (var i= 0; i < htmlElement.length; i ++) {
			oneElement = htmlElement[i];
			flag = oneElement.getAttribute('data-id');
			if ( !flag) {
				continue;
			}
			json[oneElement.getAttribute('data-name')] = flag;
		}
		console.log(json);
		return this.ajax(obj.getAttribute('url'), json);
	}
	
	this.updateSort = function (obj)
	{
		var json = {};
		
		json[obj.getAttribute('data-name')] = obj.getAttribute('data-value');
		json[obj.name] = obj.value;
		
		return this.ajax(obj.getAttribute('url'), json);
	}
	
	
	this.deleteData = function (obj) {
		var self = this;
		this.deleteDbData (function () {
			
			var url    = obj.getAttribute('url');
			var attrId = obj.getAttribute('data-id');
			
			self.ajax(url, {id : attrId}, function (res) {
				layer.msg(res.message);
				if (res.status === 1) {
					var id = setInterval (function () {
						 location.reload();
					}, 2000);
					
					setInterval (function () {
						clearInterval(id);
					}, 2000);
				}
			});
			
		});
	}
	
}

// ajax 抓取页面 form 为表单id  page 为当前第几页

(function() {

	selectEdit.prototype = Tool;
	window.selectTool = new selectEdit();
	return window.selectTool;
})(window)