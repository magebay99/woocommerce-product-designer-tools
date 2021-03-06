/*
*/
jQuery( document ).ready( function($) {
	$('a[class=zip-design]').each(function(){
		$(this).click(function(){
			var url_zip_design = $(this).data('href');
			$.ajax({
				url: url_zip_design,
				type: 'GET',
				beforeSend: function() {
					$('.block-button .spinner').addClass('is-active');
				},
				success: function(_res) {
					$('.block-button .spinner').removeClass('is-active');
					if(typeof _res == 'object') {
						var res = _res;
					} else {
						var res = $.parseJSON(_res);
					}
					var data = res.data;
					if(res.status == 'success') {
						var url_download = data.baseUrl+''+data.file;
						window.location.href = url_download;
					} else {
						console.error('error', 'can\'t zip design');
					}					
				},
				error: function() {
					$('.block-button .spinner').removeClass('is-active');
				}
			});
		});
	});
});