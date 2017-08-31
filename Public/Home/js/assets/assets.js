//删除我的
// var aLi = $('.mcole-section-wrap .content-main .products-operation input[name=del]');
    $('.mcole-section-wrap .content-main').on('click','.products-operation input[name=del]',function(){
        var id = $(this).attr('data-value');
        var _this=$(this);
	    parent.layer.confirm('真的要删除吗？', {
	        btn: ['确认','取消'], //按钮
	        shade: 0.5 //显示遮罩
	    }, function(){
	        $.post("/index.php/Home/Assets/collection_del", { "id": id},function(data){
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
//删除单个我的足迹

$('.myTck-content-wrap .con-main li').on('click','.products-operation .del',function(){
    var id=$(this).attr('data-value');
    parent.layer.confirm('真的要删除吗？', {
        btn: ['确认','取消'], //按钮
        shade: 0.5 //显示遮罩
    }, function(){
        $.post("/index.php/Home/Assets/myTracks_del", { "id": id},function(data){
            if(data == 1){
                parent.layer.msg('删除成功', { icon: 1, time: 1000 }, function(){
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

    //删除全部我的足迹
var aLi = $('.myTck-content-wrap .nav-wrap .btn');
    aLi.on('click',function(){
	    parent.layer.confirm('真的要删除吗？', {
	        btn: ['确认','取消'], //按钮
	        shade: 0.5 //显示遮罩
	    }, function(){
	        $.post("myTracks_del_all", { "id": 5},function(data){
	            if(data == 1){
	                parent.layer.msg('删除成功', { icon: 1, time: 1000 }, function(){
	                        $('.myTck-content-wrap .con-main li').remove();
	                    });
	            }else{
	                parent.layer.msg('删除失败', {icon: 2, time: 2000 }); 
	            }
	        }, "json");
	    },function(){
	       
	    });

    });
// //分类
// $('.myTck-content-wrap .nav-wrap .nav').on('click','li .dorpmenu ul li',function(){
// 	var class_id = $(this).attr('data-value');
// 	$.ajax({
//         url : '/index.php/Home/Assets/myTracks_class',
//         dataType : "json",
//         type : 'post',
//         data : {class_id:class_id},
//         success:function(data){
//         	var page = data['page'];
//         	$('.page').html(page); 
//         	$('.myTck-content-wrap .con-main li').remove();
//             for (var i=0; i <= data['res'].length; i++) {
//             	$('.myTck-content-wrap .con-main').append('<li class="fl" id="del'+data['res'][i].id+'"><div class="screenshot"><a href="javascript:;"><img src="'+data['res'][i].goods_pic+'"></a></div><div class="products-description"><p><a href="javascript:;">'+data['res'][i].goods_name+'</a></p><span>¥ '+data['res'][i].goods_price+'</span></div><div class="products-operation clearfix"><a href="javascript:;" class="fl find" data-value="'+data['res'][i].id+'">找相似</a><input type="button" value="删除" class="fl del" data-value="'+data['res'][i].id+'"></div></li>');
//             };
//         },
//         error:function(){
            
//         },
//     });
// })