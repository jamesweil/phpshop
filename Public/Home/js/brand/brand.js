/**
 * 品牌店
 */

// 品牌快搜
/*$('.brasp-banner .solid li').eq(0).css('zIndex', '1');
var Length = $('.brasp-banner .solid li').length, iNow = 0, timer = null, _this = null, clear = null, bFlag = false;
for (var i = 0; i < Length; i++) {
	$('.brasp-banner .page').append($('<a href="javascript:;"></a>'))
}
$('.brasp-banner .page a').eq(0).addClass('hover');*/
/*function move() {
	$('.brasp-banner .solid li').eq(iNow).fadeIn(600).siblings().fadeOut(600);
	$('.brasp-banner .page a').eq(iNow).addClass('hover').siblings('a')
			.removeClass('hover');
}*/
var iNow = 0;
function block() {
	iNow++;
	if (iNow >= Length) {
		iNow = 0;
	}
	console.log(iNow)
	move();
}
timer = setInterval(block, 3000);
$('.brasp-banner').on('mouseenter', function() {
	clearInterval(timer);
	$('.brasp-banner .btn-left').addClass('active');
	$('.brasp-banner .btn-right').addClass('active');
}).on('mouseleave', function() {
	timer = setInterval(block, 3000);
	$('.brasp-banner .btn-left').removeClass('active');
	$('.brasp-banner .btn-right').removeClass('active');
});
$('.brasp-banner .page a').on('mouseenter', function() {
	_this = $(this).index();
	clear = setTimeout(function() {
		iNow = _this;
		move();
	}, 100);
}).on('mouseleave', function() {
	clearInterval(clear);
});
$('.brasp-banner .btn-left').on('click', function() {
	if (bFlag == true)
		return;
	bFlag = true;
	setTimeout(function() {
		bFlag = false;
	}, 600)
	iNow--;
	if (iNow <= -1) {
		iNow = Length - 1;
	}
	move();
});
$('.brasp-banner .btn-right').on('click', function() {
	if (bFlag == true)
		return;
	bFlag = true;
	setTimeout(function() {
		bFlag = false;
	}, 600)
	iNow++;
	if (iNow >= Length) {
		iNow = 0;
	}
	move();
});

//---------------------------------------------------------------------------

(function () {
	
	
	this.data = {};
	
	
	this.url = '';
	
	
	this.ajaxGetData = function(url, id) {
		
		return $.post(url, this.data, function (res) {
			$('#'+id).html(res);
		}, '');
	}
	
	
	window.pjaxData = this;
})(window);

$('.borasp-brandFS dd').each(function () {
	if ($(this).hasClass('active') && typeof GetURL !=='undefined') {
		pjaxData.data = {firster : $(this).text()}
		
		return pjaxData.ajaxGetData(GetURL, WHOID);
	}
});


$('#FastSeek-wrap .fl').click(function() {
	$(this).addClass('active');
	
	$(this).siblings().each(function () {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
		}
	});
	
	pjaxData.data = {firster : $(this).text()}
	
	pjaxData.ajaxGetData(ajaxBrandURL, addId);
});

$("#FastSeek-wrap .fl:eq(1)").trigger("click");