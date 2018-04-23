jQuery( document ).ready( function() {

	var important = '';
	console.log(important);

	wp.customize.bind( 'preview-ready', function() {

	  wp.customize.preview.bind( 'olympusFontURL', function( url ) {
			 $("head").append( url );
	  } );
	} );

	function fontFamilyChange( selector, value ) {
		if( value == 'default' ) {
			jQuery( selector ).css( 'font-family', '' );
		} else {
			jQuery( selector ).css( 'font-family', value.replace('-', ' ') );
		}
	}

	/* === Base Typography === */

	wp.customize(
		'ogf_body_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( 'body', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_body_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( 'body' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_body_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( 'body' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Headings Typography === */

	wp.customize(
		'ogf_headings_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( '.site-title, .widget-title, h1, h2, h3, h4, h5, h6', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_headings_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( '.site-title, .widget-title, h1, h2, h3, h4, h5, h6' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_headings_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( '.site-title, .widget-title, h1, h2, h3, h4, h5, h6' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Inputs Typography === */

	wp.customize(
		'ogf_inputs_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( 'button, input, select, textarea', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_inputs_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( 'button, input, select, textarea' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_inputs_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( 'button, input, select, textarea' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Site Title Typography === */

	wp.customize(
		'ogf_site_title_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( '.site-title', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_site_title_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( '.site-title' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_site_title_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( '.site-title' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Site Description Typography === */

	wp.customize(
		'ogf_site_description_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( '.site-description', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_site_description_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( '.site-description' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_site_description_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( '.site-description' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Navigation Typography === */

	wp.customize(
		'ogf_navigation_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( '.menu, .page_item, .menu-item', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_navigation_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( '.menu, .page_item, .menu-item' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_navigation_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( '.menu, .page_item, .menu-item' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Post & Pages Heading Typography === */

	wp.customize(
		'ogf_post_page_headings_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( 'article h1, article h2, article h3, article h4, article h5, article h6', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_post_page_headings_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( 'article h1, article h2, article h3, article h4, article h5, article h6' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_post_page_headings_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( 'article h1, article h2, article h3, article h4, article h5, article h6' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Post & Pages Content Typography === */

	wp.customize(
		'ogf_post_page_content_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( 'article', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_post_page_content_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( 'article' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_post_page_content_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( 'article' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Sidebar Headings Typography === */

	wp.customize(
		'ogf_sidebar_headings_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( '.widget-title, .widget-area h1, .widget-area h2, .widget-area h3, .widget-area h4, .widgets-area h5, .widget-area h6', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_sidebar_headings_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( '.widget-title, .widget-area h1, .widget-area h2, .widget-area h3, .widget-area h4, .widgets-area h5, .widget-area h6' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_sidebar_headings_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( '.widget-title, .widget-area h1, .widget-area h2, .widget-area h3, .widget-area h4, .widgets-area h5, .widget-area h6' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Sidebar Content Typography === */

	wp.customize(
		'ogf_sidebar_content_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( '.widget-area, .widget', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_sidebar_content_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( '.widget-area, .widget' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_sidebar_content_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( '.widget-area, .widget' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Footer Headings Typography === */

	wp.customize(
		'ogf_footer_headings_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( 'footer h1, footer h2, footer h3, footer h4, .widgets-area h5, footer h6', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_footer_headings_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( 'footer h1, footer h2, footer h3, footer h4, .widgets-area h5, footer h6' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_footer_headings_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( 'footer h1, footer h2, footer h3, footer h4, .widgets-area h5, footer h6' ).css( 'font-style', to );
				}
			);
		}
	);

	/* === Footer Content Typography === */

	wp.customize(
		'ogf_footer_content_font',
		function( value ) {
			value.bind(
				function( to ) {
					fontFamilyChange( 'footer', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_footer_content_font_weight',
		function( value ) {
			value.bind(
				function( to ) {
					if( to == '0') to =  '';
					jQuery( 'footer' ).css( 'font-weight', to );
				}
			);
		}
	);

	wp.customize(
		'ogf_footer_content_font_style',
		function( value ) {
			value.bind(
				function( to ) {
					jQuery( 'footer' ).css( 'font-style', to );
				}
			);
		}
	);

} ); // jQuery( document ).ready
