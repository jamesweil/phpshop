/**
 * 
 */
var timeAlg = 0;
function pay_status() {
	if (timeAlg > 20) {
		layer.msg('等待时间过长。。。');
		setTimeout(function() {
			// 跳转到结果页面，并传递状态
			location.href = ORDER_URL;
		}, 10);
		
	}
	
	var order_id = $("#orderId").val();
	$.ajax({
		url : LISTEN_URL,
		dataType : 'json',
		type : 'post',
		data : {
			'orderSnId' : order_id
		},
		success : function(res) {
			var url = res.data.url;
			var status = res.status;
			if (status == 0) {
				timeAlg++;
				layer.msg('等待支付中。。。');
				return false ;
			}
			clearInterval(time); // 销毁定时器
			setTimeout(function() {
				// 跳转到结果页面，并传递状态
				window.location.href = url;
			}, 1000)
		},
		error : function() {
			layer.msg('支付错误');
		},

	});
}
// 启动定时器
var time = setInterval(function() {
	pay_status()
}, 3000);