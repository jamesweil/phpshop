
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
 * 发放代金卷
 */
(function(){
	function SendCoupon () {
		
		
		/**
		 * 线下发放
		 */
		this.makeCoupon = function(url) {
			
			var coupon = new Array();
			
			$('input').each(function() {
				coupon.push({name:$(this).attr('name'), value:$(this).val()});
			})
			
			if(!coupon.length) {
				layer.msg('参数错误');
				return false;
			}
			
			var flag = 0;
			
			for(var i in coupon) {
				if(!coupon[i].value || coupon[i].value == 0) {
					layer.msg('参数错误');
					return false;
				} else {
					flag++;
				}
			}
			if(flag === 0) {
				return false;
			}
			return this.ajax(url, coupon, function(res) {
				return Tool.notice(res);
			});
		}
		
		
		/**
		 * 按照用户等级
		 */
		this.sendCouponByUserLevel = function(url, rap, id) {
			
			var rap = $('.'+rap);
			
			if ( !rap.length || !$('#'+id).length ) {
				return false;
			}
			
			var data = [];
			
			rap.each(function(){
				data.push({name:$(this).attr('name'), value : $(this).val()});
			});
			
			var value = $('#'+id).find("option:selected").val();
			
			data.push({name:id, value:value});
			
			for( var i in data) {
				if(!data[i].value){
					layer.msg('参数错误');
					return false;
				} else {
					flag = 1;
				}
			}
		
			if(flag === 0) {
				return false;
			}
			
			return this.ajax(url, data, function(res) {
				return Tool.notice(res);
			});
			
		}
		/**
		 * 发放代金卷 [按照搜索条件]
		 */
		this.sendCoupon = function(url, rap) {
			
			var rap = $('.'+rap);
			
			if (!rap.length) {
				return false;
			}
			
			
			if(!this.dataJson.length) {
				layer.msg('请选择用户');
				return false;
			}
			
			var flag = 0;
			var data = this.dataJson;
			
			for( var i in data) {
				if(!data[i].value){
					layer.msg('参数错误');
					return false;
				} else {
					flag = 1;
				}
			}
		
			if(flag === 0) {
				return false;
			}
			
			rap.each(function(){
				data.push({name:$(this).attr('name'), value : $(this).val()});
			});
			
			return this.ajax(url, data, function(res) {
				return Tool.notice(res);
			});
		}
		/**
		 * 删除代金卷 
		 */
		this.deleteCoupon = function(url, id) {
			
			if(!this.isNumer(id)) {
				layer.msg('参数错误');
				return false;
			}
			
			return this.ajax(url, {id:id}, function(res){
				return Tool.notice(res);
			});
			
		}
		
	}
	
	SendCoupon.prototype = Tool;
	
	window.SendCoupon = new SendCoupon();
	
	return window.SendCoupon;
	
})(window)