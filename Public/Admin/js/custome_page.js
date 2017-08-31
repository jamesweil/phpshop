
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
 * custome_page
 */

window.onload = function () {
	Tool.ueditor(options,'goods_content');
}


function CustomePage (obj, formId)
{
	this.url = obj.getAttribute('url');
	
	this.formname = null;
	
	this.formId = formId;
	
	this.submitHandleByCustom = function() {
		
		var rule = {};
		
		rule['name'] = {
			required : true,
			checkIsEmglish : true,
		}
		
		var message = {};
		
		message['name'] = {
			required : '请输入数据',
			checkIsEmglish : "只能输入英文",
		}
		console.log(getContent());
//		return this.submitHandle($("#"+this.formId), message, rule, this.url);
	}
}



(function (t) {
	
	CustomePage.prototype = t;
	
	this.getObj = function (obj, formId) {
		return new CustomePage(obj, formId);
	}
	window.CustomeEvent = this;
})(Tool);

function getContent() {
    var arr = [];
    arr.push("使用editor.getContent()方法可以获得编辑器的内容");
    arr.push("内容为：");
    console.log(UE.getEditor('goods_content').getAllHtml());
}
