//<!--樊恩材新加弹出框JS-->

function Pr(){
	var  Popover = $(".addCommd .Popover"),
			aC = $(".add_Commodity"),
			aCT = $(".add_Commodity_Title a"),
			acb = $(".add_Commodity_bg");
	Popover.click(function(){
		aC.addClass("active");
		acb.addClass("active");
	});
	aCT.click(function(){
		aC.removeClass("active");
		acb.removeClass("active");
	});
	acb.click(function(){
		aC.removeClass("active");
		acb.removeClass("active");
	})
	// bi.click(function(){
	// 	aC.removeClass("active");
	// 	acb.removeClass("active");
	// })
}Pr();

$(function() {
    $("#checkAll").click(function() {
        $('input[name="goods_id[]"]').attr("checked",this.checked); 
    });
    var $subBox = $("input[name='goods_id[]']");
    $subBox.click(function(){
        $("#checkAll").attr("checked",$subBox.length == $("input[name='goods_id[]']:checked").length ? true : false);
    });
});
//删除商品
$(".delete_list").live("click",function(){
	var id = $(this).attr('data-value');
	alert(id)
    var _this=$(this);
    parent.layer.confirm('真的要删除吗？', {
        btn: ['确认','取消'], //按钮
        shade: 0.5 //显示遮罩
    }, function(){
        $.post("/index.php/Home/SpecialBusiness/goods_del", { "id": id},function(data){
            if(data == 1){
                parent.layer.msg('删除成功', { icon: 1, time: 1000 }, function(){
                        $("#del"+id).remove();
                    });
            }else{
                parent.layer.msg('删除失败', {icon: 2, time: 2000 }); 
            }
        }, "json");
    },function(){
        // $("#del"+id+" td").css('border-top','0');
        // $("#del"+id+" td").css('border-bottom','1px solid #EFEFEF');
    });
});
//一级分类选择
$(".class").bind("change",function(){
    var class_id = $(this).val();
    if (class_id != '') {
    	var page = $('#page').val();
	    $.ajax({
	        url : 'class_goods_ajax',
	        dataType : "json",
	        type : 'post',
	        data : {class_id:class_id,page:page},
	        success:function(data){
	            var page=data['page'];
	            var class_id=data['class_id'];
	            var count = data['count'];
	            $('.add_fenye').html('<input type="hidden" id="page" value="0" data-value="'+class_id+'"><span class="fl">共有'+count+'条数据</span><a href="javascript:;" id="Prev" class="fl Prev">上一页</a><a href="javascript:;" id="next" class="fl next">下一页</a>');
	            // $('#page').val(page); 
	            if (data['class'] != '') {
                    $('.class_one option').remove(); 
		            for (var i = 0; i < data['class'].length; i++) {                                                                                                                                   
		                $('.class_one').append('<option value ="'+data['class'][i].id+'">'+data['class'][i].class_name+'</option>');
		            };
	            }else{
	            	$('.class_one').html('<select class="add_Commodity_Filter_select class_one"><option value ="">请选择</option></select>');
	            	$('.class_two').html('<select class="add_Commodity_Filter_select class_one"><option value ="">请选择</option></select>');
	            };
	            $('.add_Commodity_Content .add_Commodity_Content_particular').remove();
	            for (var i=0; i<data['data'].length; i++) {
	            	$('.add_Commodity_Content').append('<div class="add_Commodity_Content_particular"><div class="add_input"><input  name="danxuan" type="radio" value="'+data['data'][i].goods_id+'" /></div><div class="add_Countastic">'+data['data'][i].goods_id+'</div><div class="add_Product">'+data['data'][i].title+'</div><div class="add_Flyers">'+data['data'][i].class_name+'</div><div class="add_unit">$'+data['data'][i].price_member+'</div><div class="add_spimg"><img src="'+data['data'][i].images+'" alt=""/></div></div>');
	            }; 	
	            $(".class_one").trigger("change");            
	        },
	        error:function(){
	            return false;
	        },
	    });
    }else{
    	return false;
    };    
});
//二级分类选择
$(".class_one").bind("change",function(){
    var class_id = $(this).val();
    if (class_id != '') {
    	var page = $('#page').val();
	    $.ajax({
	        url : 'class_goods_ajax',
	        dataType : "json",
	        type : 'post',
	        data : {class_id:class_id,page:page},
	        success:function(data){
	            var page=data['page'];
	            var class_id = data['class_id'];
	            var count = data['count'];
	            $('.add_fenye').html('<input type="hidden" id="page" value="0" data-value="'+class_id+'"><span class="fl">共有'+count+'条数据</span><a href="javascript:;" id="Prev" class="fl Prev">上一页</a><a href="javascript:;" id="next" class="fl next">下一页</a>');
	            // $('#page').val(page); 
	            if (data['class']!='') {
	            	$('.class_two option').remove(); 
		            for (var i = 0; i < data['class'].length; i++) {                                                                                                                                   
		                $('.class_two').append('<option value ="'+data['class'][i].id+'">'+data['class'][i].class_name+'</option>');
		            };
	            }else{
	            	$('.class_two').html('<select class="add_Commodity_Filter_select class_one"><option value ="">请选择</option></select>');
	            }; 	                       	
	            $('.add_Commodity_Content .add_Commodity_Content_particular').remove();
	            for (var i=0; i<data['data'].length; i++) {
	            	$('.add_Commodity_Content').append('<div class="add_Commodity_Content_particular"><div class="add_input"><input  name="danxuan" type="radio" value="'+data['data'][i].goods_id+'" /></div><div class="add_Countastic">'+data['data'][i].goods_id+'</div><div class="add_Product">'+data['data'][i].title+'</div><div class="add_Flyers">'+data['data'][i].class_name+'</div><div class="add_unit">$'+data['data'][i].price_member+'</div><div class="add_spimg"><img src="'+data['data'][i].images+'" alt=""/></div></div>');
	            };
	            $(".class_two").trigger("change");
	        },
	        error:function(){
	            return false;
	        },
	    });
    }else{
    	return false;
    };    
});
//三级分类选择
$(".class_two").bind("change",function(){
    var class_id = $(this).val();
    if (class_id != '') {
    	var page = $('#page').val();
	    $.ajax({
	        url : 'class_goods_ajax',
	        dataType : "json",
	        type : 'post',
	        data : {class_id:class_id,page:page},
	        success:function(data){
	            var page=data['page'];
	            var class_id = data['class_id'];
	            var count = data['count'];
	            $('.add_fenye').html('<input type="hidden" id="page" value="0" data-value="'+class_id+'"><span class="fl">共有'+count+'条数据</span><a href="javascript:;" id="Prev" class="fl Prev">上一页</a><a href="javascript:;" id="next" class="fl next">下一页</a>');
	            $('.add_Commodity_Content .add_Commodity_Content_particular').remove();
	            for (var i=0; i<data['data'].length; i++) {
	            	$('.add_Commodity_Content').append('<div class="add_Commodity_Content_particular"><div class="add_input"><input  name="danxuan" type="radio" value="'+data['data'][i].goods_id+'"/></div><div class="add_Countastic">'+data['data'][i].goods_id+'</div><div class="add_Product">'+data['data'][i].title+'</div><div class="add_Flyers">'+data['data'][i].class_name+'</div><div class="add_unit">$'+data['data'][i].price_member+'</div><div class="add_spimg"><img src="'+data['data'][i].images+'" alt=""/></div></div>');
	            };
	        },
	        error:function(){
	            return false;
	        },
	    });
    }else{
    	return false;
    };    
});
//上一页
$('.add_Commodity ').on('click','.add_fenye .Prev',function(){
   var class_id = $('#page').attr('data-value');
   var page = $('#page').val();
   // var page = parseInt(page)-6;
   // alert(page);
   $.ajax({
        url : 'goods_prve_ajax',
        dataType : "json",
        type : 'post',
        data : {class_id:class_id,page:page},
        success:function(data){
            var page=data['page'];
            var class_id=data['class_id'];
            var count = data['count'];
            $('.add_fenye').html('<input type="hidden" id="page" value="'+page+'" data-value="'+class_id+'"><span class="fl">共有'+count+'条数据</span><a href="javascript:;" id="Prev" class="fl Prev">上一页</a><a href="javascript:;" id="next" class="fl next">下一页</a>');
            // $('#page').val(page);  	            
        	$('.class_one option').remove(); 
            for (var i = 0; i < data['class'].length; i++) {                                                                                                                                   
                $('.class_one').append('<option value ="'+data['class'][i].id+'">'+data['class'][i].class_name+'</option>');
            };
            $('.add_Commodity_Content .add_Commodity_Content_particular').remove();
            for (var i=0; i<data['data'].length; i++) {
            	$('.add_Commodity_Content').append('<div class="add_Commodity_Content_particular"><div class="add_input"><input  name="danxuan" type="radio" value="'+data['data'][i].goods_id+'" /></div><div class="add_Countastic">'+data['data'][i].goods_id+'</div><div class="add_Product">'+data['data'][i].title+'</div><div class="add_Flyers">'+data['data'][i].class_name+'</div><div class="add_unit">$'+data['data'][i].price_member+'</div><div class="add_spimg"><img src="'+data['data'][i].images+'" alt=""/></div></div>');
            };
        },
        error:function(){
            return false;
        },
    });
});
//下一页
$('.add_Commodity ').on('click','.add_fenye .next',function(){
   var class_id = $('#page').attr('data-value');
   var page = $('#page').val();
   // var page = parseInt(page)+6;
   // alert(page);
   $.ajax({
        url : 'goods_next_ajax',
        dataType : "json",
        type : 'post',
        data : {class_id:class_id,page:page},
        success:function(data){
            var page=data['page'];
            var class_id=data['class_id'];
            var count = data['count'];
            $('.add_fenye').html('<input type="hidden" id="page" value="'+page+'" data-value="'+class_id+'"><span class="fl">共有'+count+'条数据</span><a href="javascript:;" id="Prev" class="fl Prev">上一页</a><a href="javascript:;" id="next" class="fl next">下一页</a>');
            // $('#page').val(page);  	            
        	$('.class_one option').remove(); 
            for (var i = 0; i < data['class'].length; i++) {                                                                                                                                   
                $('.class_one').append('<option value ="'+data['class'][i].id+'">'+data['class'][i].class_name+'</option>');
            };
            $('.add_Commodity_Content .add_Commodity_Content_particular').remove();
            for (var i=0; i<data['data'].length; i++) {
            	$('.add_Commodity_Content').append('<div class="add_Commodity_Content_particular"><div class="add_input"><input  name="danxuan" type="radio" value="'+data['data'][i].goods_id+'"/></div><div class="add_Countastic">'+data['data'][i].goods_id+'</div><div class="add_Product">'+data['data'][i].title+'</div><div class="add_Flyers">'+data['data'][i].class_name+'</div><div class="add_unit">$'+data['data'][i].price_member+'</div><div class="add_spimg"><img src="'+data['data'][i].images+'" alt=""/></div></div>');
            };
        },
        error:function(){
            return false;
        },
    });
})
//添加商品
function check_goods_add(){
    var goods_id = $('input[name=danxuan]:checked').val();
    var num = $('#num').val();
    var price = $('#price').val();
    var productexplain = $('#productexplain').val();
    if(goods_id == null){
	    layer.tips('请选择商品', '#xuanze',{tips:1});
	    return false;
    }
    if(num == ''){
  		layer.tips('请填写数量!', '#num');
		return false;
	}
    if(price == ''){
	    layer.tips('请填写预算单价', '#price');
	    return false;
    }
    if(productexplain == ''){
	    layer.tips('请填写预算说明!', '#productexplain');
	    return false;
    }
    return true;
}
//商品价格
var oNumber = null;
var iNow = 0;
var oInput = $('.requts-content-wrap .goods_list td input');
var priceNumber = null;

for(var i = 0; i < oInput.length; i++){
	oInput.eq(i).on('click',function(){
		if($(this).attr('checked') == 'checked'){
			oNumber = parseInt($(this).parent().parent().children('.number').text()) * parseInt($(this).parent().parent().children('.price').text());
			priceNumber += oNumber;
			$('.price-number').val(priceNumber + '元');
			iNow ++;
		}else{
			oNumber = parseInt($(this).parent().parent().children('.number').text()) * parseInt($(this).parent().parent().children('.price').text());
			priceNumber -= oNumber;
			$('.price-number').val(priceNumber + '元');
			iNow --;
		}
	});
}
$('.parentNode').on('click',function(){
	if($(this).attr('checked') == 'checked'){
		priceNumber = 0;
		for(var i = 0; i < oInput.length; i++){
			priceNumber += parseInt(oInput.eq(i).parent().parent().children('.number').text()) * parseInt(oInput.eq(i).parent().parent().children('.price').text());
			$('.price-number').val(priceNumber + '元');
		}
		iNow = oInput.length;
	}else{
		priceNumber = 0;
		$('.price-number').val(priceNumber + '元');
		iNow = 0;
	}
});
//提交
$('.submit-main .one').on('click',function(){
	$('.submit-main .state').val(2);
	$('.form').submit();
})
//保存
$('.submit-main .two').on('click',function(){
	$('.submit-main .state').val(1);
	$('.form').submit();
})
//提交采购需求
function purchase(){
	var purchase_title = $('#purchase_title').val();
	var purchase_type = $('input[name=purchase_type]:checked').val();
	var total_price = $('#total_price').val();
	var contacts = $('#contacts').val();
	var tel = $('#tel').val();
	var overtime = $('#overtime').val();
	var pay_type = $('input[name=pay_type]:checked').val();
	var invoice = $('input[name=invoice]:checked').val();
	var explain = $('#explain').val();
	if (purchase_title == '') {
		layer.msg('请填写采购标题!');
		return false;
	};
	if(purchase_type == null){
        layer.msg('请选择需求类型!');
        return false;
    }
    if(iNow == 0){
        layer.msg('请选择商品!');
        return false;
    }
    if(contacts == ''){
        layer.msg('请填写联系人姓名!');
        return false;
    }
    if(tel == ''){
        layer.msg('请填写联系人电话!');
        return false;
    }
    if(!/^1[34578]\d{9}$/.test(tel)){
        layer.msg('请输入正确的手机号!');
       return false;
    }
    if(overtime == ''){
        layer.msg('请选择收货日期!');
        return false;
    }
    if(pay_type == null){
        layer.msg('请选择支付方式!');
        return false;
    }
    if(invoice == null){
        layer.msg('请选择发票信息!');
        return false;
    }
    if(explain == ''){
        layer.msg('请填写说明!');
        return false;
    }
    return true;
}

//详情页面提交
$('.submit-main .three').on('click',function(){
	var id = $(this).attr('data-value');
    var _this=$(this);
    parent.layer.confirm('真的要提交吗？', {
        btn: ['确认','取消'], //按钮
        shade: 0.5 //显示遮罩
    }, function(){
        $.post("/index.php/Home/SpecialBusiness/purchase_requisition_details", { "id": id},function(data){
            if(data == 1){
                parent.layer.msg('提交成功!', { icon: 1, time: 1000 }, function(){
                        window.location.href="/index.php/Home/SpecialBusiness/purchase_requisition";
                    });
            }else{
                parent.layer.msg('提交失败', {icon: 2, time: 2000 }); 
            }
        }, "json");
    },function(){
        // $("#del"+id+" td").css('border-top','0');
        // $("#del"+id+" td").css('border-bottom','1px solid #EFEFEF');
    });
})