/**
 * 
 */
$(function() {
	$("*[data-toggle='tooltip']").tooltip({
		position : {
			my : "left top+5",
			at : "left bottom"
		}
	});
});

/**
 * 页面加载完成时
 */
window.onload  = function ()
{
	td_toggle();
	Tool.dataType = '';
	Tool.post (TODAY_URL, {}, function (res) {
		$('#today').html('');
		$('#today').html(res);
	});
	
	Tool.post (ALL_URL, {}, function (res) {
		$('#all').html('');
		$('#all').html(res);
	});
}

function td_toggle() {
	$('.display-none').toggle();
	if($(".display-none").is(":hidden")){
		$('#td-toggle').text('点击查看更多');
	}else{
		$('#td-toggle').text('点击隐藏');
	}

}