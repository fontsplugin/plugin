/* global ogf_repeater */

jQuery( document ).ready( function() {
	const themeControls = jQuery( '#customize-theme-controls' );

	/**
   * This adds a new box to repeater
   */
	themeControls.on( 'click', '.customizer-repeater-new-field', function() {
		const parent = jQuery( this ).closest( '.customize-control' );

		if ( typeof parent !== 'undefined' ) {
			/* Clone the first box*/
			const field = parent.find( '.customizer-repeater-general-control-repeater-container:first' ).clone( true, true );

			if ( typeof field !== 'undefined' ) {
				/*Show delete box button because it's not the first box*/
				field.find( '#ogf-repeater-control-remove-field' ).show();

				/*Remove value from text field*/
				field.find( '.customizer-repeater-control' ).val( '' );

				/*Append new box*/
				parent.find( '.customizer-repeater-general-control-repeater-container:first' ).parent().append( field );

				/*Refresh values*/
				customizerRepeaterRefreshValues();
			}
		}
		return false;
	} );

	themeControls.on( 'click', '#ogf-repeater-control-remove-field', function() {
		const control = jQuery( this ).closest( '.customizer-repeater-general-control-repeater-container' );
		if ( typeof control !== 'undefined' ) {
			control.hide( 250, function() {
				control.remove();
				customizerRepeaterRefreshValues();
			} );
		}
		return false;
	} );

	themeControls.on( 'keyup', '.customizer-repeater-control', function() {
		customizerRepeaterRefreshValues();
	} );

	/**
   * Save elements and refresh the customizer.
   */
	themeControls.on( 'click', '.ogf_save_elements_button', function() {
		jQuery.when( wp.customize.previewer.save() ).done( function() {
			window.location.href = ogf_repeater.return_url;
		} );
	} );
} );

function customizerRepeaterRefreshValues() {

	const entityMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		'\'': '&#39;',
		'/': '&#x2F;',
	};

	function escapeHtml( string ) {
		//noinspection JSUnresolvedFunction
		string = String( string ).replace( new RegExp( '\r?\n', 'g' ), '<br />' );
		string = String( string ).replace( /\\/g, '&#92;' );
		return String( string ).replace( /[&<>"'\/]/g, function( s ) {
			return entityMap[ s ];
		} );
	}

	jQuery( '.customizer-repeater-general-control-repeater' ).each( function() {
		const values = [];
		jQuery( this ).find( '.customizer-repeater-general-control-repeater-container' ).each( function() {
			let label = jQuery( this ).find( '.customizer-repeater-label-control' ).val();
			const description = jQuery( this ).find( '.customizer-repeater-description-control' ).val();
			const selectors = jQuery( this ).find( '.customizer-repeater-selectors-control' ).val();

			if ( label !== '' || description !== '' || selectors !== '' ) {
				label = ( label !== '' ? label : selectors );
				values.push( {
					label: escapeHtml( label ),
					description: escapeHtml( description ),
					selectors: escapeHtml( selectors ),
				} );
			}
		} );
		jQuery( this ).find( '.customizer-repeater-colector' ).val( JSON.stringify( values ) );
		jQuery( this ).find( '.customizer-repeater-colector' ).trigger( 'change' );
	} );
}
