
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
 * 选择商品 
 */
(function(){
	function select() {
		
		this.selectGoods = function () {	
		   var obj = $("input[type='radio']:checked");
		   if(obj.length == 0)
		   {
			   layer.alert('请选择商品', {icon: 2}); //alert('请选择商品');
			   return false;
		   }
	       window.opener.callBack(obj.attr('data-id'), obj.attr('data-name'));
	       return window.close();
		}
	}
	
	select.prototype = Tool;
	
	var obj = new select();
	
	window.objSelect = obj;
	return window.objSelect;
})(window);