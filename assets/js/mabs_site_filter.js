jQuery(document).ready( function($) {
	$('#wp-admin-bar-mabs_site_filter').show();

	$('#mabs_site_filter_text').keyup(function() {
		var searchQuery = new RegExp( $(this).val(), 'i');

		// Hide letters and blogs
		$('#wp-admin-bar-mabs-default li.mabs_letter.menupop, #wp-admin-bar-mabs-default li.mabs_blog.menupop').hide();

		$('#wp-admin-bar-mabs-default li.mabs_blog.menupop > a').each(function() {
			var matched = $(this).text().search(searchQuery) !== -1;

			if ( matched )
			{
				$(this).closest('li.mabs_blog.menupop').show();
				$(this).closest('li.mabs_letter.menupop').show();
			}
		});
	});
});