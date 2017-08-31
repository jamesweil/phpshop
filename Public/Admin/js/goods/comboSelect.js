
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
 *可下拉 可搜索 
 */

(function (w, t) {
	
	/**
	 * 获取分类
	 */
	
	this.classValue = 0;
	
	
	
	window.GetClassOBj = this;
	
})(window, Tool);
EventAddListener.insertListen('comboSelect', function (param) {
	var obj = param[1];
	var str = param[0];
	$(obj).html(null);
	$(obj).html(str);
	$(obj).comboSelect();//下拉选择框 加事件
});

$(document).ready(function () {
	
	$(document.getElementById('first')).comboSelect();
	var id = setInterval(function () {
		Tool.getClassById(CLASS_LIST, document.getElementById('second'));
	}, 100);
	
	setInterval(function (){
		clearInterval(id);
	}, 100);
	
	var three = setInterval(function () {
		Tool.getClassById(CLASS_LIST, document.getElementById('three'));
	}, 100);
	
	setInterval(function (){
		clearInterval(three);
	}, 100);
	//--------------------------------------------
	$(document.getElementById('four')).comboSelect();
	var fourId = setInterval(function () {
		Tool.areaData = extendClassData;
		Tool.getClassById(CLASS_LIST, document.getElementById('five'));
	}, 1000);
	
	setInterval(function (){
		clearInterval(fourId);
	}, 1000);
	
	var fiveId = setInterval(function () {
		Tool.getClassById(CLASS_LIST, document.getElementById('six'));
	}, 1000);
	
	setInterval(function (){
		clearInterval(fiveId);
	}, 1000);
});