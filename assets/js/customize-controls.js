/* global ogf_font_variants, ogf_font_array, ajaxurl, fontsReset, location */
( function( api ) {
	api.controlConstructor[ 'ogf-typography' ] = api.Control.extend(
		{
			ready: function() {
				const control = this;
				const controlClass = '.customize-control-ogf-typography';
				const footerActions = jQuery( '#customize-footer-actions' );
				//
				// Do stuff when device icons are clicked
				jQuery( control.selector + ' .ogf-device-controls > div' ).on( 'click', function( event ) {
					var device = jQuery( this ).data( 'option' );
					wp.customize.previewedDevice.set( device );

					jQuery( controlClass + ' .ogf-device-controls div' ).each( function() {
						var _this = jQuery( this );

						if ( device === _this.attr( 'data-option' ) ) {
							_this.addClass( 'selected' );
							_this.siblings().removeClass( 'selected' );
						}
					} );

				});

				// Set the selected devices in our control when the Customizer devices are clicked
				footerActions.find( '.devices button' ).on( 'click', function() {
					var device = jQuery( this ).data( 'device' );

					jQuery( controlClass + ' .ogf-device-controls div' ).each( function() {
						var _this = jQuery( this );

						if ( device === _this.attr( 'data-option' ) ) {
							_this.addClass( 'selected' );
							_this.siblings().removeClass( 'selected' );
						}
					} );
				});

				// Load the Google Font for the preview.
				function addGoogleFont( fontName ) {
					const font = ogf_font_array[ fontName ];
					const weights = jQuery.map(
						font.v,
						function( value, key ) {
							return key;
						}
					);
					const weightsURL = weights.join( ',' );
					const fontURL = font.f.replace( / /g, '+' ) + ':' + weightsURL;
					wp.customize.previewer.send( 'olympusFontURL', '<link href=\'https://fonts.googleapis.com/css?family=' + fontURL + '\' rel=\'stylesheet\' type=\'text/css\'>' );
				}

				function isSystemFont( fontID ) {
					if ( fontID.indexOf( 'sf-' ) !== -1 ) {
						return true;
					}
					return false;
				}

				function isTypekitFont( fontID ) {
					if ( fontID.indexOf( 'tk-' ) !== -1 ) {
						return true;
					}
					return false;
				}

				function isCustomFont( fontID ) {
					if ( fontID.indexOf( 'cf-' ) !== -1 ) {
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
						const weightsSelect = jQuery( '.typography-font-weight select' );

						if ( value === 'default' || isSystemFont( value ) || isCustomFont( value ) ) {

							const defaultWeights = {
								0: "- Default -",
								400: "Normal",
								700: "Bold",
							}

							// replace the 'Font Weight' select field values.
							weightsSelect.empty();
							jQuery.each(
								defaultWeights,
								function( key, val ) {
									weightsSelect.append(
										jQuery( '<option></option>' )
											.attr( 'value', key ).text( val )
									);
								}
							);
						} else if ( isTypekitFont( value ) ) {
							const font = ogf_typekit_fonts[ value ];
							const newWeights = font.variants;
							newWeights.unshift("0");

							// remove variants the font doesn't support.
							var finalWeights = new Object();
							newWeights.forEach( function(i) {
								finalWeights[i] = ogf_font_variants[i];
							});

							const weightsSelect = jQuery( '.typography-font-weight select' );
							weightsSelect.empty();
							jQuery.each(
								finalWeights,
								function( key, val ) {
									weightsSelect.append(
										jQuery( '<option></option>' )
											.attr( 'value', key ).text( val )
									);
								}
							);

						} else {
							// Add Google Font enqueue to head of customizer.
							addGoogleFont( value );

							const font = ogf_font_array[ value ];
							const newWeights = font.v;
							newWeights[0] = "0";

							// remove variants the font doesn't support.
							var finalWeights = new Object();
							Object.keys(newWeights).forEach( function(val, i) {
								if ( ! val.endsWith('0i') ) {
									finalWeights[val] = ogf_font_variants[val];
								}
							});

							// replace the 'Font Weight' select field values.
							const weightsSelect = jQuery( '.typography-font-weight select' );
							weightsSelect.empty();
							jQuery.each(
								finalWeights,
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
						width : 225,
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
					const sliderMinValue = parseFloat( newSlider.attr( 'slider-min-value' ) );
					const sliderMaxValue = parseFloat( newSlider.attr( 'slider-max-value' ) );
					const sliderStepValue = parseFloat( newSlider.attr( 'slider-step-value' ) );

					newSlider.slider( {
						value: sliderValue,
						min: sliderMinValue,
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

	wp.customize.control( 'ogf_clear_cache', function( control ) {
		control.container.find( '.button' ).on( 'click', function( event ) {
			event.preventDefault();

			console.log('clicked clear cache');

			const data = {
				wp_customize: 'on',
				action: 'customizer_clear_cache',
				security: clearCache.nonce,
			};

			jQuery( this ).attr( 'disabled', 'disabled' );

			jQuery.post( ajaxurl, data, function( result ) {
				wp.customize.state( 'saved' ).set( true );
				alert('Cache successfully cleared.');
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

/* === Optimization Controls === */
jQuery( document ).ready( function() {
	jQuery( '#_customize-input-ogf_host_locally, #_customize-input-ogf_preloading, #_customize-input-ogf_removal, #_customize-input-ogf_rewrite' ).attr( 'disabled', 'true' );
} );

/* === Multiple Fonts Control === */
( function( api ) {
	api.controlConstructor[ 'ogf-typography-multiselect' ] = api.Control.extend( {
		ready: function() {
			const control = this;
			// Initialize chosen.js
			jQuery( '.ogf-select', control.container ).chosen( { width: '85%' } );
			jQuery( 'select', control.container ).on('change',
				function() {
					let selectValue = jQuery( this ).val();
					selectValue = ( null === selectValue ) ? [] : selectValue;
					control.setting.set( selectValue );
				}
			);
		},
	} );
}( wp.customize ) );
