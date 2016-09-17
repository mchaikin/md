$(document).ready(function() {
    $('a').click(function() {
		var url = $(this).attr('href');
		
		//sep = url.indexOf("?");
		//if ( sep != -1) {
		//	alert("EST'" + url);
		//} else {
		//	alert("NET '?'" + url);
		//}
		
        $.ajax({
            url:     url + '?ajax=1',
            success: function(data){
                $('#main').html(data);
            }
        });

        // А вот так просто меняется ссылка
        if(url != window.location){
            window.history.pushState(null, null, url);
        }

        // Предотвращаем дефолтное поведение
        return false;
    });
});

$(window).bind('popstate', function() {
    $.ajax({
        url:     location.pathname + '?ajax=1',
        success: function(data) {
            $('#main').html(data);
        }
    });
});