//团购订单--取消订单
$('.mordrMain .myderCentent .con .conFr .details .cancel').on('click',function(){
	var id = $(this).attr('data');
	parent.layer.confirm('真的要取消吗？', {
        btn: ['确认','取消'], //按钮
        shade: 0.5 //显示遮罩
    }, function(){
        $.post("/index.php/Home/OrderGroup/cancel_order", { "id": id},function(data){
            if(data == 1){
                parent.layer.msg('取消成功', { icon: 1, time: 2000 }, function(){
                        $("#del"+id).remove();
                    });
            }else{
                parent.layer.msg('取消失败', {icon: 2, time: 2000 }); 
            }
        }, "json");
    },function(){
    //     $("#del"+id+" td").css('border-top','0');
    //     $("#del"+id+" td").css('border-bottom','1px solid #EFEFEF');
    });
})

//团购订单--删除订单
$('.mordrMain .myderCentent .ctitle .del').on('click',function(){
	var id = $(this).attr('date');
	parent.layer.confirm('真的要删除吗？', {
        btn: ['确认','取消'], //按钮
        shade: 0.5 //显示遮罩
    }, function(){
        $.post("/index.php/Home/OrderGroup/order_del", { "id": id},function(data){
            if(data == 1){
                parent.layer.msg('删除成功', { icon: 1, time: 2000 }, function(){
                        $("#del"+id).remove();
                    });
            }else{
                parent.layer.msg('删除失败', {icon: 2, time: 2000 }); 
            }
        }, "json");
    },function(){
    //     $("#del"+id+" td").css('border-top','0');
    //     $("#del"+id+" td").css('border-bottom','1px solid #EFEFEF');
    });
})