
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
 * 商品热词 
 */
var hot_words = {
	isHaveClass : false,
	//热词编辑添加 
	edit_or_add : function(url, title)
	{
		if(!url)
		{
			alert('来自网页的消息,未知错误');
			return false;
		}
		title = title ? title : '添加关键词';
		parent.layer.open({
			type: 2,
			shadeClose: true,
			shade: 0.5,
			area: ['500px', '400px'],
			title: title,
			content: url,
		});
	},
	checkForm : function(event)
	{
		var value = $(event).find('select[name="goods_class_id"] option:selected').val();
		var hot_words = $(event).find('input[name="hot_words"]').val();
		var ishide	  = parseInt($(event).find('select[name="is_hide"] option:selected').val());
		
		return true;
	},
	//获取form数据
	getForm :function(event)
	{
		var formData = {};
		
		$('select').each(function(){
			formData[$(this).attr('name')] = $(this).find('option:selected').val();
		});
		
		$('input').each(function(){
			formData[$(this).attr('name')] = $(this).val();
		});
		var flag = 0;
		for(var i in formData)
		{
			if(!formData[i])
			{
				flag++;
			}
		}
		return flag === 0 ? formData : false;
	},
	
	CloseWebPage : function () {
	    var browserName = navigator.appName;
	    if (browserName=="Netscape") {
	        window.open('', '_self', '');
	        window.close();
	    }
	    else {
	        if (browserName == "Microsoft Internet Explorer"){
	            window.opener = "whocares";
	            window.opener = null;
	            window.open('', '_top');
	            window.close();
	        }
	    }
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
	
	deleteHotWords : function(id,url)
	{
		if (!isNaN(id))
		{
			this.ajax(url, {id : id});
		}
	},
	
	deleteConfigClass : function (id, url, isURL)
    {
		this.haveClass(id, isURL);
        if (this.isHaveClass)
        {
        	layer.confirm('您确定要删除该分类吗，该分类下的子分类也将删除？', {
        		btn: ['确定','取消'], //按钮
        		shade: false //不显示遮罩
        		}, function(){
        			hot_words.deleteHotWords(id, url);
        		}, function(){
        		layer.msg('已放弃删除', {shift: 6});
        	});
        }
        else
        {
        	this.deleteHotWords(id, url);
        }
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
					alert(res.message);
					var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
					index ? parent.layer.close(index) : false;
					window.parent.iframe.location.reload();
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
	haveClass : function(id, url)
	{
		$.ajax({
			url  		: url,
			type 		: 'post',
			data		: {id :id},
			async		: false,
			dataType	: 'json',
			success		: function(res)
			{
				hot_words.isHaveClass = res.status == 1 ? true : false;
			}
		})
	},
	
	tree : function(url,  id)
	{
		$.ajax({
			url  		: url,
			type 		: 'post',
			data		: null,
			dataType	: 'json',
			success		: function(res)
			{
				if(res.data)
				{
					var fHTML = '';
					var data = res.data;
					for (var i in data)
					{
						var html=new treeMenu(data[i].children).init(0);
						fHTML += '<tr class="fClass" ><td height="50" align="center"><div style="padding-left:50px;">'+data[i].id+"</div></td>" +
								'<td><div style="padding-left:50px;" align="center">'+data[i].config_class_name+'</div></td></tr></tr>'+html;
					}
					$(".dataHTML").append(fHTML);
					$('.dataHTML').find('.samep').each(function(i){
						
							var value = $(this).eq(i).find('div').text();
							var next  = $(this).parents('.pop').siblings('.pop').find('.samep').eq(i).find('div').text();
						
							console.log(value+"==="+next);
							if(value == next)
							{
								$(this).parents('.pop').remove();
								$(this).parents('.pop').remove()
							}
					});
					
					$('.dataHTML').find('.fClass').each(function(i){
						$(this).next().remove();
					});
					
				}
			}
		});
	},
	
	creatSelectTree : function (id, zNodes) {
		var setting = {
				view: {
					dblClickExpand: false
				},
				data: {
					simpleData: {
						enable: true
					}
				},
				callback: {
					beforeClick: hot_words.beforeClick,
					onClick: hot_words.onClick
				}
			};
		
			$.fn.zTree.init($("#treeDemo"), setting, zNodes);

	},
	beforeClick : function (treeId, treeNode) {
		var check = (treeNode && !treeNode.isParent);
		if (!check) alert("只能选择城市...");
		return check;
	},

	onClick : function (e, treeId, treeNode) {
		var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
		nodes = zTree.getSelectedNodes(),
		v = "";
		nodes.sort(function compare(a,b){return a.id-b.id;});
		for (var i=0, l=nodes.length; i<l; i++) {
			v += nodes[i].name + ",";
		}
		if (v.length > 0 ) v = v.substring(0, v.length-1);
		var cityObj = $("#citySel");
		cityObj.attr("value", v);
	},
	showMenu : function () {
		var cityObj = $("#citySel");
		var cityOffset = $("#citySel").offset();
		$("#menuContent").css({left:cityOffset.left + "px", top:cityOffset.top + cityObj.outerHeight() + "px"}).slideDown("fast");

		$("body").bind("mousedown", onBodyDown);
	},
	
	hideMenu : function () {
		$("#menuContent").fadeOut("fast");
		$("body").unbind("mousedown", onBodyDown);
	},
	onBodyDown : function (event) {
		if (!(event.target.id == "menuBtn" || event.target.id == "menuContent" || $(event.target).parents("#menuContent").length>0)) {
			this.hideMenu();
		}
	},
};
function treeMenu(a){
    this.tree=a||[];
    this.groups={};
    this.html = '';
};
treeMenu.prototype={
    init:function(pid){
       return this.group(this.tree);
    },
    group:function(tree){
	    for(var i in tree)
	    {
	    	if(tree[i] instanceof Object)
	    	{
	    		this.group(tree[i]);
	    	}
	    	else
	    	{
	    		if(typeof tree.id != undefined && typeof tree.config_class_name　!= undefined )
	    		{
	    			var ss = (tree.id == tree[i]) ? '&nbsp;&nbsp;&nbsp;|———— ;':'|——';
	    			 this.html += '<tr height="50" class="hot pop" ><td class="samep"><div style="padding-left:50px;" align="center">'+tree.id +'</div></td>'+
	    		 		'<td><div style="padding-left:50px;" >'+ss+tree.config_class_name+'</div></td></tr>';
	    		}
	    	}
	    }
	    return this.html;
    },
    getDom:function(a){
        if(!a){return ''}
        var html='\n<ul >\n';
        for(var i=0;i<a.length;i++){
            html+='<li><a href="#">'+a[i].name+'</a>';
            html+=this.getDom(this.groups[a[i].id]);
            html+='</li>\n';
        };
        html+='</ul>\n';
        return html;
    }
};
