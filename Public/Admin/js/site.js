
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
 * 分站点 js
 */
(function(w, t) {

	function Site() {
		
		
		/**
		 * 地区下拉选择 
		 */
		this.select = function (event, url) {
			
//			var value = event.value;
//			
//			if (!value) {
//				return false;
//			}
//			
//			return this.submitFynction(event, url, {areaName:value});
			
			
		}
		/**
		 * 首页地区 省级输入 
		 */
		this.cin = function(obj, url, value) {
			if (!this.isNumer(value)) {
				layer.msg('参数错误');
				return false;
			}
			
			var json = {};
			json[obj.name] = value;
			
			t.noticeHTML = false;
			var self = this;
			EventAddListener.insertListen('parseSelect', self.selectList);
			return self.submitFynction(obj, url, json);
					
		}
	}
	Site.prototype = t;
	var siteObj = new Site();
	w.MySite = siteObj;
})(window, Tool);

/**
 * 下拉列表
 */
function open(obj,index,_input){
	var Length = obj.parents('.drop-main').children('.menu').children().length;
	if(Length <= 0)return;
	$('.drop-main .menu').eq(obj.parents('.drop-main').index()).toggleClass('active');
	$('.drop-main .drop').eq(obj.parents('.drop-main').index()).toggleClass('active');
	if(_input == 'false'){
		index.html('∧');
		index.attr('data','true');
	}else{
		index.html('∨');
		index.attr('data','false');
	}
}


$('.drop-main .menu').on('mouseenter','li',function(){
	$(this).addClass('hover');
}).on('mouseleave','li',function(){
	$(this).removeClass('hover');
}).on('click','li',function(){
	var obj = $(this).parents('.drop-main').find('input[type="text"]');
	
	obj.val($(this).text());
	var parent = $(this).parents('.drop-main').find('.drop');
	var txt = $(this).html().replace($(this).text(), '');

	parent.append(txt);
	
	$(this).parent().toggleClass('active');
	
	$('.drop-main .drop').eq($(this).parents('.drop-main').index()).toggleClass('active');
	
	$('.drop-main .drop a').eq($(this).parents('.drop-main').index()).html('∨');
	
	$('.drop-main .drop a').eq($(this).parents('.drop-main').index()).attr('data','false');
	if($('.drop-main .drop input').eq($(this).parents('.drop-main').index()).val() != ''){
		$('.drop-main .drop input').eq($(this).parents('.drop-main').index()).addClass('active');
	}else{
		$('.drop-main .drop input').eq($(this).parents('.drop-main').index()).removeClass('active');
	}
	var number = $(this).find('input[name="'+MySite.area+'"]').val();
	MySite.cin($('#city').get(0), CITY_LIST, number);
});