
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
 * 获取列表
 */
(function(w,t) {
	
	this.changeData = function (event, id)
	{
		var value = event.value;
		console.log(id);
		var obj = $('#'+id);
		console.log(obj);
		if (value != 1) {
			obj.addClass('hidden');
			
			this.getData(obj, true);
			
		} else {
			obj.removeClass('hidden');
			this.getData(obj, false);
		}
	}
	this.getData = function(event, isCheck) {
		event.attr('disabled', isCheck);
	}
	w.ChangeEvent = this;
})(window, Tool);

ChangeEvent.changeData($('select').get(0), 'have');