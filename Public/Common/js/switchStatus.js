/**
 * 切换图片状态
 */
function SwitchImage() {
	
	var _data;
	var _url;
	var _obj;
	
	var _id;
	var _imagePath = [];
	
	var TURE_STATUS = 0x01;
	
	var ERROR_STATUS = 0x00;
	
	//设置 获取实例对象
	this.getInstance = function (obj) {
		
		if (!(obj instanceof Object)) {
			throw new Exception();
		}
		_obj = obj;
		_data = obj.getAttribute('data-status');
		_id = obj.getAttribute ('data-id');
		console.log(_id);
		return this;
	}

	this.setURL =  function(url) {
		_url =  url;
		return this;
	}
	
	this.setImagType = function(imageType) {
		_imagePath = imageType;
	}
	
	this.switchImage = function () {
		
		var status = _data;
		status = status == TURE_STATUS ? ERROR_STATUS : TURE_STATUS;
		return Tool.ajax(_url, {status : status, id : _id}, function (res) {
			 var isPass = (new NotifyAdmin(res)).NotifyMSG();
			 if (!isPass) {
				 return false;
			 }
			 _obj.setAttribute('src', _imagePath[status]);
			 _obj.setAttribute('data-status', status);
		});
	}
	
	this.setDefault = function() {
		
		var status = _data;
		if (status == TURE_STATUS) {
			return false;
		}
		return Tool.ajax(_url, {id : _id}, function (res) {
			 var isPass = (new NotifyAdmin(res)).NotifyMSG();
			 if (!isPass) {
				 return false;
			 }
			 
			 _obj.setAttribute('src', _imagePath[TURE_STATUS]);
			 _obj.setAttribute('data-status',TURE_STATUS);
			 
			 $(_obj).parents('tr').siblings('tr').find('.all').each(function (index, event) {
				 
				 event.setAttribute('src', _imagePath[status]);
				 event.setAttribute('data-status', status);
				 
			 });
			
		});
		
	}
};
(function (w) {
	var switchI = new SwitchImage();
	w.SwitchStatus = switchI;
})(window);