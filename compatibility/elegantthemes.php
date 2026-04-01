<?php
/**
 * Compatibility file for Elegant Themes (Divi).
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Add uploaded custom fonts to the Elegant Themes (Divi) custom fonts list.
 *
 * @param array $fonts The default font list.
 * @return array
 */
add_filter(
	'et_builder_custom_fonts',
	function ( $fonts ) {
		if ( ! function_exists( 'ogf_custom_fonts' ) ) {
			return $fonts;
		}

		foreach ( ogf_custom_fonts() as $font ) {
			$files = isset( $font['files'] ) && is_array( $font['files'] ) ? $font['files'] : array();

			$font_url = array();
			foreach ( array( 'woff2', 'woff', 'ttf', 'otf' ) as $format ) {
				if ( ! empty( $files[ $format ] ) ) {
					$font_url[ $format ] = esc_url_raw( $files[ $format ] );
				}
			}

			if ( empty( $font_url ) ) {
				continue;
			}

			$name = ! empty( $font['family'] ) ? $font['family'] : ( isset( $font['label'] ) ? $font['label'] : '' );
			if ( ! $name ) {
				continue;
			}

			$fonts[ $name ] = array(
				'font_url' => $font_url,
			);
		}

		return $fonts;
	},
	10,
	1
);
