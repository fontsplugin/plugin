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
	<!-- Olympus Google Fonts CSS-->
	<style type="text/css">

		<?php ogf_generate_css( 'body', 'ogf_body_font' ); ?>
		<?php ogf_generate_css( '.site-title, h1, h2, h3, h4, h5, h6', 'ogf_headings_font' ); ?>
		<?php ogf_generate_css( 'button, input, select, textarea', 'ogf_inputs_font' ); ?>

	</style>
	<!--/Customizer CSS-->
	<?php
}

// Output custom CSS to live site.
add_action( 'wp_head' , 'ogf_output_css' );

/**
 * Helper function to build the CSS styles.
 *
 * @param string $selector The CSS selector to apply the styles to.
 * @param string $option_name The option name to pull from the database.
 */
function ogf_generate_css( $selector, $option_name ) {
	$return = '';

	$stack = ogf_build_font_stack( get_theme_mod( $option_name ) );

	if ( ! empty( $stack ) && 'default' !== $stack ) {
		$return = sprintf('%s { font-family: %s; }',
			$selector,
			$stack . ogf_is_forced()
		);
	}
	echo wp_kses_post( $return );
}

/**
 * Check if the styles should be forced.
 */
function ogf_is_forced() {

	if ( '1' === get_theme_mod( 'ogf_force_styles' ) ) {
		return ' !important';
	}

}
