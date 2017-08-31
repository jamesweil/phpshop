
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
 * 用户组 
 */
function del(id){
	$("#del"+id+" td").css('background','#CBDFF2');
	parent.layer.confirm('请确认该组下已经没有组成员了，否则需要重新授权，真的要删除吗？', {
		btn: ['是的','取消'], //按钮
		shade: 0.5
	}, function(){
		$.post(ADMIN_GROUP_DEL, { "id": id },function(data){
			if(data == 1){
				parent.layer.msg('删除成功', { icon: 1, time: 1000 }, function(){
						$("#del"+id).remove();
					});
			}else{
				parent.layer.msg('删除失败', {icon: 2, time: 2000 }); 
			}
	   }, "json");
	},function(){
		$("#del"+id+" td").css('border-top','0');
		$("#del"+id+" td").css('border-bottom','1px solid #EFEFEF');
	});
}