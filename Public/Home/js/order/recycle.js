//订单回收站---还原订单
$('.myderCentent .con .conFr .details ').on('click','.recycle',function(){
    var id = $(this).attr('data-value');
    parent.layer.confirm('真的要还原订单吗？', {
        btn: ['确认','取消'], //按钮
        shade: 0.5 //显示遮罩
    }, function(){
        $.post("/index.php/Home/Order/recycle_reduction", { "id": id},function(data){
            if(data == 1){
                parent.layer.msg('还原成功', { icon: 1, time: 1000 }, function(){
                        $("#del"+id).remove();
                    });
            }else{
                parent.layer.msg('还原失败', {icon: 2, time: 2000 }); 
            }
        }, "json");
    },function(){
        // $("#del"+id+" td").css('border-top','0');
        // $("#del"+id+" td").css('border-bottom','1px solid #EFEFEF');
    });
})
//订单回收站---删除订单
$('.myderCentent .ctitle ').on('click','.recycle',function(){
    var id = $(this).attr('data-value');
    parent.layer.confirm('真的要删除订单吗？', {
        btn: ['确认','取消'], //按钮
        shade: 0.5 //显示遮罩
    }, function(){
        $.post("/index.php/Home/Order/recycle_order_del", { "id": id},function(data){
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
})

//添加收货地址验证 
function check_add(){    
    var realname = $('#realname').val();
    var prov = $('#province').val();
    var city = $('#city').val();
    var dist = $('#area').val();
    var address  = $('#address').val();
    var mobile   = $('#mobile').val();
    var email  = $('#email').val();    
    var alias = $('#alias').val();
    if(realname == ''){
        layer.tips('请填写收货人姓名!', '#realname',{icon: 5});
        return false;
    }
    if(prov == '请选择省份'){        
        return false;
    }
    if(city == '请选择城市'){        
        return false;
    }
    if(dist == '请选择地区'){        
        return false;
    }
    if(address == ''){
        layer.tips('请填写详细地址!', '#address');
        return false;
    }
     if(mobile == ''){
        layer.tips('请填写收货人手机号码!', '#mobile');
        return false;
    }
    if(!/^1[34578]\d{9}$/.test(mobile)){
        layer.tips('请输入正确的手机号!', '#mobile');
        return false;
    }
    if(email == ''){
        layer.tips('请填写邮箱!', '#email');
        return false;
    } 
    if(!/^(\w)+(\.\w+)*@(\w)+((\.\w{2,3}){1,3})$/.test(email)){
        layer.tips('请输入正确的邮箱!', '#email');
        return false;
    }  
     if(alias == ''){
        layer.tips('请地址别名!', '#alias');
        return false;
    }  
    $.post( "/index.php/Home/Order/cart_address_add",{"realname":realname,"prov":prov,"city":city,"dist":dist,"address":address,"mobile":mobile,"email":email,"alias":alias},function(data){
                if(data == 1){
                    layer.msg('添加成功，正在跳转中...',{icon: 1,time: 2000,shade: [0.8, '#393D49']},function(){
                        window.location.reload();    //刷新父页面
                    });
                }else{
                    layer.msg('添加失败，请重新输入',{icon: 2,time: 2000},function(){
                        window.location.reload();
                    });
                }
            }, "json");
}