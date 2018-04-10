<?php
/**
 * Helper functions.
 *
 * @package     olympus-google-fonts
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! function_exists( 'ogf_fonts_array' ) ) :
	/**
	 * Return an array of all available Google Fonts.
	 *
	 * @since  1.0.0.
	 *
	 * @return array    All Google Fonts.
	 */
	function ogf_fonts_array() {

		$fonts_json = file_get_contents( plugin_dir_path( __FILE__ ) . 'fonts.json' );

		// Change the object to a multidimensional array.
		$fonts_array = json_decode( $fonts_json, true );

		// Change the array key to the font's ID.
		foreach ( $fonts_array['items'] as $font ) {

			$id = trim( strtolower( str_replace( ' ', '-', $font['family'] ) ) );

			$fonts[ $id ] = $font;

		}

		return $fonts;
	}
endif;

/**
 * Build a font stack using the users font choice.
 *
 * @param string $font_id The users font choice.
 * @return string The built font stack.
 */
function ogf_build_font_stack( $font_id ) {

	$google_fonts = ogf_fonts_array();

	$sans      = '"Helvetica Neue", Helvetica, Arial, sans-serif';
	$serif     = 'Georgia, Times, "Times New Roman", serif';
	$monospace = '"Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace;';

	if ( array_key_exists( $font_id, $google_fonts ) ) {

		if ( 'monospace' === $google_fonts[ $font_id ]['category'] ) {
			$stack = $monospace;
		} elseif ( 'serif' === $google_fonts[ $font_id ]['category'] ) {
			$stack = $serif;
		} else {
			$stack = $sans;
		}

		$stack = '"' . $google_fonts[ $font_id ]['family'] . '", ' . $stack;

		return $stack;

	}

}
