
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
 * 商品选择
 */
(function(){
	function searchGoods() {
		this.selectGoods = function(id, remove)
		  {
			   if($("input[type='checkbox']:checked").length == 0)
			   {
				   layer.alert('请选择商品', {icon: 2}); //alert('请选择商品');
				   return false;
			   }
			  	//将没选中的复选框所在的  tr  remove  然后删除复选框
			    $("input[type='checkbox']").each(function(){
				   if($(this).is(':checked') == false)
				   {
					    $(this).parent().parent().remove();
				   }else{
					   $(this).parent().css('display','none');
					   $(this).attr("checked","checked");
				   }
			    });
				$("."+remove).remove();
		        javascript:window.opener.callBack($('#'+id).html());

				return window.close();
		  }

		//赠品中的商品列表显示
		this.selectRowGoods = function(id, remove)
		{
			if($("input[type='checkbox']:checked").length == 0)
			{
				layer.alert('请选择商品', {icon: 2}); //alert('请选择商品');
				return false;
			}
			//判断checkbox长度限定只能选择一件商品
			if($("input[type='checkbox']:checked").length >= 2 )
			{
				layer.alert('只能选择一件商品', {icon: 2}); //alert('请选择商品');
				return false;
			}
			//将没选中的复选框所在的  tr  remove  然后删除复选框
			$("input[type='checkbox']").each(function(){
				if($(this).is(':checked') == false)
				{
					$(this).parent().parent().remove();
				}else{
					$(this).parent().css('display','none');
					$(this).attr("checked","checked");
				}
			});
			$("."+remove).remove();
			javascript:window.opener.callBack($('#'+id).html());

			return window.close();
		}

		this.selectGifts = function(id, remove)
		{
			if($("input[type='checkbox']:checked").length >= 2 )
			{
				layer.alert('一次只能添加一个赠品，可多次添加', {icon: 2}); //alert('请选择商品');
				return false;
			}
			if($("input[type='checkbox']:checked").length == 0)
			{
				layer.alert('请选择商品', {icon: 2}); //alert('请选择商品');
				return false;
			}
			//将没选中的复选框所在的  tr  remove  然后删除复选框
			$("input[type='checkbox']").each(function(){
				if($(this).is(':checked') == false)
				{
					$(this).parent().parent().remove();
				}else{
					$(this).parent().css('display','none');
					$(this).attr("checked","checked");
				}
			});
			$("."+remove).remove();

			javascript:window.opener.GiftscallBack($('#'+id).find('td').eq(0).html(),$('#'+id).find('td').eq(1).html(),$('#'+id).find('td').eq(4).html(),$('#'+id).find('input').val());

			return window.close();
		}
	}
	window.SearchGoods = new searchGoods();
	return window.SearchGoods;

})(window);