<?php
/**
 * Compatibility file for Elegant Themes (Divi).
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Font weights Divi expects on entries merged with Google Fonts (see Divi Theme Customizer).
 *
 * @return string
 */
function ogf_elegantthemes_divi_default_styles() {
	return '100,200,300,400,500,600,700,800,900,100italic,200italic,300italic,400italic,500italic,600italic,700italic,800italic,900italic';
}

/**
 * Register a font with Divi's custom font list without clobbering an existing entry.
 *
 * @param array        $fonts           List passed through et_builder_custom_fonts (modified by reference).
 * @param string       $name            Font family name as shown in the builder.
 * @param string|array $font_url        Asset URLs per format, or empty string if the plugin loads the face elsewhere.
 * @param string       $styles          Comma-separated weights (required by Divi when merged in customizer).
 * @param string|null  $character_set   Optional subsets string.
 */
function ogf_elegantthemes_register_builder_font( &$fonts, $name, $font_url, $styles, $character_set = null ) {
	if ( '' === $name || null === $name ) {
		return;
	}
	if ( isset( $fonts[ $name ] ) ) {
		return;
	}
	$fonts[ $name ] = array(
		'font_url' => $font_url,
		'styles'   => $styles,
		'type'     => 'sans-serif',
	);
	if ( null !== $character_set && '' !== $character_set ) {
		$fonts[ $name ]['character_set'] = $character_set;
	}
}

/**
 * Add uploaded custom fonts and Adobe Fonts to the Divi custom fonts list.
 *
 * @param array $fonts The default font list.
 * @return array
 */
add_filter(
	'et_builder_custom_fonts',
	function ( $fonts ) {
		$default_styles = ogf_elegantthemes_divi_default_styles();
		$wide_subsets   = 'cyrillic,latin';

		if ( function_exists( 'ogf_custom_fonts' ) ) {
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
					'font_url'      => $font_url,
					'styles'        => $default_styles,
					'type'          => 'sans-serif',
					'character_set' => $wide_subsets,
				);
			}
		}

		if ( function_exists( 'ogf_typekit_fonts' ) ) {
			foreach ( ogf_typekit_fonts() as $kit_font ) {
				if ( empty( $kit_font['id'] ) ) {
					continue;
				}
				$styles = $default_styles;
				if ( ! empty( $kit_font['variants'] ) && is_array( $kit_font['variants'] ) ) {
					$styles = implode( ',', $kit_font['variants'] );
				}
				ogf_elegantthemes_register_builder_font( $fonts, $kit_font['id'], '', $styles, $wide_subsets );
			}
		}

		return $fonts;
	},
	10,
	1
);
