
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
 * 日志操作 js 
 */
window.onload = function() {
	LogObj.dataPick('create_time')
}

/**
 * @param string format 日期格式
 * @param boolean singleDataPicker 单日期选择器
 */
function Log(format, singleDataPicker) {
	
	this.setFormat(format);
	
	this.setSingleDataPicker(false);
	
}

(function(t) {
	Log.prototype = t;
	var logObj = new Log('YYYY-MM-DD HH:mm:ss', false);
	window.LogObj = logObj;
})(Tool);
