
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
 * 添加回复
 */
(function (){
	
	this.addMessage = function (url, event) {
		
		if (!(event instanceof Object)) {
			return false;
		}
		
		var data = {};
		
		$(event).parents('.formIdy').find('.textsd').each(function () {
			
			if($(this).val()) {
				data[$(this).attr('name')] = $(this).val();
			} else {
				layer.msg('参数异常');
				return false;
			}
		});
		
		return Tool.ajax(url, data, function (res) {
			var data = res.data;
			layer.msg(res.message);
			if (data.hasOwnProperty('url')) {
				setInterval(function() {
					location.href = data.url;
				}, 3000);
			}
		});
	}
	
	window.Message = this;
})(window, Tool);