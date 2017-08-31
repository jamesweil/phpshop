
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
 *优惠券js 
 */
(function(){
	
	function Conpon() {
		
		this.addConpon = function (url, id) {
			
			var isExe = document.getElementById(id);
			
			if(!isExe) {
				return false;
			}
			
			var form = $('#'+id).formToArray();
			console.log(form);
			var flag = 0;
			
			for(var i in form) {
				
				if(!form[i].value) {
					return false;
				} else {
					flag ++;
				}
			}
			
			if(flag === 0) {
				return false;
			}
			
			return this.ajax(url, form, function(res){
				return Tool.notice(res);
			});
		}
	}
	Conpon.prototype = Tool;
	
	window.Conpon = new Conpon();
	
	return window.Conpon;
})(window);
$(function(){
	
	
});

window.onload = function () {
	
	Conpon.dataPick('send_start_time');
	Conpon.dataPick('send_end_time');
	Conpon.dataPick('use_start_time');
	Conpon.dataPick('use_end_time');
    
    $('input[type="radio"]').click(function(){
        if($(this).val() == 0){
        	$('.timed').find('input[type="text"]').each(function(){
        		$(this).attr('disabled', 'disabled');
        	});
        	$('.timed').hide();
        }else{
        	$('.timed').show();
        	$('.timed').find('input[type="text"]').each(function(){
        		$(this).attr('disabled', false);
        	});
        }
    });
    $('input[type="radio"]:checked').trigger('click');
}
