//验证非商城商品维修提交
function check_repair(){
    var tuihuo_case = $('#tuihuo_case').val();
    var update_time = $('#update_time').val();
    var address = $('#address').val();
    var tel = $('#tel').val();
    if(tuihuo_case == ''){
	    layer.tips('请填写维修项目', '#tuihuo_case');
	    return false;
    }
    if(update_time == ''){
	    layer.tips('请填写维修时间', '#update_time');
	    return false;
    }
    if(address == ''){
	    layer.tips('请填写维修地点', '#address');
	    return false;
    }
    if(tel == ''){
	    layer.tips('请填写联系电话', '#tel');
	    return false;
    }
    if(!/^1[34578]\d{9}$/.test(tel)){
  		layer.tips('请输入正确的手机号', '#tel');
		return false;
	}
    return true;
}
//验证商城商品维修提交
function check_repair_ys(){
    var tuihuo_case = $('#tuihuo_case').val();
    var address = $('#address').val();
    var tel = $('#tel').val();
    var explain = $('#explain').val();
    if(tuihuo_case == ''){
	    layer.tips('请填写维修项目', '#tuihuo_case');
	    return false;
    }
    if(address == ''){
	    layer.tips('请填写维修地点', '#address');
	    return false;
    }
    if(tel == ''){
	    layer.tips('请填写联系电话', '#tel');
	    return false;
    }
    if(!/^1[34578]\d{9}$/.test(tel)){
  		layer.tips('请输入正确的手机号', '#tel');
		return false;
	}
	if(explain == ''){
	    layer.tips('请填写维修描述', '#explain');
	    return false;
    }
    return true;
}

// //搜索订单商品
// var aLi = $('.content-main .nav span input[type=button]');
//     aLi.on('click',function(){
//         var search = $('#search').val();
//         $.ajax({
//             url : 'check_order_ajax',
//             dataType : "json",
//             type : 'post',
//             data : {data:search},
//             success:function(data){ 
//             console.log(data);             
//                 // $('.mcole-section-wrap .content-main li').remove(); 
//                 // for (var i = 0; i < data.length; i++) { 
//                 // console.log(data);                                                                                                                                  
//                 //     $('.mcole-section-wrap .content-main').append('<li class="fl" id="del'+data[i].id+'">'+'<div class="screenshot">'+'<a href="javascript:;">'+'<img src="'+data[i].images+'">'+'</a'+'></div>'+'<div class="products-description">'+'<p>'+'<a href="javascript:;">'+data[i].title+' </a>'+'</p>'+'<span class="products-prices">¥ '+data[i].price_member+'</span>'+'</div>'+'<div class="products-operation clearfix">'+'<input type="button" value="加入购物车" class="fl search-text">'+'<input type="button" name="del" value="删除" data-value="'+data[i].id+'" class="fl search-btn">'+'</div>'+'</li>')
//                 // };
//             },
//             error:function(){
              
//             },
//         });
//     });