/* global ajaxurl */
jQuery( document ).ready( function() {

	const menuGetHelpLink = jQuery('a[href="https://docs.fontsplugin.com"]');
	menuGetHelpLink.attr('target', '_blank');

	// Hook into the "notice-dismiss-welcome" class we added to the notice, so
	// Only listen to YOUR notices being dismissed
	jQuery( document ).on(
		'click',
		'.ogf-send-guide-button',
		function() {
			// Make an AJAX call
			// Since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.ajax(
				ajaxurl,
				{
					type: 'POST',
					data: {
						action: 'ogf_dismiss_guide',
					},
					complete: function() {
						location.reload();
					}
				}
			);
		}
	);

	// Hook into the "notice-dismiss-welcome" class we added to the notice, so
	// Only listen to YOUR notices being dismissed
	jQuery( document ).on(
		'click',
		'.notice-dismiss-dc .notice-dismiss',
		function() {
			// Read the "data-notice" information to track which notice
			// is being dismissed and send it via AJAX
			const type = jQuery( this ).closest( '.notice-dismiss-dc' ).data( 'notice' );
			// Make an AJAX call
			// Since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.ajax(
				ajaxurl,
				{
					type: 'POST',
					data: {
						action: 'ogf_dismiss_notice',
						type: type,
					},
				}
			);
		}
	);
} );
