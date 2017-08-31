
/**
 * 通知[后台 
 */
function NotifyAdmin(res){
	
	var _res = res;
	
	/**
	 * 通知消息
	 */
	this.NotifyMSG = function () {
		
		var data = _res.data;
		
		var isTrue = this.notify();
		if (typeof data !== 'undefined' && data.hasOwnProperty('url')) {
			
			this.notifyURL();
		}
		
		return isTrue;
		
	}
	
	this.notify = function () {
		
		var data = _res.data;
		
		var status = _res.status;
		
		layer.msg(_res.message);
		
		if (!status) {
			 return true;
		} 
		return true;
	}
	
	this.notifyURL = function() {
		
		var url = _res.data.url;
		
		setInterval (function () {
			location.href = url;
		}, 2000);
	}
};