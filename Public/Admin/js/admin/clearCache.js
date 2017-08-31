
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
 * 清除缓存 
 */
function all_checked(){
	$('input[name="clear"]').attr("checked", true);
}
//获取所有checbox的值
function get_checbbox() {
	var str = '';
	$('input[name="clear"]:checked').each(function(){
		str += $(this).val();
	});
	return str;
}
//清空缓存
function clear_cache(){
	var index = layer.load(1, {
		shade: 0.5
	});
	var str = get_checbbox();
	$.get(DELTE_URL,{"clear":str},function(data){
		 if(data == 1){
			 parent.layer.msg('清理成功，自动关闭中',{shift: 1,time: 3000},function(){
							var index = parent.layer.getFrameIndex(window.name); //获取当前窗体索引
							parent.layer.close(index); //执行关闭
						}		
					);
		 }else{
			 layer.msg('系统异常哦', {shift: 5});
		 }
	   }, "json");
}