wp.customize.controlConstructor[ 'ogf-multiple-checkbox' ] = wp.customize.Control.extend( {

	// When we're finished loading continue processing.
	ready: function() {

		const control = this;

		if ( control.params.inputAttrs && /disabled/.test( control.params.inputAttrs ) ) {
			control.container.addClass( 'ogf-pro-locked' );

			control.container.find( 'input[type="checkbox"]' ).each( function() {
				this.disabled = true;
			} );

			control.container[0].addEventListener(
				'click',
				function( event ) {
					event.preventDefault();
					event.stopImmediatePropagation();

					if ( typeof window.ogfOpenCustomizerProModal === 'function' && typeof ogfCustomizerPro !== 'undefined' ) {
						window.ogfOpenCustomizerProModal(
							ogfCustomizerPro.fontLoading.title,
							ogfCustomizerPro.fontLoading.desc
						);
					}
				},
				true
			);
		}

		// Save the value
		control.container.on( 'change', 'input', function() {
			if ( control.container.hasClass( 'ogf-pro-locked' ) ) {
				return;
			}
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
