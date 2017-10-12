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

		<?php gfwp_generate_css( 'body', 'font-family', 'gfwp_body_font' ); ?>
		<?php gfwp_generate_css( 'h1, h2, h3, h4, h5, h6', 'font-family', 'gfwp_header_font' ); ?>

	</style>
	<!--/Customizer CSS-->
	<?php
}

// Output custom CSS to live site.
add_action( 'wp_head' , 'gfwp_output_css' );

/**
 * @TODO
 */
function gfwp_generate_css( $selector, $style, $option_name ) {
	$return = '';

	$mod = get_theme_mod( $option_name );

	if ( ! empty( $mod ) ) {
		$return = sprintf('%s { %s:\'%s\'; }',
			$selector,
			$style,
			$mod
		);
	}
	echo $return;
}
