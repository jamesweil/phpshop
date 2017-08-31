
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
 *  系统配置js
 */
var system = {
	//配置编辑添加   layer.alert('内容');
	edit_or_add : function(url)
	{
		if(!url)
		{
			alert('来自网页的消息,未知错误');
			return false;
		}
		parent.layer.open({
			type: 2,
			shadeClose: true,
			shade: 0.5,
			area: ['600px', '500px'],
			title: '添加关键词',
			content: url,
		});
	},
	
	submit : function(event, url)
	{
		var data = this.getForm(event);
		if(!data)
		{
			alert('数据有误');
			return false;
		}
		
		this.ajax(url, data);
	},
	
	//获取form数据
	getForm :function(event)
	{
		var formData = {};
		
		$('select').each(function(){
			if(!$(this).attr('disabled') && $(this).attr('id') !='aaa')
			{
				formData[$(this).attr('name')] = $(this).find('option:selected').val();
			}
		});
		
		$('input').each(function(){
			if(!$(this).attr('disabled'))
			{
				formData[$(this).attr('name')] = $(this).val();
			}
		});
		var flag = 0;
		for(var i in formData)
		{
			if(!formData[i])
			{
//				flag++;
				delete formData[i];
			}
		}
		return  formData;
	},
	ajax : function(url , data)
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
					layer.msg(res.message);
					Tool.closeWindow();
					return true;
				}
				else
				{
					alert(res.message);
					return false;
				}
			}
		})
	},
	isTop :function(id)
	{
		
		if(id == 0)
		{
			$('#content').find('select').attr('disabled', 'disabled');
			$('#content').hide();
			$('#type').find('select').attr('disabled', 'disabled');
			$('#type').hide();
			$('#Attribute').find('select').attr('disabled', 'disabled');
			$('#Attribute').hide();
			$('#name').find('select').attr('disabled', 'disabled');
			$('#name').hide();
		}
		else
		{
			$('#content').find('select').attr('disabled', false);
			$('#content').show();
			$('#type').find('select').attr('disabled', false);
			$('#type').show();
			$('#Attribute').find('select').attr('disabled', false);
			$('#Attribute').show();
			$('#name').find('select').attr('disabled', false);
			$('#name').show();
		}
	}
};