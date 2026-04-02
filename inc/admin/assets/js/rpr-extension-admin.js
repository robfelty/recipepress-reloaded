document.addEventListener('DOMContentLoaded', function(){
    MicroModal.init();
}, false);

(function($){

	$( document ).on( 'click', '.button-extension-activate', function( e ){
		e.preventDefault();
		// Get the current extension ID
		var extension = $(this).attr('data-extension'),
			$this     = $(this);

		if ( extension ) {
			// Process AJAX
			$.ajax({
				url: rpr_extension_admin.ajax_url,
				dataType: 'json',
				type: 'POST',
				data: {
					action: 'rpr_activate_extension_ajax',
					extension: extension,
					ext_nonce: rpr_extension_admin.ext_nonce
				},
				success: function(resp) {
					if ( resp.success ) {
						// On success, add the deactivate class and remove the activate class.
						// Also remove the primary class so we have a gray button and not a blue one.
						$this.addClass('button-extension-deactivate')
						.addClass('button-default')
						.removeClass('button-extension-activate')
						.removeClass('button-primary')
						.html( rpr_extension_admin.text.deactivate );
						$( '.' + extension + '-settings').show('fast');
						location.reload();
					}
				}
			});
		}

	});

	$( document ).on( 'click', '.button-extension-deactivate', function( e ){
		e.preventDefault();

		var extension = $(this).attr('data-extension'),
			$this = $(this);
		if( extension ) {
			$.ajax({
				url: rpr_extension_admin.ajax_url,
				dataType: 'json',
				type: 'POST',
				data: {
					action: 'rpr_deactivate_extension_ajax',
					extension: extension,
					ext_nonce: rpr_extension_admin.ext_nonce
				},
				success: function(resp) {
					if( resp.success ) {
						$this.removeClass('button-extension-deactivate')
						.removeClass('button-default')
						.addClass('button-extension-activate')
						.addClass('button-primary')
						.html( rpr_extension_admin.text.activate );
						$( '.' + extension + '-settings').hide('fast');
					}
				}
			});
		}

	});

})(jQuery);
