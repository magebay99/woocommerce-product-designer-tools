jQuery( document ).ready( function($) {
	$('a[class=zip-design]').each(function(){
		$(this).click(function(){
			var url_zip_design = $(this).data('href');
			$.ajax({
				url: url_zip_design,
				type: 'GET',
				beforeSend: function() {
					
				},
				success: function(response) {
					
				}
			});
		});
	});
});