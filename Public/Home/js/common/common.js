/**
 * 导航栏目页修正
 */
window.onload = function () {
	$(".level").mouseover(function(){
		$(this).find('.menu').css({display : 'block', maraginBottom:'30px'});
	}).mouseout(function () {
		$(this).find('.menu').css({display : 'none', maraginBottom:'0px'});
	});
}