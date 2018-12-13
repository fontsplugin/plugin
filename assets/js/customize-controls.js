( function ( api ) {

	api.controlConstructor['typography'] = api.Control.extend(
		{
			ready: function () {

				var control = this;

				// Load the Google Font for the preview.
				function addGoogleFont(fontName) {
					var font       = ogf_font_array[fontName];
					var weights    = jQuery.map(
						font.variants,
						function (value, key) {
							return key;
						}
					);
					var weightsURL = weights.join( ',' );
					var fontURL    = font.family.replace( / /g, '+' ) + ':' + weightsURL;
					wp.customize.previewer.send( 'olympusFontURL', "<link href='https://fonts.googleapis.com/css?family=" + fontURL + "' rel='stylesheet' type='text/css'>" );
				}

				// Load the font-weights for the newly selected font.
				control.container.on(
					'change',
					'.typography-font-family select',
					function () {
						var value = jQuery( this ).val();
						control.settings['family'].set( value );
						if (value != 'default' ) {
							addGoogleFont( value );

							var font          = ogf_font_array[value];
							var weightsSelect = jQuery( '.typography-font-weight select' );
							var newWeights    = font.variants;
							weightsSelect.empty();
							jQuery.each(
								newWeights,
								function ( key, val ) {
									weightsSelect.append(
										jQuery( "<option></option>" )
										.attr( "value", key ).text( val )
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
					function () {
						jQuery( this ).toggleClass( 'open' );
						jQuery( this ).parent().next( ".advanced-settings-wrapper" ).toggleClass( 'show' );
					}
				);

				// Initialize the wpColorPicker.
				var picker = this.container.find( '.typography-font-color .color-picker-hex' );

				picker.wpColorPicker(
					{
						change: function ( event, ui ) {
							setTimeout(
								function () {
									control.settings['color'].set( picker.val() );
								},
								100
							);
						},
						clear: function() {
							control.settings['color'].set( picker.val() );
						}
					}
				);

				// Initialize chosen.js
				jQuery( '.ogf-select' ).chosen( {width: "85%"} );

				/**
				 * Slider Custom Control
				 */

				// Set our slider defaults and initialise the slider
				$('.slider-custom-control').each(function(){
					var sliderValue = $(this).find('.customize-control-slider-value').val();
					var newSlider = $(this).find('.slider');
					var sliderMinValue = parseFloat(newSlider.attr('slider-min-value'));
					var sliderMaxValue = parseFloat(newSlider.attr('slider-max-value'));
					var sliderStepValue = parseFloat(newSlider.attr('slider-step-value'));

					newSlider.slider({
						value: sliderValue,
						max: sliderMaxValue,
						step: sliderStepValue,
						slide: function(e,ui){
							// Important! When slider stops moving make sure to trigger change event so Customizer knows it has to save the field
							$(this).parent().find('.customize-control-slider-value').trigger('change');
							}
					});
				});

				// Change the value of the input field as the slider is moved
				$('.slider').on('slide', function(event, ui) {
					$(this).parent().find('.customize-control-slider-value').val(ui.value);
				});

				// Reset slider and input field back to the default value
				$('.slider-reset').on('click', function() {
					var resetValue = $(this).attr('slider-reset-value');
					$(this).parent().find('.customize-control-slider-value').val(resetValue);
					$(this).parent().find('.customize-control-slider-value').trigger('change');
					$(this).parent().find('.slider').slider('value', resetValue);
				});

				// Update slider if the input field loses focus as it's most likely changed
				$('.customize-control-slider-value').blur(function() {
					var resetValue = $(this).val();
					var slider = $(this).parent().find('.slider');
					var sliderMinValue = parseInt(slider.attr('slider-min-value'));
					var sliderMaxValue = parseInt(slider.attr('slider-max-value'));

					// Make sure our manual input value doesn't exceed the minimum & maxmium values
					if(resetValue < sliderMinValue) {
						resetValue = sliderMinValue;
						$(this).val(resetValue);
					}
					if(resetValue > sliderMaxValue) {
						resetValue = sliderMaxValue;
						$(this).val(resetValue);
					}
					$(this).parent().find('.slider').slider('value', resetValue);
				});

			}
		}
	);

} )( wp.customize );

/* === Checkbox Multiple Control === */
jQuery(document).ready(function () {

	jQuery( '.customize-multiple-checkbox-control input[type="checkbox"]' ).on( 'change',
		function() {

				checkbox_values = jQuery( this ).parents( '.customize-control' ).find( 'input[type="checkbox"]:checked' ).map(
						function() {
								return this.value;
						}
				).get().join( ',' );

				jQuery( this ).parents( '.customize-control' ).find( 'input[type="hidden"]' ).val( checkbox_values ).trigger( 'change' );
			}
	);
});
