/**
 * 通知 
 */
(function(w, t){
	
	var _res = null;
	
	this.getInstance = function(res) {
		
		_res = res; 
		
		return this;
	}
	
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
		
		if (!status) {
			 t.error(_res.message);
			 return true;
		} 
		
		t.success(_res.message);
		
		return true;
	}
	
	this.notifyURL = function() {
		
		var url = _res.data.url;
		
		setInterval (function () {
			location.href = url;
		}, 2000);
	}
	w.Notify = {
			'getInstance' : this.getInstance,
			'NotifyMSG'	  : this.NotifyMSG,
			'notify'	  : this.notify,
			'notifyURL'	  : this.notifyURL
	};
})(window, toastr);