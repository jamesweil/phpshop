
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
 * 商品编辑添加
 */
(function(){
	
	function Goods()
	{
		this.url		 = null;
		this.dbUrl       = null;
		this.del		 = null; //删除图片的url
		this.selectIndex = 0; 
		this.ajaxReturn  = null;
		this.parimayKey  = 0; 
		this.number = 0;
		this.imgUrl = null;
		//选项卡
		this.selectTab = function() {
		    return $('.panel-body .nav li').on('click',function(){
				$('.panel-body .nav li').removeClass('active').eq($(this).index()).addClass('active');
				GoodsOption.url = $(this).attr('url');
				GoodsOption.selectIndex = $(this).attr('num')-1;
				$('input[type="button"]').attr('url',$(this).attr('url'));
				GoodsOption.type = 'post';
				$('.tab-content .tab-pane').removeClass('active').eq($(this).index()).addClass('active');
			});
		}

		/**
		 * 初始化
		 * @param string tabSelect 切换按钮
		 * @returns Boolean
		 */
		this.init = function (tabSelect){
			var obj = $('#'+tabSelect);
			
			if(!obj.length) {
				return false;
			}
			var length = 0;
			obj.find('a').each(function(){
				if($(this).attr('data-toggle') === 'tab') {
					length++;
					return $(this).parent().attr('num', length);
				}
			});
			
			this.number = length;
			
			return length;
		}
		
		//提交检测
		this.submitPjax = function (tabSelect, formId, url) {
			
			var obj = $('#'+tabSelect);
			if(!obj.length) {
				return false;
			}
			//获取标识
			var flag =0;
			obj.find('li').each(function() {
				if($(this).hasClass('active')) {
					flag = $(this).attr('num');
				}
			});
			flag = parseInt(flag)+1; 
			var sdk = null;
			if(flag > this.number) {
				return false;
			}
			
			obj.find('li').each(function(){
				if($(this).attr('num') == flag) {
					
					$(this).prev().removeClass('active');
					$(this).addClass('active');
				}
			});
			$('.tab-pane').each(function(){
				if($(this).hasClass('active')) {
					sdk = $(this).next();
					$(this).removeClass('active');
				}
				
			});
				
			if(sdk instanceof Object){
				sdk.addClass('active');
				
				//走提交
				return this.addGoods(formId, url);
			}
		}
		this.getClass = function(obj, url, value) {
			value = (typeof value ==='undefined' ? obj.getAttribute('data') : value);
			console.log(value);
			if (!this.isNumer(value)) {
				layer.msg('参数错误');
				return false;
			}
			var json = {};
			Tool.area = 'class_id';
			json[obj.name] = value;
			Tool.noticeHTML = false;
			var self = this;
			
			EventAddListener.insertListen('parseSelect', self.selectOneArray);
			
			var app =  self.submitFynction(obj, url, json);
			
			Tool.noticeHTML = true;
			
			return app;
			
		}
		
		this.search = function (obj) {
			if (!(obj instanceof HTMLElement)) {
				return false;
			}
			value = obj.value;
			return $(obj).parent().siblings('.menu').find('li').each(function() {
				if ($(this).text().indexOf(value) === -1) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});
			
		}

		//商品属性
		/**
		 * @param Object objx 空间对象
		 * @paran String url url连接
		 * @param Function callBack 会掉方法
		 */
		this.getAttribute = function (objx, url, callBack) {
			if(!objx instanceof Object) {
				return false;
			}
			var data = {};
			data[objx.name] = objx.value;
			return $.ajax({
						url  : url,
						type : 'get',
						data : data,
						success : callBack
				  });
		}
		
		/**
		 * 获取规格项 
		 * @param Object event    
		 * @param string url url 连接
		 * @param 获取不同的规格项
		 */
		this.getSpec = function(event, url, targetUrl) {
			  
			  var goods_id = !GoodsOption.ajaxReturn ? this.parimayKey : GoodsOption.ajaxReturn;
             
			  var spec_type =$(event).val();
			  $.ajax({
				  type:'get',
				  url:url, 
				  data:{goods_id:goods_id,spec_type:spec_type}, 
				  success:function(res) {
					  $("#ajax_spec_data").html('');
	                  $("#ajax_spec_data").append(res);
				  }
			  });
		}
		
		this.parseAttr = function(res) {
			$('#ajax_spec_data').append(res);
		}
		
		
		/**
		 * 添加商品 
		 */
		this.addGoods = function(id, url, redirct) {
			var trueURL = this.url === null ? url : this.url;
			var data = $('#'+id).formToArray();

			if(this.ajaxReturn) {
				data.push({name :'goods_id', value : this.ajaxReturn});
			} else {
				data.push({name :'goods_id', value : this.parimayKey});
			}
			if(this.selectIndex > this.number) {
				location.href = redirct;
			}
			this.type='post';
			this.selectIndex += 1;
			Tool.noticeHTML = true;
			return this.ajaxOther(url, data, function(res) {
				if(res.status) {
					layer.msg(res.message);
					GoodsOption.ajaxReturn = res.data.insertId;
					if(GoodsOption.selectIndex ==4) {
						location.href = redirct;
					}
					$('input[type="button"]').attr('url', res.data.url);
					$('.panel-body .nav li').removeClass('active').eq(GoodsOption.selectIndex).addClass('active');
					$('.tab-content .tab-pane').removeClass('active').eq(GoodsOption.selectIndex).addClass('active');
				} else {
					layer.msg(res.message);
					GoodsOption.selectIndex -=1;
					console.log(GoodsOption.selectIndex);
				}
			});
		}
		
		/**
		 * 按钮切换 
		 */
		this.buttonTab = function(tag) {
			 if($(tag).hasClass('btn-success'))
			   {
				   $(tag).removeClass('btn-success');
				   $(tag).addClass('btn-default');		   
			   }
			   else
			   {
				   $(tag).removeClass('btn-default');
				   $(tag).addClass('btn-success');		   
			   }
		}
		
		/**
		 * 获取 商品属性规格 数据
		 * @param string tag  标签
		 * @param string url  请求连接
		 * @param string id   属性id 数据 要添加的地方
		 * @param Object	  标签对象
		 */
		this.getAttr = function(tag, url, id, event) {
			
			this.buttonTab(event);
			
			var spcArr = {};
			$(tag).each(function(){
				 if($(this).hasClass('btn-success'))	
					{
						var spec_id = $(this).data('spec_id');
						var item_id = $(this).data('item_id');
						if(!spcArr.hasOwnProperty(spec_id))
							spcArr[spec_id] = [];
						spcArr[spec_id].push(item_id);
					}		
			});
			
			var js = {spc:spcArr};
			
			if(this.parimayKey) {
				js['goods_id'] = this.parimayKey;
			}
			this.ajax(url, js, function(res) {
				 $("#goods_spec_table2").html('')
				 $("#goods_spec_table2").append(res.data);
				 GoodsOption.mergeCell(id);
			});
		}
		/**
		 * 删除图片文件，及其数据库图片路径 
		 */
		this.ClearPicArr2 = function(obj,path)
	    {
			this.type='get';
	    	this.ajax(this.del, {filename : path}, function(res){
	    		 $(obj).parent().remove(); 
	    	})
	    	
	    	return this.ajax(this.dbUrl, {filename : path}, function(res){
	    		 return true;
	    	})
	    }
		
		/**
		 * 三级分类 
		 */
		this.threGoodsClass = function (url) {
			 $.getJSON(url,function (json) {
                  var goodsCats = json;
                  $("#cat_id").on('change',function(){
                      var cat1_id = $("#cat_id").val();
                      cat2_html = '<option value="0">请选择商品分类</option>';
                      cat3_html = '<option value="0">请选择商品分类</option>';
                      if(cat1_id == "0"){
                          $("#cat_id_2").html(cat2_html);
                          $("#cat_id_3").html(cat3_html);
                          return false;
                      };
                      console.log(goodsCats);
                      $.each(goodsCats,function(i,v){
                          if(cat1_id == v.fid){
                              cat2_html += '<option value="'+ v.id+'">'+ v.class_name+'</option>';

                          }
                          $("#cat_id_2").html(cat2_html);
                      });
                      $("#cat_id_3").html(cat3_html);
                  });
                  $("#cat_id_2").on("change",function(i,v){
                      var cat2_id = $("#cat_id_2").val();
                      $.each(goodsCats,function(i,v){
                          if(cat2_id == v.fid){
                              cat3_html += '<option value="'+ v.id+'">'+ v.class_name+'</option>';
                          }
                          $("#cat_id_3").html(cat3_html);
                      });
                  });

              });
		}
		
		/**
		 * 三级分类赋值 
		 */
		this.sendValue = function (show_cat) {
			if(!show_cat) {
				return false;
			}
			 show_cat = JSON.parse(show_cat);
			 
             return $.each(show_cat,function(show_i,show_v){
            	 if(!show_v) {
            		 return ;
            	 }
                 if(show_i=="three"){
                     cat33_html = '<option value="'+ show_v.id+'" selected="selected">'+ show_v.class_name+'</option>';
                     $("#cat_id_3").html(cat33_html);
                 }
                 if(show_i=="two"){
                     cat22_html = '<option value="'+ show_v.id+'" selected="selected">'+ show_v.class_name+'</option>';
                     $("#cat_id_2").html(cat22_html);
                 }
                 if(show_i=="one"){
                     cat11_html = '<option value="'+ show_v.id+'" selected="selected">'+ show_v.class_name+'</option>';
                     $("#cat_id").val(show_v.id);
                 }
             });
		}
		/**
		 * 是否上架推荐 
		 */
		this.isShelves = function(url, event) {
			var status = event.getAttribute('data-status');
			if(!this.isNumer(status)) {
				layer.msg('未知错误');
				return false;
			}
			var init = status == 0 ? 1 : 0;
			
			var json = {};
			var img = init == 0 ? '/img/cancel.png' : "/img/yes.png";
			json[event.getAttribute('key')] = init;
			json['id'] = event.getAttribute('data-id');
			return this.ajax(url, json, function(res){
				if(res.status == 1){
					layer.msg(res.message);
					event.setAttribute('src', GoodsOption.imgUrl+img);
					event.setAttribute('data-status', init);
				}
			});
		}
		
		/**
		 * 商品属性添加 
		 */
		this.selectGoodsAttribute = function (event) {
			
			var url = event.getAttribute('url');
			
			if (!this.isNumer(this.parimayKey)) {
				layer.msg('添加失败');
				return false;
			}
			
			var id = event.value;
			this.dataType = '';
			this.post(url, {id : id, goodsId: this.parimayKey}, function (res) {
				$("#goods_attr_table tr:gt(0)").remove()
                $("#goods_attr_table").append(res.data);
			})
			
		}
		
		/**
		 * 合并单元格 
		 */
		
		this.mergeCell = function(id) {
			var tab = document.getElementById(id); //要合并的tableID
	        var maxCol = 2, val, count, start;  //maxCol：合并单元格作用到多少列  
	        if (tab != null) {
	            for (var col = maxCol - 1; col >= 0; col--) {
	                count = 1;
	                val = "";
	                for (var i = 0; i < tab.rows.length; i++) {
	                    if (val == tab.rows[i].cells[col].innerHTML) {
	                        count++;
	                    } else {
	                        if (count > 1) { //合并
	                            start = i - count;
	                            tab.rows[start].cells[col].rowSpan = count;
	                            for (var j = start + 1; j < i; j++) {
	                                tab.rows[j].cells[col].style.display = "none";
	                            }
	                            count = 1;
	                        }
	                        val = tab.rows[i].cells[col].innerHTML;
	                    }
	                }
	                if (count > 1) { //合并，最后几行相同的情况下
	                    start = i - count;
	                    tab.rows[start].cells[col].rowSpan = count;
	                    for (var j = start + 1; j < i; j++) {
	                        tab.rows[j].cells[col].style.display = "none";
	                    }
	                }
	            }
	        }
		}
	}

	Goods.prototype = Tool;
	window.GoodsOption = new Goods();
})(window);
window.onload = function() {
	
	GoodsOption.init("tabSelect");
}

