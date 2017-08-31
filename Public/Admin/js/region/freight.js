
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
 * 运费模板
 */
(function() {
	
	this.id = 0;
	
	this.addTemplate = function(formId, url) {
		/**
		 * 添加发货地区
		 */

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
				message[$(this).attr('name')] = "请输入信息";
			} 

		});
		return obj.validate({
			rules : rule,
			messages : message,
			submitHandler : function() {
				var data = obj.formToArray();
				if (Tool.isNumer(Freight.id) && Freight.id > 0) {
					var json = {};
					json['name']  = 'id';
					json['value'] = Freight.id;
					data.push(json);
				}
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
	 * 选择促销 
	 */
	this.selectGoods = function (url){
		var goodsId = []; 
		// 过滤选择重复商品
		$('input[name*="mail_area"]').each(function(i,o){
			goodsId.push($(o).val());
		});
	    return window.open(url, '请选择商品', "width=900, height=650, top=100, left=100");
	}
	window.Freight = this;
})(window);

function callBack(tableHtml)
{
	layer.closeAll('iframe');
	console.log(tableHtml);
	$('#goods_list').append(tableHtml);
}