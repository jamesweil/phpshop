
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
 * 团购
 */
(function() {
	
	function Group (){
		
		/**
		 * 选择商品
		 * @param string url 
		 */
		this.selectGoods = function (url) {
			
			if (!url) {
				return false;
			}
			
			return window.open(url, '请选择商品', "width=900, height=650, top=100, left=100");
		}
		
		
	}
	
	Group.prototype = Tool;
	var obj = new Group();
	window.GroupObj = obj;
	
})(window);

window.onload = function(){
	GroupObj.dataPick('start_time');
	GroupObj.dataPick('end_time');
}