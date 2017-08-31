
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
 * 用户组 编辑权限 
 */
$(function(){
	$("#all_checkbox").click(function(){
		var all = $('#all').val();
		if(all == 1){	
			$('#all').attr('value', 0);	
			//此处使用attr第二次设置的时候会除问题，解决办法使用prop函数，jquery版本必须要1.6.1以上
			$('input[type="checkbox"]').prop('checked', false);
		}else{
			$('#all').attr('value', 1);
			$('input[type="checkbox"]').prop('checked', true);
		}
	});
});

function checkbox(id){
	var box = $('#box'+id).val();
	if(box == 1){
		$('#box'+id).attr('value', 0);	
		//此处使用attr第二次设置的时候会出问题，解决办法使用prop函数，jquery版本必须要1.6.1以上
		$('.checkbox'+id).prop('checked', false);
	}else{
		$('#box'+id).attr('value', 1);
		$('.checkbox'+id).prop('checked', true);
	}
}
function check_form(){
	var title = $('#title').val();
	if(title == ''){
		layer.tips('请输入组名称', '#title', {time: 10000});
		return false;
	}
	return true;
}