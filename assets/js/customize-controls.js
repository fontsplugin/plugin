/* global ogf_font_variants, ogf_font_array, ajaxurl, fontsReset, location */
( function( api ) {
	api.controlConstructor[ 'typography' ] = api.Control.extend(
		{
			ready: function() {
				const control = this;

				// Load the Google Font for the preview.
				function addGoogleFont( fontName ) {
					const font = ogf_font_array[ fontName ];
					const weights = jQuery.map(
						font.variants,
						function( value, key ) {
							return key;
						}
					);
					const weightsURL = weights.join( ',' );
					const fontURL = font.family.replace( / /g, '+' ) + ':' + weightsURL;
					wp.customize.previewer.send( 'olympusFontURL', '<link href=\'https://fonts.googleapis.com/css?family=' + fontURL + '\' rel=\'stylesheet\' type=\'text/css\'>' );
				}

				function isSystemFont( fontID ) {
					if ( fontID.indexOf( 'sf-' ) !== -1 ) {
						return true;
					}
					return false;
				}

				// Load the font-weights for the newly selected font.
				control.container.on(
					'change',
					'.typography-font-family select',
					function() {
						const value = jQuery( this ).val();
						control.settings.family.set( value );
						if ( value !== 'default' && ! isSystemFont( value ) ) {
							addGoogleFont( value );

							const font = ogf_font_array[ value ];
							const weightsSelect = jQuery( '.typography-font-weight select' );
							const newWeights = font.variants;

							// remove variants the font doesn't support.
							for (const property in ogf_font_variants) {
								if ( property != 0 && typeof( newWeights[property] ) == "undefined" ) delete ogf_font_variants[property];
							}

							// replace the 'Font Weight' select field values.
							weightsSelect.empty();
							jQuery.each(
								ogf_font_variants,
								function( key, val ) {
									weightsSelect.append(
										jQuery( '<option></option>' )
											.attr( 'value', key ).text( val )
									);
								}
							);
						}
					}
				);

				// Show advanced settings.
				control.container.on(
					'click',
					'.advanced-button',
					function() {
						jQuery( this ).toggleClass( 'open' );
						jQuery( this ).parent().next( '.advanced-settings-wrapper' ).toggleClass( 'show' );
					}
				);

				// Initialize the wpColorPicker.
				const picker = this.container.find( '.typography-font-color .color-picker-hex' );

				picker.wpColorPicker(
					{
						change: function() {
							setTimeout(
								function() {
									control.settings.color.set( picker.val() );
								},
								100
							);
						},
						clear: function() {
							control.settings.color.set( picker.val() );
						},
					}
				);

				// Initialize chosen.js
				jQuery( '.ogf-select', control.container ).chosen( { width: '85%' } );

				// Set our slider defaults and initialise the slider
				jQuery( '.slider-custom-control' ).each( function() {
					const sliderValue = jQuery( this ).find( '.customize-control-slider-value' ).val();
					const newSlider = jQuery( this ).find( '.slider' );
					const sliderMaxValue = parseFloat( newSlider.attr( 'slider-max-value' ) );
					const sliderStepValue = parseFloat( newSlider.attr( 'slider-step-value' ) );

					newSlider.slider( {
						value: sliderValue,
						max: sliderMaxValue,
						step: sliderStepValue,
						slide: function() {
							// Important! When slider stops moving make sure to trigger change event so Customizer knows it has to save the field
							jQuery( this ).parent().find( '.customize-control-slider-value' ).trigger( 'change' );
						},
					} );
				} );

				// Change the value of the input field as the slider is moved
				jQuery( '.slider' ).on( 'slide', function( event, ui ) {
					jQuery( this ).parent().find( '.customize-control-slider-value' ).val( ui.value );
				} );

				// Reset slider and input field back to the default value
				jQuery( '.slider-reset' ).on( 'click', function() {
					const resetValue = jQuery( this ).attr( 'slider-reset-value' );
					jQuery( this ).parent().find( '.customize-control-slider-value' ).val( resetValue );
					jQuery( this ).parent().find( '.customize-control-slider-value' ).trigger( 'change' );
					jQuery( this ).parent().find( '.slider' ).slider( 'value', resetValue );
				} );

				// Update slider if the input field loses focus as it's most likely changed
				jQuery( '.customize-control-slider-value' ).blur( function() {
					let resetValue = jQuery( this ).val();
					const slider = jQuery( this ).parent().find( '.slider' );
					const sliderMinValue = parseInt( slider.attr( 'slider-min-value' ) );
					const sliderMaxValue = parseInt( slider.attr( 'slider-max-value' ) );

					// Make sure our manual input value doesn't exceed the minimum & maxmium values
					if ( resetValue < sliderMinValue ) {
						resetValue = sliderMinValue;
						jQuery( this ).val( resetValue );
					}
					if ( resetValue > sliderMaxValue ) {
						resetValue = sliderMaxValue;
						jQuery( this ).val( resetValue );
					}
					jQuery( this ).parent().find( '.slider' ).slider( 'value', resetValue );
				} );
			},
			/**
			 * Embed the control in the document.
			 *
			 * Override the embed() method to do nothing,
			 * so that the control isn't embedded on load,
			 * unless the containing section is already expanded.
			 *
			 */
			embed: function() {
				const control = this;
				const sectionId = control.section();
				if ( ! sectionId ) {
					return;
				}
				wp.customize.section( sectionId, function( section ) {
					section.expanded.bind( function( expanded ) {
						if ( expanded ) {
							control.actuallyEmbed();
						}
					} );
				} );
			},
			/**
			 * Deferred embedding of control when actually
			 *
			 * This function is called in Section.onChangeExpanded() so the control
			 * will only get embedded when the Section is first expanded.
			 */
			actuallyEmbed: function() {
				const control = this;
				if ( 'resolved' === control.deferred.embedded.state() ) {
					return;
				}
				control.renderContent();
				control.deferred.embedded.resolve(); // This triggers control.ready().
			},
		}
	);

	wp.customize.control( 'ogf_reset_fonts', function( control ) {
		control.container.find( '.button' ).on( 'click', function( event ) {
			event.preventDefault();

			const data = {
				wp_customize: 'on',
				action: 'customizer_reset',
				security: fontsReset.nonce,
			};

			const confirmReset = confirm( fontsReset.confirm );

			if ( ! confirmReset ) {
				return;
			}

			jQuery( this ).attr( 'disabled', 'disabled' );

			jQuery.post( ajaxurl, data, function( result ) {
				wp.customize.state( 'saved' ).set( true );
				location.reload();
			} );
		} );
	} );
}( wp.customize ) );

/* === Checkbox Multiple Control === */
jQuery( document ).ready( function() {
	jQuery( '.customize-multiple-checkbox-control input[type="checkbox"]' ).on( 'change',
		function() {
			const checkboxValues = jQuery( this ).parents( '.customize-control' ).find( 'input[type="checkbox"]:checked' ).map(
				function() {
					return this.value;
				}
			).get().join( ',' );

			jQuery( this ).parents( '.customize-control' ).find( 'input[type="hidden"]' ).val( checkboxValues ).trigger( 'change' );
		}
	);
} );

/* === Multiple Fonts Control === */
( function( api ) {
	api.controlConstructor[ 'typography-multiselect' ] = api.Control.extend( {
		ready: function() {
			const control = this;
			// Initialize chosen.js
			jQuery( '.ogf-select', control.container ).chosen( { width: '85%' } );
			jQuery( 'select', control.container ).change(
				function() {
					let selectValue = jQuery( this ).val();
					selectValue = ( null === selectValue ) ? [] : selectValue;
					control.setting.set( selectValue );
				}
			);
		},
	} );
}( wp.customize ) );
