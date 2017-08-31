$(function(){
	//点击换位置
	$('.cityNav a').on('click',function(){
		$('#location').html($(this).html());
		$('.fore2').removeClass('active');
	});
	$('#switch').on('click',function(){
		$('.fore2').addClass('active');
	});
	$('.cityNav .cancel').on('click',function(){
		$('.fore2').removeClass('active');
	});


	//头部右侧效果
	$('.nav-right li').on('mouseenter',function(){
		$('.nav-right li').eq($(this).index()).addClass('up');
	}).on('mouseleave',function(){
		$('.nav-right li').removeClass('up');
	});
	$('.mobile').on('mouseenter',function(){
		$('.mobile .mobile-phone s').animate({top:0},100);
		$('.mobile .mobile-phone b').animate({top:-22+'px'},100);
		$(this).addClass('active');
	}).on('mouseleave',function(){
		$('.mobile .mobile-phone s').animate({top:22},100);
		$('.mobile .mobile-phone b').animate({top:0+'px'},100);
		$(this).removeClass('active');
	});


	;(function(){
		//头部广告过渡动画
		var timer = null,
			clear = null,
			advMax = $('.header-advertisement-one'),
			advMin = $('.header-advertisement');
		function move(){
			// h:500 w:1200
			timer = setTimeout(function(){
				advMax.animate({height:0},1000,function(){
					clearInterval(timer);
					advMin.show();
					sessionStorage.setItem('moveData', 'false');
				});
			},3000);
		}
		if(sessionStorage.getItem('moveData') === 'false'){
			advMax.css('height',0);
			advMin.show();
		} else {
			advMax.animate({height:500+'px'},1000,function(){
				move();
			});
		}
		advMin.hover(function(){
			clear = setTimeout(function(){
				advMin.hide();
				advMax.animate({height:500+'px'},1000);
			},2000);
		},function(){
			clearInterval(clear)
		})
		advMax.hover(function(){
			clearInterval(timer);
		},function(){
			move();
		})
		$('.home-delete-one').on('click',function(){
			advMax.animate({height:0},1000,function(){
				clearInterval(timer);
				advMin.show();
				sessionStorage.setItem('moveData', 'false');
			});
			$('.header-advertisement').css('display','block');
		});
	})();

	//购物车
	$('.home-shopping').on('mouseenter',function(){
		$(this).addClass('active');
	}).on('mouseleave',function(){
		$(this).removeClass('active');
	});

	//二级菜单
	$('.level .menu').on('mouseenter',function(){
		$('.level .menu').removeClass('active').eq($(this).index()-1).addClass('active');
	}).on('mouseleave',function(){
		$('.level .menu').removeClass('active');
	});

	//新品选项卡效果
	$('.hpLeftBanner .mc ol li').on('mouseenter',function(){
		$(this).css('cursor',"pointer");
		$('.hpLeftBanner .mc ol li').removeClass('hover').eq($(this).index()).addClass('hover');
		$('.hpLeftBanner .mc ul li').removeClass('active').eq($(this).index()).addClass('active');
	});

	//每日推荐选项卡效果
	$('.home-today .home-today-fl .mrtj-tab li').on('click',function(){
		$('.home-today .home-today-fl .mrtj-tab li').removeClass('active').eq($(this).index()).addClass('active');
		$('.home-today .home-today-fr ul').removeClass('active').eq($(this).index()).addClass('active');
	});

	//内容选项
	$('.home-section1 .saleZone-fr .content-nav-one li').on('click',function(){
		$('.home-section1 .saleZone-fr .content-main .con').removeClass('active').eq($(this).index()).addClass('active');
		$('.home-section1 .saleZone-fr .content-nav-one li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.home-section2 .saleZone-fr .content-nav-one li').on('click',function(){
		$('.home-section2 .saleZone-fr .content-main .con').removeClass('active').eq($(this).index()).addClass('active');
		$('.home-section2 .saleZone-fr .content-nav-one li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.home-section3 .saleZone-fr .content-nav-one li').on('click',function(){
		$('.home-section3 .saleZone-fr .content-main .con').removeClass('active').eq($(this).index()).addClass('active');
		$('.home-section3 .saleZone-fr .content-nav-one li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.home-section4 .saleZone-fr .content-nav-one li').on('click',function(){
		$('.home-section4 .saleZone-fr .content-main .con').removeClass('active').eq($(this).index()).addClass('active');
		$('.home-section4 .saleZone-fr .content-nav-one li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.home-section5 .saleZone-fr .content-nav-one li').on('click',function(){
		$('.home-section5 .saleZone-fr .content-main .con').removeClass('active').eq($(this).index()).addClass('active');
		$('.home-section5 .saleZone-fr .content-nav-one li').removeClass('active').eq($(this).index()).addClass('active');
	});
	$('.home-section6 .saleZone-fr .content-nav-one li').on('click',function(){
		$('.home-section6 .saleZone-fr .content-main .con').removeClass('active').eq($(this).index()).addClass('active');
		$('.home-section6 .saleZone-fr .content-nav-one li').removeClass('active').eq($(this).index()).addClass('active');
	});
	//下楼梯菜单
	;(function(){
		var _index=0;
		$("#menu ul li").click(function(){
			$('html').attr('data','false');
			$(this).find("span").addClass("active").parent().siblings().find("span").removeClass("active");
			_index=$(this).index()+1;
			var _top=$(".home-section"+_index).offset().top;
			$("body,html").animate({scrollTop:_top},500,function(){
				setTimeout(function(){
					$('html').attr('data','');
				},100);
			});
		});
		var nav=$("#menu");
		var win=$(window);
		var sc=$(document);
		win.scroll(function(){
			if(sc.scrollTop()>=600){
				$("#menu").show();
				var index=Math.floor(sc.scrollTop()/600);
				if($('html').attr('data') != 'false'){
					console.log(1)
					$("#menu ul li span").eq(index-1).addClass("active").parent().siblings().find("span").removeClass("active");
				}
			}else{
				$("#menu").hide();
			}
		});
	})();
	//服务
	$('.home-tab li').on('mouseover',function(){
		$('.home-tab li .userTips').eq($(this).index()).addClass('active');
	}).on('mouseout',function(){
		$('.home-tab li .userTips').removeClass('active');
	});
	$('.home-tab li').eq(5).on('click',function(){
		$('.home-tab').css('display','none');
	});
});