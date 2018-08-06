<?php
/**
 * Output the Google Fonts CSS.
 *
 * @package     olympus-google-fonts
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Output the font CSS to wp_head.
 */
function ogf_output_css() {
	?>
	<!-- Olympus Google Fonts CSS - https://wordpress.org/plugins/olympus-google-fonts/ -->
	<style>
		<?php ogf_generate_css( 'body', 'ogf_body_font' ); ?>
		<?php ogf_generate_css( '.site-title, h1, h2, h3, h4, h5, h6', 'ogf_headings_font' ); ?>
		<?php ogf_generate_css( 'button, input, select, textarea', 'ogf_inputs_font' ); ?>

		/* Advanced Settings */

		<?php ogf_generate_css( '.site-title', 'ogf_site_title_font' ); ?>
		<?php ogf_generate_css( '.site-description', 'ogf_site_description_font' ); ?>
		<?php ogf_generate_css( '.menu, .page_item, .menu-item', 'ogf_navigation_font' ); ?>
		<?php ogf_generate_css( '.entry-title, .entry-content h1, .entry-content h2, .entry-content h3, .entry-content h4, .entry-content h5, .entry-content h6', 'ogf_post_page_headings_font' ); ?>
		<?php ogf_generate_css( '.entry-content', 'ogf_post_page_content_font' ); ?>
		<?php ogf_generate_css( '.widget-area h1, .widget-area h2, .widget-area h3, .widget-area h4, .widgets-area h5, .widget-area h6', 'ogf_sidebar_headings_font' ); ?>
		<?php ogf_generate_css( '.widget-area', 'ogf_sidebar_content_font' ); ?>
		<?php ogf_generate_css( 'footer h1, footer h2, footer h3, footer h4, .widgets-area h5, footer h6', 'ogf_footer_headings_font' ); ?>
		<?php ogf_generate_css( 'footer', 'ogf_footer_content_font' ); ?>
	</style>
	<!-- Olympus Google Fonts CSS -->
	<?php
}

// Output custom CSS to live site.
add_action( 'wp_head', 'ogf_output_css' );

/**
 * Helper function to build the CSS styles.
 *
 * @param string $selector The CSS selector to apply the styles to.
 * @param string $option_name The option name to pull from the database.
 */
function ogf_generate_css( $selector, $option_name ) {

	$family = get_theme_mod( $option_name, false );
	$weight = get_theme_mod( $option_name . '_weight', false );
	$style  = get_theme_mod( $option_name . '_style', false );

	$return = '';

	if ( $family || $weight || $style ) {

		$return .= $selector . ' {' . PHP_EOL;

		// Return font-family CSS.
		if ( false !== $family && 'default' !== $family ) {

			$stack = ogf_build_font_stack( $family );

			if ( ! empty( $stack ) ) {
				$return .= sprintf('font-family: %s;' . PHP_EOL,
					$stack . ogf_is_forced()
				);
			}
		}

		// Return font-weight CSS.
		if ( false !== $weight && '0' !== $weight ) {
				$return .= sprintf('font-weight: %s;' . PHP_EOL,
					absint( $weight ) . ogf_is_forced()
				);
		}

		// Return font-style CSS.
		if ( false !== $style && 'normal' !== $style ) {
				$return .= sprintf('font-style: %s;' . PHP_EOL,
					esc_attr( $style ) . ogf_is_forced()
				);
		}

		$return .= ' }' . PHP_EOL;

		echo wp_kses_post( $return );

	}
}

/**
 * Check if the styles should be forced.
 */
function ogf_is_forced() {

	if ( '1' === get_theme_mod( 'ogf_force_styles' ) ) {
		return ' !important';
	}

}
