
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
 * 促销产品js
 */
(function() {
	function Promation() {
		
		this.extsion_d = 0;
		
		this.partten = function(promType, showId, url, proURL) {
			 if(!$("#"+promType).length || !$("#"+showId).length) {
				 return false;
			 }
			 var type = parseInt($("#"+promType).val());
			 switch(type){
			    case -1:{
			      expression = '<td><b class="red">*</b>代金券：</td> <td></td>';
			      
			      var expression = '<td><b class="red">*</b>代金券：</td> <td><select name="expression">';
			      // 获取面额代金卷
			      return this.ajax(url, {id:2}, function(res){
			    	  if(res.hasOwnProperty('data') &&　res.data) {
			    		 
			    		  var data = res.data;
			    		  for (var i in data) {
			    			  if (window.Promation.extsion_d == data[i].id) {
			    				  
			    				  expression += '<option value="'+data[i].id+'" selected="selected">'+data[i].name+'</option>'+'\n\t';
			    			  } else {
			    				  expression += '<option value="'+data[i].id+'">'+data[i].name+'</option>'+'\n\t';
			    			  }
			    		  }
			    		  expression = expression+'</select></td>';
			    		  $("#"+showId).html(expression)
			    	  }
			      });
			      break;
			    }
			  }
			  var obj   = $("#"+promType +" option:selected");
			  var txt 	= obj.text();
			  var value = obj.val();
//			  var value = data[children[type]];
			  
//			  if(!value) {
//				  layer.msg('请先到系统配置里设置促销类型');
//				  return false;
//			  }
			  
//			  value = value.split(',');
			  
			  var expression = '<td><b class="red">*</b>'+txt+'：</td> <td><select name="expression">';
			  
			  return this.ajax(proURL, {id:value}, function (res) {
				  if(!res.hasOwnProperty('data') || !res.data) {
					  return false;
				  }
				  var res = res.data;
				  for(var i in res) {
					  if (window.Promation.extsion_d == res[i].pro_discount) {
						  expression += '<option value="'+res[i].pro_discount+'" selected="selected">'+res[i].pro_discount+'</option>';
					  } else {
						  expression += '<option value="'+res[i].pro_discount+'">'+res[i].pro_discount+'</option>';
					  }
				  }
				  expression += '</select></td>'; 
				  return $("#"+showId).html(expression);
			  });
		}
		/**
		 * 选择促销 
		 */
		this.selectGoods = function (url){
			var goodsId = [];
			// 过滤选择重复商品
			$('input[name*="goods_id"]').each(function(i,o){
				goodsId.push($(o).val());
			});
				return window.open(url, '请选择商品', "width=900, height=650, top=100, left=100");
		}
		//赠品
		//点击按钮显示商品添加列表
		this.selectRowGoods = function (url){
			var goodsId = [];
			// 过滤选择重复商品
			$('input[name*="goods_id"]').each(function(i,o){
				goodsId.push($(o).val());
			});
			//判断只能选择一件商品 目前占不支持批量添加
			if($('#goods_list').children().length!=0) {
				layer.msg('只能选择一件商品,请删除后重试');
				return false;
			}
			else{
				return window.open(url, '请选择商品', "width=900, height=650, top=100, left=100");
			}
		}
		//赠品
		//点击按钮显示赠品添加列表
		this.selectGifts = function (url){
			var goodsId = [];
			// 过滤选择重复商品
			$('input[name*="goods_id"]').each(function(i,o){
				goodsId.push($(o).val());
			});
			//一件商品对应多件赠品
				return window.open(url, '请选择商品', "width=900, height=650, top=100, left=100");
		}
	}

	Promation.prototype = Tool;
	
	var obj = new Promation();
	window.Promation = obj;
	return window.Promation;
})(window);

window.onload = function() {
	Promation.ueditor(options,'post_content');
	Promation.dataPick('start_time');
	Promation.dataPick('end_time');
}
function callBack(tableHtml)
{
	layer.closeAll('iframe');
	$('#goods_list').append(tableHtml);
}
var i=0;
function GiftscallBack(gifts_id,gifts_name,gift_deleted,id)
{
	layer.closeAll('iframe');
	console.log(id);
	var giftHtml='<tr><td class="text-left" style="display:none;"><input type="text" name="gift['+i+'][goods_id]" value="'+id+'"></td>'+'<td class="text-left">'+gifts_name+'</td><td class="text-left"><input type="text" name="gift['+i+'][gift_number]"></td><td class="text-left"><input type="text" name="gift['+i+'][gift_stock]"></td><td>'+gift_deleted+'</td></tr>';
	i++;
	//console.log(giftHtml);
	$('#gifts_list').append(giftHtml);
}
