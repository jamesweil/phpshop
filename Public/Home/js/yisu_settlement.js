
$(function(){
	$('#cart').on('mouseenter',function(){
		$('#cart .catr_none').css('display','block');
		$('#cart .catr_block').addClass('active');
	}).on('mouseleave',function(){
		$('#cart .catr_none').css('display','none');
		$('#cart .catr_block').removeClass('active');
	});
	$('#nav_li1').on('mouseenter',function(){
		$(this).addClass('active');
		$('#menu').css('display','block');
	}).on('mouseleave',function(){
		$(this).removeClass('active');
		$('#menu').css('display','none');
	});
	$('#menu li').on('mouseenter',function(){
		$('#menu li').removeClass('active');
		$(this).addClass('active');
		$('#menu li ul').eq($(this).index()).css('display','block');
	}).on('mouseleave',function(){
		$('#menu li ul').css('display','none');
	})
	$('#menu').on('mouseleave',function(){
		$('#option li').css('display','none');
		$('#menu li').removeClass('active');
	});
	//放大镜
	function getPos(obj){
        var l = 0;
        var t = 0;

        while(obj){
            l += obj.offsetLeft;
            t += obj.offsetTop;

            obj = obj.offsetParent;
        }
        return {left: l, top: t};
    }
    var oSmall = document.getElementById('small');
    var oSpan = oSmall.children[0];
    var oImg1 = oSmall.children[1];
    var oBig = document.getElementById('big');
    var oImg = oBig.children[0];
    oSmall.onmouseover = function(){
        oSpan.style.display = 'block';
        oBig.style.display = 'block';
    };
    oSmall.onmousemove = function(ev){
        var oEvent = ev || event;
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
        var l = oEvent.clientX-getPos(oSmall).left-oSpan.offsetWidth/2+scrollLeft;
        var t = oEvent.clientY-getPos(oSmall).top-oSpan.offsetHeight/2+scrollTop;
        if(l <= 0){
            l = 0;
        }
        if(t <= 0){
            t = 0;
        }
        if(l >= oSmall.offsetWidth-oSpan.offsetWidth){
            l = oSmall.offsetWidth-oSpan.offsetWidth;
        }
        if(t >= oSmall.offsetHeight-oSpan.offsetHeight){
            t = oSmall.offsetHeight-oSpan.offsetHeight;
        }
        oSpan.style.left = l+'px';
        oSpan.style.top = t+'px';

        var l2 = -l/(oSmall.offsetWidth-oSpan.offsetWidth)*(oImg.offsetWidth-oBig.offsetWidth);
        var t2 = -t/(oSmall.offsetHeight-oSpan.offsetHeight)*(oImg.offsetHeight-oBig.offsetHeight);
        oImg.style.left = l2+'px';
        oImg.style.top = t2+'px';
    };
    oSmall.onmouseout=function(){
        oSpan.style.display = 'none';
        oBig.style.display = 'none';
    };
    //滑入换图
    var aA = document.querySelectorAll('#details_con_image a');
    for(var i = 0; i < aA.length; i++){
    	aA[i].index = i;
    	aA[i].onmouseenter = function(){
    		var oImg_1 = aA[this.index].children[0];
    		oImg1.src = oImg_1.src;
			oImg.src = oImg_1.src;
    	}
    }
    //加减
    var b = 1;   
    $('#purchase input').on('input',function(){
    	
    	if(this.value <= 0){
    		$(this).val(1);
    	}else{
    		b = $(this).val();
    	}
    	
    }).on('keydown',function(event){
    	if((event.keyCode<48 || event.keyCode>57) && event.keyCode!=8){
            return false;
        }
    });
    $('#purchase a').on('click',function(){
    	if($(this).index() <= 1){
    		b--;
    	}else{
    		b++;
    	};
    	if(b <= 1){
    		b = 1;
    		$(this).addClass('active');
    	}else{
    		$('#purchase a').removeClass('active');
    	}
    	$('#purchase input').val(b);
    	$('#order').find('input[name="goods_num"]').val(b);
    });

    //内容切换选项卡
    $('#sett_con_fr_top li').on('click',function(){
        $('#sett_con_fr_top li').removeClass('active').eq($(this).index()).addClass('active');
        $('#sett_con_fr_con .centent').removeClass('active').eq($(this).index()).addClass('active');
    });
    
    //收藏
	function collects(){
		var urlCollection="{:U('User/collection')}";
		$('.collection').click(function(){
			var _data=$('#ww').serialize();
			console.log(_data);
			$.post(urlCollection,_data,function(data){
				alert(data.message);
			})
		});
	}

    
    //添加购物车
    $('.addCart').click(function(){

    	/*var addCartData = {};
    	$(this).siblings('.collection').find('input[type="hidden"]').each(function(){

    		if ($(this).attr('addCart')) {
    			addCartData[$(this).attr('name')] = $(this).val();
    		}
    	})
    	addCartData['goods_num'] = $("#nums").find('input[name="goods_num"]').val();
    	if(addCartData != '' && addCartData != null)
		{
    		for(var i in addCartData) {
    			if (!addCartData[i] && i!='taocan_name')
    			{
    				toastr.error('数据有误', '来自收藏的消息');
    				return false;
    			}
    		}
    		return ajax(urlAddCart, addCartData) === true ? true : false;
		}
    	toastr.error('无法收藏，数据有误', '来自收藏的消息');
    	return false;*/
    });
});

function ajax(url, data)
{
	$.ajax({
		url  	:  url,
		type 	: 'post',
		data 	: data,
		success : function(res)
		{
			if(res.status == 1) {
				toastr.success(res.message, '来之网页的消息');
				return true;
			} else {
				toastr.error(res.message, '来之网页的消息');
				
				if(res['data'] != null) {
					setInterval(function(){
						location.href = res.data.url;
					}, 3000);
				}
				return false;
			}
		}
	});
	return true;
}

//

var order = {
	getChildNodes : function(ele){
	   if(!(ele instanceof Object))
	   {
		   return [];
	   }
	   var childArr=ele.children || ele.childNodes,
	         childArrTem=new Array();  //  临时数组，用来存储符合条件的节点
	    for(var i=0,len=childArr.length;i<len;i++){
	        if(childArr[i].nodeType==1){
	            childArrTem.push(childArr[i]);
	        }
	    }
	    return childArrTem;
	},	
	
	checkOrder: function (event)
	{
		//获取form 节点数据
		var formData = this.getChildNodes(event);
		var flag 	 = 0;
		if (formData.length !== 0) {
			for (var i in formData) {
				
				if (!formData[i].value) {
					return false;
				} else {
					flag++;
				}
			}
			if (flag === formData.length)
			{
				return true;
			}
		}
		return false;
	},
	
	isArray : function(v){
        return toString.apply(v) === '[object Array]';
	}

}