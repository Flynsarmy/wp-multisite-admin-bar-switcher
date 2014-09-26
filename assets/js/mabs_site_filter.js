jQuery(document).ready( function($) {
	$('#wp-admin-bar-mabs_site_filter').show();

	$('#mabs_site_filter_text').keyup(function() {
		var queryLength = $(this).val().length;
		var searchQuery = new RegExp( $(this).val(), 'i');

		// Remove previous filter results
		$('#wp-admin-bar-mabs-default > li.mabs_blog.menupop').remove();

		// Hide letters if we're filtering, else show them
		$('#wp-admin-bar-mabs-default li.mabs_letter.menupop').toggle( !queryLength );

		if ( queryLength )
		{
			$('#wp-admin-bar-mabs-default li.mabs_blog.menupop').each(function(index, elem) {
				var matched = $('> a', elem).text().search(searchQuery) !== -1;

				if ( matched )
				{
					var id = $(elem).attr('id') + '_' + index;
					var clone = $(elem).clone().attr('id', id);

					$('#wp-admin-bar-mabs-default').append(clone);
					clone.hoverIntent({
						over: function() {
							$(this).addClass("hover")
						}, out: function() {
							$(this).removeClass("hover")
						}
					});
				}
			});
		}
	});
});