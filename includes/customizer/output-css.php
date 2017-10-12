<?php
/**
 * Output the Google Fonts CSS.
 *
 * @package     google-fonts-wp
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * @todo
 */
function gfwp_output_css() {
	?>
	<!-- Google Fonts for WP CSS-->
	<style type="text/css">


		<?php gfwp_generate_css( 'body', 'gfwp_body_font' ); ?>
		<?php gfwp_generate_css( 'h1, h2, h3, h4, h5, h6', 'gfwp_headings_font' ); ?>
		<?php gfwp_generate_css( 'button, input, select, textarea', 'gfwp_inputs_font' ); ?>

	</style>
	<!--/Customizer CSS-->
	<?php
}

// Output custom CSS to live site.
add_action( 'wp_head' , 'gfwp_output_css' );

/**
 * @TODO
 */
function gfwp_generate_css( $selector, $option_name ) {
	$return = '';

	$stack = gfwp_build_font_stack( get_theme_mod( $option_name ) );

	if ( ! empty( $stack ) && 'default' !== $stack ) {
		$return = sprintf('%s { font-family: %s; }',
			$selector,
			$stack
		);
	}
	echo wp_kses_post( $return );
}
