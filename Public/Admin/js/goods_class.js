
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
 * 修改是否显示在导航栏中
 */
$(document).ready(function(){
	$(".editIsSHowNav").click(function(){
		var postdata = {};
		$(this).find('input').each(function(){
			postdata[$(this).attr('name')] = $(this).val();
		});
		if(postdata)
		{
			$.ajax({
				url  : url,
				type : 'post',
				data : postdata,
				dataType : 'json',
				success : function(result) {
					opreation.message(result);
					return false;
				}
			});
		}
		else 
		{
			opreation.message({data:{message : '操作有误'}});
			return false;
		}
	})
});

var opreation = {
	message : function(data)
	{
		//判断是否是json数据
		if (typeof(data) == "object" && Object.prototype.toString.call(data).toLowerCase() == "[object object]" && !data.length)
		{
			if(data.hasOwnProperty('message'))
			{
				layer.msg(data.message);
				setInterval(function(){
					location.reload();
				}, 3000);
				return true;
			}
		}
	}
};