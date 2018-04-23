( function( api ) {

	api.controlConstructor['typography'] = api.Control.extend( {
		ready: function() {
			var control = this;

			function addGoogleFont(fontName) {
					var font = control.params.ogf_fonts[fontName];
					var weights = $.map(font.variants, function(value, key) {
					  return key;
					});
					var weightsURL = weights.join(',');
					var fontURL = font.family.replace(' ','+') + ':' + weightsURL;
					wp.customize.previewer.send( 'olympusFontURL', "<link href='https://fonts.googleapis.com/css?family=" + fontURL + "' rel='stylesheet' type='text/css'>" );
			}

			control.container.on( 'change', '.typography-font-family select',
				function() {
					var value = jQuery( this ).val();
					control.settings['family'].set( value );
					if( value != 'default' ) {
						addGoogleFont( value );

						var font = control.params.ogf_fonts[value];
						var weightsSelect = jQuery( '.typography-font-weight select' );
						var newWeights = font.variants;
						weightsSelect.empty();
						$.each( newWeights, function( key, val ) {
							weightsSelect.append( $( "<option></option>" )
								 .attr( "value", key ).text( val ) );
						});
					}
				}
			);

			control.container.on( 'change', '.typography-font-weight select',
				function() {
					control.settings['weight'].set( jQuery( this ).val() );
				}
			);

			control.container.on( 'change', '.typography-font-style select',
				function() {
					control.settings['style'].set( jQuery( this ).val() );
				}
			);

			control.container.on( 'change', '.typography-font-size input',
				function() {
					control.settings['size'].set( jQuery( this ).val() );
				}
			);

			control.container.on( 'change', '.typography-line-height input',
				function() {
					control.settings['line_height'].set( jQuery( this ).val() );
				}
			);

			control.container.on( 'click', '.advanced-button',
				function() {
					jQuery( this ).toggleClass('open');
					jQuery( this ).parent().next( ".advanced-settings-wrapper" ).toggleClass('show');
				}
			);

			var picker = this.container.find( '.typography-font-color .color-picker-hex' );

			picker.wpColorPicker({
				change: function( event, ui ) {
					setTimeout( function(){
						control.settings['color'].set( picker.val() );
					}, 100 );
				},
			});

			$('.ogf-select').chosen({width: "85%"});

		}
	} );

} )( wp.customize );
