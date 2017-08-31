/**
 * 尾货清仓 分类滑动【特殊的】
 */
(function () {
	
	this.getData = function (url, id) {
		return $.post(url, {id:2}, function(res) {
			$('#'+id).html('');
			$('#'+id).html(res);
		}, '');
	}
	window.dataObj= this;
})();

;(function() {

    /**
     * 时间对象
     * @param {int} sec  倒计时秒数
     * @param {object} html HTML对象
     */
    function Timer(sec, html) {
        this.sec   = sec;
        this.html  = html;
        this.timer = null;
    }

    /**
     * 开启定时器
     */
    Timer.prototype.start = function() {

        if (this.html == null) {
            alert('html object cannot null!!!');
            return false;
        }
        
        if (this.sec < 0) {
        	 clearInterval(obj.timer);
            return false;
        }

        // 开启定时器
        var obj = this;
        obj.show();
        this.timer = setInterval(function() {
            obj.sec = obj.sec - 1;
            if (obj.sec < 0) {
                clearInterval(obj.timer);
                return;
            }
            obj.show()
        }, 1000);
    };

    
    /**
     * 时间转换
     */
    Timer.prototype.show = function() {
        var time  = this.sec;
        
        var day   = Math.floor(time/(24*3600));
        
        var leavel1 = time%(24*3600)   //计算天数后剩余的
        
        var leave2 = leavel1%(3600)     //计算小时数后剩余的
        
        var leave3 =  leave2%(60)  //计算分钟数后剩余的
        if (day < 10) {
            hours = '0'+day;
        }
        
        var hours = Math.floor(leavel1/(3600));
        
        if (hours < 10) {
            hours = '0'+hours;
        }
        var minute= Math.floor(leave2/60);
        console.log(minute);
        if (minute < 10) {
            minute = '0'+minute;
        }
        var second=leave3;
        if (second < 10) {
            second = '0'+second;
        }
        var html = '<span class="fl">'+day+'</span><b class="fl">：</b> <span class="fl">'+hours+'</span> <b class="fl">：</b> <span class="fl">'+minute+'</span>'+
				'<b class="fl">：</b> <span class="fl">'+second+'</span>'
        this.html.innerHTML = html;
    };

    var obj = document.getElementById("time");
    var timer = new Timer(sec, obj);
    timer.start();
})();



window.onload = function () {
	//+-------------------------------------------------------------------------
	//限时抢购
	;(function(){
		var oBtnfl = $('.poopcer-content-wrap .time-centent-main .btn-fl');
		var oBtnfr = $('.poopcer-content-wrap .time-centent-main .btn-fr');
		var oParent = $('.poopcer-content-wrap .time-centent-main');
		var oUl = $('.poopcer-content-wrap .time-centent-main ul');
		var Width = oParent.outerWidth();
		var iNow = 0;
		oParent.on('mouseenter',function(){
			oBtnfl.addClass('active');
			oBtnfr.addClass('active');
		}).on('mouseleave',function(){
			oBtnfl.removeClass('active');
			oBtnfr.removeClass('active');
		})
		oBtnfl.on('mouseenter',function(){
			if(iNow <= 0){
				$(this).addClass('hover');
			}else{
				$(this).removeClass('hover');
			}
		});
		oBtnfl.on('click',function(){
			if(iNow <= 0){
				$(this).addClass('hover');
				return;
			}
			$(this).removeClass('hover');
			iNow --;
			oUl.animate({left:-iNow*Width});
		});
		oBtnfr.on('click',function(){
			iNow ++;
			oUl.stop().animate({left:-iNow*Width});
			if(iNow <= 0){
				oBtnfl.addClass('hover');
			}else{
				oBtnfl.removeClass('hover');
			}
		});
		//轮播
		$('.brasp-banner .solid li').eq(0).css('zIndex','1');
		var Length = $('.brasp-banner .solid li').length,
			iNow = 0,
			timer = null,
			_this = null,
			clear = null,
			bFlag = false;
		for(var i = 0; i < Length; i++){
			$('.brasp-banner .page').append($('<a href="javascript:;"></a>'))
		}
		$('.brasp-banner .page a').eq(0).addClass('hover');
		function move(){
			$('.brasp-banner .solid li').eq(iNow).fadeIn(600).siblings().fadeOut(600);
			$('.brasp-banner .page a').eq(iNow).addClass('hover').siblings('a').removeClass('hover');
		}
		function block(){
			iNow++;
			if(iNow >= Length){
				iNow = 0;
			}
			move();
		}
		timer = setInterval(block,3000);
		$('.brasp-banner').on('mouseenter',function(){
			clearInterval(timer);
			$('.brasp-banner .btn-left').addClass('active');
			$('.brasp-banner .btn-right').addClass('active');
		}).on('mouseleave',function(){
			timer = setInterval(block,3000);
			$('.brasp-banner .btn-left').removeClass('active');
			$('.brasp-banner .btn-right').removeClass('active');
		});
		$('.brasp-banner .page a').on('mouseenter',function(){
			_this = $(this).index();
			clear = setTimeout(function(){
				iNow = _this;
				move();
			},100);
		}).on('mouseleave',function(){
			clearInterval(clear);
		});
		$('.brasp-banner .btn-left').on('click',function(){
			if(bFlag == true)return;
			bFlag = true;
			setTimeout(function(){
				bFlag = false;
			},600)
			iNow--;
			if(iNow <= -1){
				iNow = Length-1;
			}
			move();
		});
		$('.brasp-banner .btn-right').on('click',function(){
			if(bFlag == true)return;
			bFlag = true;
			setTimeout(function(){
				bFlag = false;
			},600)
			iNow++;
			if(iNow >= Length){
				iNow = 0;
			}
			move();
		});
	})()
}