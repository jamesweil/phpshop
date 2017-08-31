
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
 * 发货
 */

window.onload = function (){
	
	(function(w){
		
		function send(){
			
			this.orderClick = 0;
			
			/**
			 * 提交前验证 
			 */
			this.submitCheck = function (id) {
				
				var num = document.getElementById(id).value;
				
				if (!this.isNumer(num)) {
					alert('快单号必须是数字');
					return false;
				}
				
				if(num.toString().length < 6) {
					alert('快递单号必须大于6位');
					return false;
				}
				return true;
			}
			
			/**
			 * 退货 
			 * @param 
			 */
			this.returnGoods = function (orderId, url) {
				
				if(this.orderClick !==0) {
					layer.msg('不能重复点击');
					return false;
				}
				
				if (!this.isNumer(orderId)) {
					return false;
				}
				this.orderClick = 1;
				return this.ajax(url, {id : orderId}, function(res) {
					layer.msg(res.message);
//					if (res.hasOwnProperty('data') && res.data) {
//						Sender.alertEdit(res.data.url, '退款申请中。。。。', 800, 600);
//					}
					return true;
				});
			}
			
			/**
			 * 不予退货  
			 */
			this.noReturn = function(orderId, url) {
				if (!this.isNumer(orderId)) {
					return false;
				}
				return this.ajax(url, {id : orderId}, function(res) {
					layer.msg(res.message);
					return true;
				});
			}
			
			/**
			 * 优惠券费用信息 
			 */
			this.moneryInfor  = function (id, url, orderId) {
				
				var obj = $('#'+id);
				
				if (obj.length == 0 || !this.isNumer(orderId)) {
					return false;
				}
				
				return $.post(url, {id : orderId, monery : Monery}, function (res) {
					obj.html('');
					obj.html(res);
				})
			}
		};
		send.prototype = Tool;
		w.Sender = new send();
		return w.Sender;
	})(window);
	Sender.moneryInfor('moneryInformation', MONERY_LIST, ORDER_ID);
}
var id = null;
function AAA(res) {
	
	if(!res) {
		return false;
	}
	layer.msg('退款成功'+'，'+res.monery+'元');
	
	console.log(data);
	Tool.closeWindow();
	return true;
}
clearInterval(id);