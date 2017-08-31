
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
 * 地区 js
 */
(function() {

	function Region() {

		this.areaListUrl;
		
		this.currentId = 0;
		
		this.setareaListUrl = function(areaListUrl) {
			this.areaListUrl = areaListUrl;
		}
		/**
		 * 添加发货地区
		 */
		this.addSendAddress = function(formId, url) {

			var obj = $('#' + formId);

			var rule = {};
			var addRule = {};
			var message = {};

			obj.find('.req').each(function() {

				if ($(this).attr('isNumber') == 1) {
					addRule = {
						required : true,
						number : true
					};
					rule[$(this).attr('name')] = addRule;
					message[$(this).attr('name')] = "请选择详细地址";
				} else {
					rule[$(this).attr('name')] = 'required';
					message[$(this).attr('name')] = "请输入信息信息";
				}

			});
			return obj.validate({
				rules : rule,
				messages : message,
				submitHandler : function() {
					var data = obj.formToArray();
					return Tool.ajax(url, data, function(res) {
						var data = res.data;
						layer.msg(res.message);
						if (data.hasOwnProperty('url')) {
							setInterval(function() {
								location.href = data.url;
							}, 3000);
						}
					});
				}
			});

		}

		/**
		 * 切换数据
		 */
		this.changeSelectTab = function(obj, id) {
			if (!(obj instanceof Object)) {
				return false;
			}
			
			var json = {};
			
			var areaId = obj.value;

			var key = obj.getAttribute('area-key');
			
			json[key] = areaId;
			
			console.log(json);
			
			$(obj).parents('.col-xs-3').nextAll('.col-xs-3').find('select').each (function () {
				
				$(this).html('');
				
				$(this).html('<option value="">请选择分类</option>');
			})
			var self = this;
			var nextObj = document.getElementById(id);
			return this.ajax(this.areaListUrl, json, function(res) {
				self.parseData(res, nextObj, self);
			});
			
			
		}

		/**
		 * 获取分类数据
		 * @param Object obj 当前切换对象
		 * @param string id 下一个 选择框标志 
		 */
		this.getAreaListById = function(obj) {

			if (!(obj instanceof Object)) {
				return false;
			}
			
			var areaId = obj.getAttribute('area-id');

			var key = obj.getAttribute('area-key');

			var json = {};
			json[key] = areaId;
			this.currentId = obj.getAttribute('area-this-id');
			var self = this;
			
			return this.ajax(this.areaListUrl, json, function(res) {
				self.parseData(res, obj, self);
			});
		}
		
		this.parseData = function (res, obj, self) {
			layer.msg(res.message);
			var data = res.data;
			console.log(data);
			if (self.isEmptyArray(data)) {
				return false;
			}

			var str = '<option value="">请选择</option>';

			for ( var i in data) {

				if (i === self.currentId) {
					str += '<option value=' + i + ' selected="selected">'
							+ data[i].name + '</option>';
				} else {
					str += '<option value=' + i + '>' + data[i].name
							+ '</option>';
				}
			}

			obj.innerHtml = '';

			obj.innerHTML = str;
		}

	}

	Region.prototype = Tool;

	window.Region = new Region();

})(window);

window.onload = function() {

}