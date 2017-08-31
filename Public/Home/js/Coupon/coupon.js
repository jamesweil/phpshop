//删除我的优惠券
var aLi = $('.coupn-content-wrap .content-main li .use-btn .del');
    aLi.on('click',function(){
        var id = $(this).attr('value');
	    parent.layer.confirm('真的要删除吗？', {
	        btn: ['确认','取消'], // 按钮
	        shade: 0.5 // 显示遮罩
	    }, function(){
	        $.post("coupon_del", { "id": id},function(data){
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
(function (w){
	
	this.ck = function (event, url){
		var Ck = $(event);
		var conponMonery = parseFloat('-'+Ck.attr('eventMonery'));
		var totalObj = $('#total');
		var total = parseFloat(totalObj.text());
		
		var conpouMonery = parseFloat(Ck.find(".Amount").attr('monery'));
		
		if (Ck.hasClass('active')) {
			Ck.removeClass("active");
			$('#whatCoupon').text(0.0);
			
			$('#couponListId').val(0);
			
			return totalObj.text(total-conponMonery);
		} else {
			
			var json = {};
			
			Ck.find('input').each(function () {
				if ( !$(this).val() ) {
					toastr.error('数据异常');
					return false;
				}
				json[$(this).attr('name')] = $(this).val();
			});
			json['totalMonery'] = parseFloat($("#totalMonery").text());
			return $.post(url, json, function (res) {
				
				if (!res.hasOwnProperty('status') || res.status != 1) {
					toastr.error(res.message);
					return false;
				}
				Ck.addClass('active');
				$('#whatCoupon').text(conponMonery);
				$('#couponListId').val(parseInt(Ck.attr('conpouId')));
				totalObj.text(total+conponMonery);
				$("#conpouMonery").html('金额抵用<span class="Coupon_Summary_erd" id="useConpon"><em>￥</em>'+conpouMonery+' </span> 优惠券1张，优惠<span>'+conpouMonery+'</span>元');
				return Ck.siblings().each(function () {
					if ($(this).hasClass("active")) {
						$(this).removeClass("active")
					}
				});
			});
		}
	}
	this.undo = function () {
		var undo = $(".Coupon_Click .Selection_Selection .Coupon_undo");
		undo.click(function(){
			$(this).parent().removeClass("active");
			event.stopPropagation();
		})
	}
	
	this.statistics = function() {
		
	}
	
	w.SettlementCoupon = this;
})(window)