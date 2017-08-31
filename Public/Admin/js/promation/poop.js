
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
 * 尾货清仓
 */
(function (){
	
	this.extsion_d = 0;
	
	this.typeId = 0;
	
	this.setTypeId = function (id) {
		this.typeId = id;
	}
	this.setExtsionId = function (id) {
		this.extsion_d = id;
	}
	
	/**
	 * 折扣类型
	 */
	this.prom = function (obj, id, url) {
		
		var title = obj.options[obj.selectedIndex].text;
		
		if (obj.value == -1) {
			 expression = '<td><b class="red">*</b>代金券：</td> <td></td>';
		      
		      var expression = '<td><b class="red">*</b>代金券：</td> <td><select name="expression">';
		      // 获取面额代金卷
		      return Tool.ajax(url, {id:2}, function(res){
		    	  if(res.hasOwnProperty('data') &&　res.data) {
		    		 
		    		  var data = res.data;
		    		  for (var i in data) {
		    			  if (this.extsion_d == data[i].id) {
		    				  
		    				  expression += '<option value="'+data[i].id+'" selected="selected">'+data[i].name+'</option>'+'\n\t';
		    			  } else {
		    				  expression += '<option value="'+data[i].id+'">'+data[i].name+'</option>'+'\n\t';
		    			  }
		    		  }
		    		  expression = expression+'</select></td>';
		    		  $("#"+id).html(expression)
		    	  }
		      });
		}
	    
		
		var html = '<td><b class="red">*</b>'+title+'：</td> <td><input type="text" class="form-control w300" name="expression" id="name" value="'+this.extsion_d+'"> ';
	
		return $('#'+id).html(html);
	}
	
	/**
	 * 删除数据
	 */
	this.deleteDataByRomente = function(url, obj) {
		if (!(obj instanceof Object)) {
			return false;
		}
		var json = {};
		
		$(obj).find('input[type="hidden"]').each(function () {
			json[this.name] = this.value;
		});
		
	 layer.open({
		 content : '您确认删除吗？',
		 btn : [ '确认', '取消' ],
		 shadeClose : false,
		 yes : function() {
			 Tool.ajax(url, json, function(res) {
				 layer.msg(res.message);
				 if (res.hasOwnProperty('status') && res.status == 1) {
					 return setInterval(function() {
						 location.href = res.data.url;
					 }, 3000);
				 }
				 return false;
			 });
		 },
		 no : function() {
		
		 }
	 });

	},
	
	
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
	
	
	window.Poop = this;
})(Tool);

/*
 * 用来遍历指定对象所有的属性名称和值 obj 需要遍历的对象 author: Jet Mah
 */ 
function allPrpos ( obj ) { 
	// 用来保存所有的属性名称和值
	var props = "" ; 
	// 开始遍历
	for ( var p in obj ){ 
	// 方法
	if ( typeof ( obj [ p ]) == " function " ){ 
	obj [ p ]() ; 
	} else { 
	// p 为属性名称，obj[p]为对应属性的值
	console.log ( p + " => " + obj [ p ]) ; 
	} 
	} 
	// 最后显示所有的属性
	
} 