wp.customize.controlConstructor[ 'ogf-multiple-checkbox' ] = wp.customize.Control.extend( {

	// When we're finished loading continue processing.
	ready: function() {

		const control = this;

		// Save the value
		control.container.on( 'change', 'input', function() {
			const value = [];
			let i = 0;

			// Build the value as an object using the sub-values from individual checkboxes.
			jQuery.each( control.params.choices, function( key ) {
				if ( control.container.find( 'input[value="' + key + '"]' ).is( ':checked' ) ) {
					value[ i ] = key;
					i++;
				}
			} );

			// Update the value in the customizer.
			control.setting.set( value );
		} );
	},

} );
