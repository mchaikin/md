$(document).ready(function(){

        var $header = $("#header");
        var $logo = $("#logo");
        var $menu = $("#menu");
        var $clear = $("#clear");

        $(window).scroll(function(){
            if ( $(this).scrollTop() > 100 && $header.hasClass("default") ){
                $header.fadeOut('fast',function(){
                    $(this).removeClass("default")
                           .addClass("fixed transbg")
                           .fadeIn('fast');
                });
				$logo.fadeOut('fast',function(){
                    $(this).removeClass("default")
                           .addClass("fixed")
                           .fadeIn('fast');
                });
				$menu.fadeOut('fast',function(){
                    $(this).removeClass("default")
                           .addClass("fixed")
                           .fadeIn('fast');
                });
				$clear.fadeOut('fast',function(){
                    $(this).removeClass("default")
                           .addClass("fixed")
                           .fadeIn('fast');
                });
            } else if($(this).scrollTop() <= 100 && $header.hasClass("fixed")) {
                $header.fadeOut('fast',function(){
                    $(this).removeClass("fixed transbg")
                           .addClass("default")
                           .fadeIn('fast');
                });
				$logo.fadeOut('fast',function(){
                    $(this).removeClass("fixed")
                           .addClass("default")
                           .fadeIn('fast');
                });
				$menu.fadeOut('fast',function(){
                    $(this).removeClass("fixed")
                           .addClass("default")
                           .fadeIn('fast');
                });
				$clear.fadeOut('fast',function(){
                    $(this).removeClass("fixed")
                           .addClass("default")
                           .fadeIn('fast');
                });
            }
        });//scroll

        $header.hover(
            function(){
                if( $(this).hasClass('fixed') ){
                    $(this).removeClass('transbg');
                }
            },
            function(){
                if( $(this).hasClass('fixed') ){
                    $(this).addClass('transbg');
                }
            });//hover
			
		$('#menu li a').click(function () {
          $('#menu li').removeClass('current_page_item');
        $(this).parent().addClass('current_page_item');
           return true;
		});
    });//jQuery