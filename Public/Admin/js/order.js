
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
 * 订单js
 */
(function() {
	function Order() {
		this.page = 0;
		/**
		 * ajax 获取订单数据 
		 * @param  string id  form id
		 * @param  int page   页数
		 */
		this.ajaxForMyOrder = function(id, page) {
			this.page = page;
			if (!$('#' + id).length || !this.isNumer(page)) {
				layer.msg('参数错误');
				return false;
			}
			var data = $('#' + id).serialize();
			var url = document.getElementById(id).getAttribute('url')+'?p='+page;
			this.dataType = '';
			return this.post(url, data, function(res) {
				$("#ajaxGetReturn").html('');
				$("#ajaxGetReturn").append(res);
			})

		}
		/**
		 * 排序 
		 */
		this.sort = function(id, field) {
			$("input[name='order_by']").val(field);
			var v = $("input[name='sort']").val() == 'desc' ? 'asc' : 'desc';
			$("input[name='sort']").val(v);
			this.ajaxForMyOrder(id, this.page);
		}
		
		/**
		 * 是否收到货 
		 */
		this.isReceive = function (url, event, jre) {
			if (!(event instanceof Object) || !(jre instanceof Object)) {
				layer.msg('参数错误');
				return false;
			}
			var obj = $(event);
			var name= obj.attr('name');
			var value = obj.attr('value')
			value = value === '1' ? 2 : (value === '2' ? 1 : 1); 
			if (!(name) || !(value)) {
				layer.msg("参数异常");
				return false;
			}
			var json = {};
			json[name] = value;
			json = eval('('+(JSON.stringify(json)+JSON.stringify(jre)).replace(/}{/,',')+')');
			return this.ajax(url, json , function (res){
				layer.msg(res.message);
				if (res.hasOwnProperty('status') && res.status == 1) {
					return setInterval(function () {
						location.reload();
					}, 3000);
					return false;
				}
			});
		}
		/**
		 * 删除用户 
		 */
		this.deleteUser = function(url, id) {
			
			
			if(!this.isNumer(id)) {
				layer.msg('参数错误');
				return false;
			}
			
			if(!confirm('确定删除吗')) {
				return false;
			}
			
			return this.ajax(url, {id :id}, function(res) {
				if(res.hasOwnProperty('status') && res.status == 1) {
					layer.msg(res.message);
					return Tool.closeWindow();
				}
			});
		}
	}
	Order.prototype = Tool;
	window.Order = new Order();
})(window);

/**
 * 页面加载完成时【运行的方法】 
 */
window.onload = function() {
	Order.ajaxForMyOrder('conditionForm', 1);
}