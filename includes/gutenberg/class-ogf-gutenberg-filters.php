<?php
/**
 * Filters for Gutenberg.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Gutenberg filters.
 */
class OGF_Gutenberg_Filters {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'wp_theme_json_data_user', array( $this, 'add_font_families' ), 10, 1 );
	}

	/**
	 * Add Google, custom, and Typekit font families to Gutenberg.
	 *
	 * @param WP_Theme_JSON $theme_json The theme JSON object.
	 * @return WP_Theme_JSON The modified theme JSON object.
	 */
	public function add_font_families( $theme_json ) {
		$data = $theme_json->get_data();

		// Set the typography settings if they're not set.
		if ( ! isset( $data['settings']['typography'] ) ) {
			$data['settings']['typography'] = array(
				'fontFamilies' => array(),
			);
		}

		$fonts = OGF_Fonts::get_instance();
		$fonts = $fonts->choices;

		if ( ! empty( $fonts ) ) {
			foreach ( $fonts as $font ) {
				if ( ogf_is_google_font( $font ) ) {
					$family = OGF_Fonts::$google_fonts[ $font ]['f'];
					$data['settings']['typography']['fontFamilies'][] = array(
						'fontFamily' => $family,
						'name'       => $family,
						'slug'       => $font,
					);
				}
			}
		}

		$custom_fonts = ogf_custom_fonts_unique();
		foreach ( $custom_fonts as $key => $value ) {
			$data['settings']['typography']['fontFamilies'][] = array(
				'fontFamily' => $key,
				'name'       => $value,
				'slug'       => $key,
			);
		}

		$fonts = ogf_typekit_fonts();
		if ( ! empty( $fonts ) ) {
			foreach ( $fonts as $font ) {
				$data['settings']['typography']['fontFamilies'][] = array(
					'fontFamily' => $font['id'],
					'name'       => $font['label'],
					'slug'       => $font['id'],
				);
			}
		}

		$theme_json->update_with( $data );
		return $theme_json;
	}
}

$filters = new OGF_Gutenberg_Filters();