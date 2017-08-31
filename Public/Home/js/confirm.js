
window.onload = function () {
	InterAddress.getAreaList($('#parent'), AREA_LIST,$('#parent'));
	InterAddress.couponList('userConpon', COUPON);
	InterAddress.run = true;
	InterAddress.couponList('receive', RECEIVE);
	InterAddress.ajaxGetContent('invoiceHTML', INVOICE);
}
