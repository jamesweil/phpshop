
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
 * 系统配置js 
 */
function sys(){};

sys.prototype = {
	getForm : function(obj)
	{
		var form = $(obj).parents('.form').formToArray();
		var falg = 0;
		for(var i in form)
		{
			if(!form[i])
			{
				falg++;
			}
		}
		return falg ===0 && form.length ? form : null;
	},
	submit : function(obj, url)
	{
		var form = this.getForm(obj);
		
		if(form !== null)
		{
			this.ajax(url, form);
		}
		return true;
	},
	ajax : function(url, data)
	{
		$.ajax({
			url  		: url,
			type 		: 'post',
			data		: data,
			dataType	: 'json',
			success		: function(res)
			{
				if(res.status)
				{
					alert(res.message);
					var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
					index ? parent.layer.close(index) : false;
					window.parent ? window.parent.iframe.location.reload() : window.iframe.location.reload();
					return true;
				}
				else
				{
					alert(res.message);
					return false;
				}
			}
		});
	},
	timeSet : function () {
		$('input[type="datetime"]').focus(function () {
			Tool.dataPick(this);
		})
	}
};
var sys = new sys();
sys.timeSet();
EventAddListener.insertListen('listenPic', function (param){
	$('.allPic').val(param);
});