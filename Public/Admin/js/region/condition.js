
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
 * 指定条件 包邮 
 */
(function (){
	
	
	
	this.checkArea = function (event) {
		
		var pId = event.value;
		var obj = $(event);
		var checked = obj.prop('checked');
		return obj.parents('.allArea').siblings().find('.children').each(function(){
			
			if ($(this).attr('pId') === pId) {
				
				$(this).prop('checked', checked);
			}
			
		});
	}
	//获取选中地区[子父]
	this.checkChildrenArea = function (event) {
		
		//获取父级编号统计父级共有几个
		var pId = event.getAttribute('pId');
		var obj = $(event);
		var count = 0 ;
		var id = 0 ;
		var number = 0;
		var curretFauther = '';
		//统计 有几个 子集
		obj.parents('.allArea').siblings().find('.father').each(function () {
			id = $(this).val();
			if(id === pId) {// 此处的 this 是不一样的
				curretFauther = $(this);
				curretFauther.parents('.allArea').siblings().find('.children').each(function () {
					if($(this).attr('pId') === id) {
						
						count++;
					}
				});
			}
		});
		
		//统计选中的
		$('.children').each(function () {
			if($(this).attr('pId') === pId && $(this).prop('checked')) {
				number++;
			}
		});
		
		if(count === number && curretFauther instanceof $) {
			curretFauther.prop('checked', true);
		} else {
			curretFauther.prop('checked', false);
		}
	}
	
	
	window.Condition = this;
	
})(window, Tool);