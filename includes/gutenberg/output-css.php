<?php
/**
 * Output the Google Fonts CSS in Gutenberg.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Enqeue the Google Fonts URL.
 */
function ogf_gutenberg_enqueue_fonts() {

	$fonts = new OGF_Fonts();

	if ( $fonts->has_custom_fonts() ) {
		$url = $fonts->build_url();
		wp_enqueue_style( 'olympus-google-fonts', $url, array(), OGF_VERSION );
	}

}
add_action( 'enqueue_block_editor_assets', 'ogf_gutenberg_enqueue_fonts' );

/**
 * Output the font CSS to wp_head.
 */
function ogf_gutenberg_output_css() {

	// Only load on Gutenberg-enabled pages.
	global $current_screen;
	$current_screen = get_current_screen();
	if ( ! method_exists( $current_screen, 'is_block_editor' ) || ! $current_screen->is_block_editor() ) {
			return;
	}

	?>
	<!-- Fonts Plugin Gutenberg CSS - https://fontsplugin.com/ -->
	<style>
		<?php

		do_action( 'ogf_gutenberg_inline_styles' );

		$elements = array(
			'ogf_body' => array(
				'selectors' => '.editor-styles-wrapper p, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6, #editor .editor-styles-wrapper .editor-post-title__block .editor-post-title__input',
			),
			'ogf_headings' => array(
				'selectors' => '#editor .editor-styles-wrapper .editor-post-title__block .editor-post-title__input, .editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6',
			),
			'ogf_post_page_content' => array(
				'selectors' => '.editor-styles-wrapper p',
			),
			'ogf_post_page_h1' => array(
				'selectors' => '#editor .editor-styles-wrapper .editor-post-title__block .editor-post-title__input, .editor-styles-wrapper h1',
			),
			'ogf_post_page_h2' => array(
				'selectors' => '.editor-styles-wrapper h2',
			),
			'ogf_post_page_h3' => array(
				'selectors' => '.editor-styles-wrapper h3',
			),
			'ogf_post_page_h4' => array(
				'selectors' => '.editor-styles-wrapper h4',
			),
			'ogf_post_page_h5' => array(
				'selectors' => '.editor-styles-wrapper h5',
			),
			'ogf_post_page_h6' => array(
				'selectors' => '.editor-styles-wrapper h6',
			),
		);

		$elements = apply_filters( 'ogf_gutenberg_elements', $elements );

		foreach ( $elements as $id => $values ) {
			ogf_generate_css_gutenberg( $values['selectors'], $id );
		}
		?>
	</style>
	<!-- Fonts Plugin Gutenberg CSS -->
	<?php
}

// Output custom CSS to live site.
add_action( 'admin_head', 'ogf_gutenberg_output_css' );

/**
 * Helper function to build the CSS styles.
 *
 * @param string $selector    The CSS selector to apply the styles to.
 * @param string $option_name The option name to pull from the database.
 */
function ogf_generate_css_gutenberg( $selector, $option_name ) {

	$family      = get_theme_mod( $option_name . '_font', false );
	$font_size   = get_theme_mod( $option_name . '_font_size', false );
	$line_height = get_theme_mod( $option_name . '_line_height', false );
	$weight      = get_theme_mod( $option_name . '_font_weight', false );
	$style       = get_theme_mod( $option_name . '_font_style', false );
	$color       = get_theme_mod( $option_name . '_font_color', false );

	$return = '';

	if ( ( $family !== 'default' && $family ) ||
			 ( $line_height !== '0' && $line_height ) ||
			 ( $weight !== '0' && $weight ) ||
			 ( $style !== 'default' && $style ) ||
			   $font_size ||
			   $color ) {

		$return .= $selector . ' {' . PHP_EOL;

		// Return font-family CSS.
		if ( false !== $family && 'default' !== $family ) {

			$stack = ogf_build_font_stack( $family );

			if ( ! empty( $stack ) ) {
				$return .= sprintf(
					'font-family: %s;' . PHP_EOL,
					$stack
				);
			}
		}

		// Return font-size CSS.
		if ( $font_size ) {
			$return .= sprintf(
				'font-size: %s;' . PHP_EOL,
				floatval( $font_size ) . 'px'
			);
		}

		// Return font line-height CSS.
		if ( $line_height && '0' !== $line_height ) {
			$return .= sprintf(
				'line-height: %s;' . PHP_EOL,
				floatval( $line_height )
			);
		}

		// Return font-style CSS.
		if ( $style && 'default' !== $style ) {
			$return .= sprintf(
				'font-style: %s;' . PHP_EOL,
				esc_attr( $style )
			);
		}

		// Return font-weight CSS.
		if ( $weight && '0' !== $weight ) {
			$return .= sprintf(
				'font-weight: %s;' . PHP_EOL,
				absint( $weight )
			);
		}

		// Return font-color CSS.
		if ( $color ) {
			$return .= sprintf(
				'color: %s;' . PHP_EOL,
				esc_attr( $color )
			);
		}

		$return .= ' }' . PHP_EOL;

		echo wp_kses_post( $return );

	}

}
