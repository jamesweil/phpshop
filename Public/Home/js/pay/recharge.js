/**
 * 
 */
function recharge() {
	var price = $('#price').val();
	var username = $('#username').val();
	if (price < 0) {
		layer.tips('每次充值的金额不能低于0元', '#price');
		return false;
	}

	if (username == '') {
		layer.tips('充值账户的用户名不能为空', '#username');
		return false;
	}
}