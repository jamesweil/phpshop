//申请退回押金
$('.purch-content-wrap .con-wrap table tr td .Apply').on('click',function(){
	var id = $(this).attr('data-value');
	alert(id)
	parent.layer.confirm('真的要申请退回押金吗？', {
        btn: ['确认','取消'], //按钮
        shade: 0.5 //显示遮罩
    }, function(){
        $.post("/index.php/Home/SpecialBusiness/apply_for_deposit", { "id": id},function(data){
            if(data == 1){
                parent.layer.msg('提交成功!请等待审核!', { icon: 1, time: 2000 }, function(){
                    });
            }else{
                parent.layer.msg('提交失败', {icon: 2, time: 2000 }); 
            }
        }, "json");
    },function(){
    //     $("#del"+id+" td").css('border-top','0');
    //     $("#del"+id+" td").css('border-bottom','1px solid #EFEFEF');
    });
})

 
