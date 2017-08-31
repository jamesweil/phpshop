/**
 * 发票
 */
function bill_dk() {
	var xg = $(".iceInion .whether a"), Inb = $(".Invoice_background"), bill = $(".bill");
	xg.click(function() {
		Inb.addClass("active");
		bill.addClass("active");
	})
}
bill_dk();
function fpgb() {
	var Ib = $(".Invoice_background"), dg = $(".dialog_gb"), bf = $(".bill_off"), bi = $(".bill");
	Ib.click(function() {
		Ib.removeClass("active");
		bi.removeClass("active");
	});
	dg.click(function() {
		Ib.removeClass("active");
		bi.removeClass("active");
	})
	bf.click(function() {
		Ib.removeClass("active");
		bi.removeClass("active");
	})
}
fpgb();
function BT(){
	var bTui = $(".bill_Text_up_input"),
	bTuj = $(".bill_Text_up_xj"),
	bTug = $(".bill_Text_up_gs");
	bTui.click(function(){
		bTuj.removeClass("active");
		bTug.removeClass("active");
	});
	bTuj.click(function(){
		bTuj.addClass("active");
		bTug.addClass("active");
	})
}BT()
$(function() {
    var data = {
        counter: $('#invoice-tit-list').find('.invoice-item').length - 1
    };
    $('.invoice-list .add-invoice').on('click', function() {

        $('#invoice-tit-list').append('<div class="invoice-item invoice-item-selected">\
                                <div class="add-invoice-tit">\
                                    <input type="text" name="" class="itxt itxt04 xsg_new_class" placeholder="新增单位发票抬头">\
                                    <b></b>\
                                    <div class="btns"><a href="#none" class="ftx-05 save-tit">保存</a></div>\
                                </div>\
                            </div>');
        $(this).hide().parent().find('.invoice-item').removeClass('invoice-item-selected');
        data.counter++;
        $('#invoice-tit-list .invoice-item').eq(data.counter).find('input').focus();
    });
//纳税人识别号输入框显示与隐藏
	$('#click_display').on('click',function() {
		$('#xsg_div_display').css('display','none');
	});
	$('#invoice-tit-list').on('click','.xsg_new_class',function(){
		$('#xsg_div_display').find('#pay_taxes').attr('value','');
		$('#xsg_div_display').css('display','block');
	});

	$('.invoice-list .add-invoice').one("click",function(){
		$('#old_display').css('display','none');
		//增加纳税人识别号输入框
		$('#invoice-tit-list').parent().parent().after('<div class="new_style"><div class="item invoice_content clearfix"id="xsg_div_display"><span class="label fl">纳税人识别号：</span>\
				<div class="fl">\
				<div id="invoice-tit-list">\
				<div class="invoice-item invoice-item-selected block">\
				<div class="add-invoice-tit">\
				<input id="pay_taxes" type="text"   placeholder="纳税人识别号">\
				</div>\
				<b></b>\
				</div>\
				</div>\
				</div>\
				</div></div>')
		}) ;
	$('#invoice-tit-list').on('click', '.invoice-item .btns .save-tit', function() {
        if ($(this).parents('.invoice-item').find('input').val() == '') {
            alert('输入不能为空！');
        } else {
            $('#invoice-tit-list').find('.invoice-item:eq(' + data.counter + ')').removeClass('invoice-item-selected');
            $(this).addClass('hide').parents('.invoice-item').find('input').prop('readonly', true).parents('.invoice-item').addClass('invoice-item-selected');
            $('.invoice-list .add-invoice').show();
            $(this).parents('.btns').append('\
                <a href="javascript:;" class="ftx-05 edit-tit">编辑</a>\
                <a href="javascript:;" class="ftx-05 ml10 del-tit">删除</a>\
            ');
        }
    }).on('mouseenter', '.invoice-item', function() {
        $(this).addClass('hover');
    }).on('mouseleave', '.invoice-item', function() {
        $(this).removeClass('hover');
    }).on('click', '.invoice-item .btns .edit-tit', function() {
        $(this).parents('.invoice-item').find('input').prop('readonly', false).parents('.invoice-item').find('input').focus();
        $(this).parents('.invoice-item').find('input').on('blur', function() {
            $(this).prop('readonly', true);
        });
    }).on('click', '.invoice-item', function() {
        $(this).parents('#invoice-tit-list').find('.invoice-item').removeClass('invoice-item-selected');
        $(this).addClass('invoice-item-selected');
    }).on('click', '.invoice-item .btns .del-tit', function() {
        $(this).parents('.invoice-item').remove();
    });
    $('.invoice-dialog .invoice-list .invoice-item').on('click', function() {
        $(this).parent().find('.invoice-item').removeClass('invoice-item-selected');
        $(this).addClass('invoice-item-selected');
    })
    $('.tab-nav .tab-nav-item').on('click', function() {
        data.index = $(this).index();
        $(this).parent().find('.tab-nav-item').removeClass('tab-item-selected').eq(data.index).addClass('tab-item-selected');
        $('.invoice-dialog .tab-con .con').removeClass('active').eq(data.index).addClass('active');
    })
    $('.bill_bottom .bill_save').on('click',function(){
    	var type = $('.tab-nav .tab-item-selected').text();
    	var _data = $('.tab-nav .tab-item-selected').attr('data-value');
    	var invoice_header = $('.tab-con .active .invoice-tit-list .invoice-item-selected input').val();
		//var invoice_old_header=$('.tab-con .active .invoice-tit-list .invoice-item .add-invoice-tit input').val();
		//获取所有的单位抬头
		var invoice_array= new Array();
		$('.tab-con .active .invoice-tit-list .invoice-item .add-invoice-tit input').each(function(k,v){
			invoice_array[k]=$(this).val();
		})
		var pay_taxes_code=$('#pay_taxes').val();
    	if (invoice_header == '') {
    		alert('请填写发票抬头');
			return false;
    	};
		//判断选择的发票抬头
		if(invoice_header !='个人')
		{
			if(pay_taxes_code=='')
			{
				alert('请填写纳税人识别号');
				return false;
			}else if(!/^[a-zA-Z0-9]{15,18}$/.test(pay_taxes_code))
			{
				alert('请填写正确的纳税人识别号');
				return false;
			}
		}
	    var content = $('.tab-con .active .invoice_content #electro_book_content_radio .invoice-item-selected').text();
    	if (_data == 2) {
    		var mobile = $('#mobile').val();
    		var email = $('#email').val();
    		if(mobile == ''){
    			alert('请填写收票人手机号');
			    return false;
		    }
		    if(!/^1[34578]\d{9}$/.test(mobile)){
		    	alert('请输入正确的手机号');
			    return false;
			}
			if(email == ''){
				alert('请填写收票人邮箱');
			    return false;
		    }
		    if(!/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/.test(email)){
		    	alert('请填写正确邮箱');
			    return false;
		    }
			if(!/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/.test(email)){
				alert('请填写正确邮箱');
				return false;
			}
    	};
    	$.post("invoice_add",{"type":type,"invoice_array":invoice_array,"pay_taxes_code":pay_taxes_code,"invoice_header":invoice_header,"content":content,"mobile":mobile,"email":email},function(data){
			//code==1 插入成功   code==2抬头已存在   code==3插入失败
            if (data.code == 1) {
                var Inb = $(".Invoice_background"), bill = $(".bill");
                Inb.removeClass("active");
                bill.removeClass("active");
                layer.msg('保存成功!', { icon: 1, time: 2000});
				var invoiceHtml='';
				invoiceHtml+="<span style='margin-right:20px;'>"+data.invoice_data.invoice_type+"</span><span style='margin-right:20px;'>"+data.invoice_data.invoice_header+"</span><span>"+data.invoice_data.invoice_title+"</span>";
				$('#new_a').empty();
				$('#new_a').append(invoiceHtml);
                console.log(invoiceHtml);
            }else if(data.code==2){
				var Inb = $(".Invoice_background"), bill = $(".bill");
				Inb.removeClass("active");
				bill.removeClass("active");
				layer.msg('抬头已存在,请重试!', { icon: 1, time: 2000});
			}
			else{
            	var Inb = $(".Invoice_background"), bill = $(".bill");
                Inb.removeClass("active");
                bill.removeClass("active");
                layer.msg('保存失败!', { icon: 1, time: 2000});
            };
    	})
    })
});