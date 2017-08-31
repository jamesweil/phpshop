
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
 * 
 */
(function (){
	
	this.page = 0;
	
	// 删除操作
	this.del = function (id, url) {
		
	}
	
	//ajax 获取页面
	this.ajaxGetHtml = function(url, formId, page) {
		
		var obj = $('#'+formId);
		if(!Tool.isNumer(page) || !obj.length) {
			return false;
		}
		
		this.page = page;
		
		$.post(url+'/p/'+page, obj.formToArray(), function(res){
			var obj = $('#ajax_return');
			obj.html('');
			obj.html(res);
		}, '');
		
	    this.deleteCon = function (id, url, event) {
	    	
	    	if(!Tool.isNumer(id)) {
	    		return false;
	    	}
	    	
	    	return Tool.ajax(url, {id : id}, function (res) {
	    		
	    		if (res.hasOwnProperty('status') && res.status == 1) {
	    			layer.msg(res.message);
	    			return $(event).parents('.trOwn').remove();
	    		}
	    		return layer.msg(res.message);
	    	});
	    	
	    }
		
	}
	window.Consulation = this;
	
})(window, Tool);
  
//   
//    function op(){
//        //获取操作
//        var op_type = $('#operate').find('option:selected').val();
//        if(op_type == 0){
//			layer.msg('请选择操作', {icon: 1,time: 1000});   //alert('请选择操作');
//            return;
//        }
//        //获取选择的id
//        var selected = $('input[name*="selected"]:checked');
//        var selected_id = [];
//        if(selected.length < 1){
//
//			layer.msg('请选择项目', {icon: 1,time: 1000}); //            alert('请选择项目');
//            return;
//        }
//        $(selected).each(function(){
//            selected_id.push($(this).val());
//        })
//        $('#op').find('input[name="selected"]').val(selected_id);
//        $('#op').find('input[name="type"]').val(op_type);
//        $('#op').submit();
//    }
//
//    $(document).ready(function(){
//        ajax_get_table('search-form2',1);
//    });
//
//
//    // ajax 抓取页面
//    function ajax_get_table(tab,page){
//        cur_page = page; //当前页面 保存为全局变量
//        $.ajax({
//            type : "POST",
//            url:"/index.php/Admin/Comment/ajax_ask_list/p/"+page,//+tab,
//            data : $('#'+tab).serialize(),// 你的formid
//            success: function(data){
//                $("#ajax_return").html('');
//                $("#ajax_return").append(data);
//            }
//        });
//    }