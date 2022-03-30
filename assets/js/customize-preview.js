/* global ogf_elements, ogf_system_fonts, ogf_custom_fonts */
jQuery( document ).ready(
	function() {
		// Retrieve the Google Fonts url from the Customizer and append it to head.
		wp.customize.preview.bind(
			'olympusFontURL',
			function( url ) {
				jQuery( 'head' ).append( url );
			}
		);

		// Update the font family for this element.
		function fontFamilyChange( selector, value ) {
			if ( value === 'default' ) {
				jQuery( selector ).css( 'font-family', '' );
				wp.customize.preview.send( 'refresh' );
			} else if ( isSystemFont( value ) ) {
				jQuery( selector ).each( function( i, v ) {
					const fontID = value.replace( 'sf-', '' );
					v.style.setProperty( 'font-family', ogf_system_fonts[ fontID ].stack, 'important' );
				} );
			} else if ( isCustomFont( value ) ) {
				jQuery( selector ).each( function( i, v ) {
					const fontID = value.replace( 'cf-', '' );
					v.style.setProperty( 'font-family', ogf_custom_fonts[ fontID ].stack, 'important' );
				} );
			} else if ( isTypekitFont( value ) ) {
				jQuery( selector ).each( function( i, v ) {
					v.style.setProperty( 'font-family', ogf_typekit_fonts[ value ].stack, 'important' );
				} );
			} else {
				jQuery( selector ).each( function( i, v ) {
					v.style.setProperty( 'font-family', '"' + value.split( '-' ).join( ' ' ) + '"', 'important' );
				} );
			}
		}

		function isSystemFont( fontID ) {
			if ( fontID.indexOf( 'sf-' ) !== -1 ) {
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

		function isTypekitFont( fontID ) {
			if ( fontID.indexOf( 'tk-' ) !== -1 ) {
				return true;
			}
			return false;
		}

		// Loop through the elements and bind the controls.
		jQuery.map( ogf_elements, function( val, id ) {
			wp.customize(
				id + '_font',
				function( value ) {
					value.bind(
						function( to ) {
							fontFamilyChange( val.selectors, to );
						}
					);
				}
			);

			wp.customize(
				id + '_font_weight',
				function( value ) {
					value.bind(
						function( to ) {
							if ( to === '0' ) {
								wp.customize.preview.send( 'refresh' );
							} else {
								jQuery( val.selectors ).each( function( i, v ) {
									v.style.setProperty( 'font-weight', to, 'important' );
								} );
							}
						}
					);
				}
			);

			wp.customize(
				id + '_font_style',
				function( value ) {
					value.bind(
						function( to ) {
							jQuery( val.selectors ).each( function( i, v ) {
								v.style.setProperty( 'font-style', to, 'important' );
							} );
						}
					);
				}
			);

			wp.customize(
				id + '_font_color',
				function( value ) {
					value.bind(
						function( to ) {
							jQuery( val.selectors ).each( function( i, v ) {
								v.style.setProperty( 'color', to, 'important' );
							} );
						}
					);
				}
			);

			wp.customize(
				id + '_font_size',
				function( value ) {
					value.bind(
						function( to ) {
							jQuery( val.selectors ).each( function( i, v ) {
								if ( to === '' ) {
									wp.customize.preview.send( 'refresh' );
								} else {
									v.style.setProperty( 'font-size', to + 'px', 'important' );
								}
							} );
						}
					);
				}
			);

			wp.customize(
				id + '_line_height',
				function( value ) {
					value.bind(
						function( to ) {
							jQuery( val.selectors ).each( function( i, v ) {
								v.style.setProperty( 'line-height', to, 'important' );
							} );
						}
					);
				}
			);

			wp.customize(
				id + '_text_transform',
				function( value ) {
					value.bind(
						function( to ) {
							jQuery( val.selectors ).each( function( i, v ) {
								v.style.setProperty( 'text-transform', to, 'important' );
							} );
						}
					);
				}
			);

			wp.customize(
				id + '_text_decoration',
				function( value ) {
					value.bind(
						function( to ) {
							jQuery( val.selectors ).each( function( i, v ) {
								v.style.setProperty( 'text-decoration', to, 'important' );
							} );
						}
					);
				}
			);

			wp.customize(
				id + '_letter_spacing',
				function( value ) {
					value.bind(
						function( to ) {
							jQuery( val.selectors ).each( function( i, v ) {
								if ( to === '' ) {
									wp.customize.preview.send( 'refresh' );
								} else {
									v.style.setProperty( 'letter-spacing', to + 'px', 'important' );
								}
							} );
						}
					);
				}
			);

		} );
	}
); // jQuery( document ).ready
