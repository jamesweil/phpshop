;(function(){
  /*  var ul_html = "";
    var nav_url=$("#nav_banner_url").val();
    $.ajax({
        type:'get',
        dataType:"json",
        url:nav_url,
        cache: false,
        async: false,  //设置同步了
        success:function(data){
            $(data).each(function(i,v){
                ul_html +=' <li><a data="'+ v.ad_link+'" gg="'+v.id+'" onclick="addAd(this)" href="#"><img src="'+v.pic_url+'" width="1200" height="400"></a></li>';
            });
            $(".solid").html(ul_html);
        }

    });*/
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


})();
