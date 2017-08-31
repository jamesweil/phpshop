
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
 * 编辑支付
 */

(function (w) {
	
	var _data;
	var _url
	this.getInstance = function (id, url){
		_data = id;
		_url  = url;
		return this;
	}
	
	/**
	 * 保存
	 */
	this.save = function () {
		
		return $("#"+_data).validate({
			rules : rule,
			messages : msg,
			submitHandler : this.saveRemote
		});
		
	}
	
	/**
	 * 保存
	 */
	this.saveRemote = function() {
		var data  = $("#"+_data).formToArray();
		
		return Tool.ajax(_url, data, function (res) {
			 var isPass = (new NotifyAdmin(res)).NotifyMSG();
			 if (!isPass) {
				 return false;
			 }
			 
			 Tool.closeWindow();
		});
		
	}
	
	w.EditPayConfig = {
		getInstance : this.getInstance,
		save        : this.save,
		saveRemote  : this.saveRemote
	};
})(window);
