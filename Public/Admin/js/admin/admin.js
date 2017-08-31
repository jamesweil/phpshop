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
 * 管理员 js
 */
//添加用户
function add(){
	parent.layer.open({
		type: 2,
		shadeClose: true,
		shade: 0.5,
		area: ['450px', '310px'],
		title: '添加用户',
		content: ADMIN_ADD
	});
}

admin_system=function()
{
	parent.layer.open({
		type: 2,
		shadeClose: true,
		shade: 0.5,
		area: ['450px', '400px'],
		title: '短信设置',
		content: ADMIN_SYSTEM_SAVE
	});
}

//编辑用户
function edit(id){
	parent.layer.open({
		type: 2,
		shadeClose: true,
		shade: 0.5,
		area: ['450px', '240px'],
		title: '编辑账号信息',
		content: ADMIN_EDIT+'?id='+id
	});
}

//删除用户
function del(id){
	$("#del"+id+" td").css('background','#CBDFF2');
	parent.layer.confirm('真的要删除吗？', {
		btn: ['确认','取消'], //按钮
		shade: 0.5 //显示遮罩
	}, function(){
		$.post(ADMIN_DEL, { "id": id},function(data){
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

//开启关闭功能
is_start=function()
{
	if($('input[name=is_start]').val()==1)
	{
		$.ajax({
			type:"POST",
			data:"IS_START_CONFIG="+0,
			url:'config_status_save',
			success:function(data)
			{
				//console.log(data);debugger;
				if(data['status']==1)
				{
					layer.msg('更改成功,请等待刷新');
					page_href=function (){
						window.location.href=data['url'];
					}
					setTimeout("page_href()",2000);
				}
			}
		})
	}else{
		$.ajax({
			type:"POST",
			data:"IS_START_CONFIG="+1,
			url:'config_status_save',
			success:function(data)
			{
				//console.log(data);debugger;
				if(data['status']==1)
				{
					layer.msg('更改成功,请等待刷新');
					page_href=function (){
						window.location.href=data['url'];
					}
					setTimeout("page_href()",2000);
				}
			}
		})
	}
}


sms_template_save = function(){
	var obj = $('#form_save').serializeArray();
	$.post(SAVE_TEMPLATE_SAVE,obj,function(e){
		if(e.status == 1){
			layer.msg('保存成功');
		}
	})
}

//sms_template_save=function ()
//{
//	$('._simple-switch-track').each(function(){
//		if($(this).hasClass('on'))
//		{
//			$(this).siblings('input').val(1);
//		}else{
//			$(this).siblings('input').val(0);
//		}
//	})
//	$.ajax({
//		type:"POST",
//		url:"save_template_save",
//		data:$('#from_save').serialize(),
//		success:(function(data){
//		if(data['status']==1)
//		{
//			layer.msg('更新成功');
//		}else{
//			layer.msg('请重试');
//			return false;
//			}
//		})
//	})
//}




template_url=function (template_id)
{
	$('#content'+template_id).css('display','block');
	$('#content'+template_id).siblings().css('display','none');
}