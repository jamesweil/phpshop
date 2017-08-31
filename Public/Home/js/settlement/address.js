/**
 * 收货地址js
 */

$(document).ready(function (){
	//支付方式选择
	$('.conrm-section .orInfio .detailed li.method .payment span').on('click',function() {
		
		$('.conrm-section .orInfio .detailed li.method .payment span').removeClass('active').eq($(this).index()).addClass('active');
	});
	
	//新增收货地址and编辑地址
	$('.receiptCh .sd').on('click',function(){
		$('.ui-dialog').addClass('active');
	});
	$('.consignee-item a').on('click',function(){
		$('.ui-dialog').addClass('active');
	});
	$('.ui-dialog .consignee .ui-dialog-title a').on('click',function(){
		$('.ui-dialog').removeClass('active');
	});
});