/**
 * 运送
 */
$(document).ready(function() {
	// 配送方式选择

	var total = parseFloat($('#total').text()); // 商品金额  
	$('li.distribution .payment span').on('click', function() {
		
		//获取运送地址
		var shippingAddress = 0;
		
		$('.myAddress').each(function () {
			var self = $(this);
			
			if (self.hasClass('active')) {
				shippingAddress = self.attr('data-id');
				return shippingAddress;
			}
			
		});
			
		//获取运送方式
		var self = $(this);
		var shopType = self.attr('value');
		var json = {
			id 			: shopType,
			addressId	: shippingAddress,
			discount    : self.attr('discount')
		};
		
		var paseMonery = parseFloat($(this).attr('monery'));
		
		
		Tool.ajaxOther(URL, json, function (res) {
			
			var data = res.data;
			
			var monery = 0;//运费
			
			if (res.status == 1) {
				var monery = parseFloat(data.money); //运费
				self.addClass('active');
			} else {
				layer.msg(res.message);
			}
			
			
			$("#shipping").html('<em>运费：￥' + monery + '</em>');

			$('#expressType').attr('name', self.attr('name'));
			$('#expId').val(shopType);
			$('#expressType').val(parseInt(self.attr('type')));
			$('#shippingMonery').val(monery);
			var goodsMonery = monery+total; // 
			console.log(goodsMonery);
			$('#total').text(goodsMonery);
			$('#priceMonery').val(goodsMonery);

		});
		
		$('#express').val($(this).attr('value'));

		self.siblings().each(function() {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');
			}
		});
	});
});