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
		add_filter( 'wp_theme_json_data_default', array( $this, 'add_font_families' ), 10, 1 );
		add_filter( 'block_editor_settings_all', array( $this, 'add_custom_fonts' ), 10, 2 );
	}

	/**
	 * Add Google, custom, and Typekit font families to Gutenberg.
	 *
	 * @param WP_Theme_JSON $theme_json The theme JSON object.
	 * @return WP_Theme_JSON The modified theme JSON object.
	 */
	public function add_font_families( $theme_json ) {
		$data = $theme_json->get_data();

		// Ensure typography settings exist.
		if ( ! isset( $data['settings']['typography'] ) ) {
			$data['settings']['typography'] = array();
		}

		// Ensure fontFamilies is always an array.
		if ( ! isset( $data['settings']['typography']['fontFamilies'] ) || ! is_array( $data['settings']['typography']['fontFamilies'] ) ) {
			$data['settings']['typography']['fontFamilies'] = array();
		}

		// Store existing fontFamilies to preserve them.
		$existing_font_families = $data['settings']['typography']['fontFamilies'];
		$new_font_families = array();

		// Add Google fonts.
		$fonts = OGF_Fonts::get_instance();
		$fonts = $fonts->choices;

		if ( ! empty( $fonts ) ) {
			foreach ( $fonts as $font ) {
				if ( ogf_is_google_font( $font ) && ! empty( $font ) ) {
					$family = OGF_Fonts::$google_fonts[ $font ]['f'];
					$new_font_families[] = array(
						'slug'       => sanitize_title( $font ),
						'fontFamily' => $family,
						'name'       => $family,
					);
				}
			}
		}

		// Add custom fonts.
		$custom_fonts = ogf_custom_fonts_unique();
		if ( ! empty( $custom_fonts ) && is_array( $custom_fonts ) ) {
			foreach ( $custom_fonts as $key => $value ) {
				if ( ! empty( $key ) && ! empty( $value ) ) {
					$new_font_families[] = array(
						'slug'       => sanitize_title( $key ),
						'fontFamily' => $value,
						'name'       => $value,
					);
				}
			}
		}

		// Add Typekit fonts.
		$typekit_fonts = ogf_typekit_fonts();
		if ( ! empty( $typekit_fonts ) && is_array( $typekit_fonts ) ) {
			foreach ( $typekit_fonts as $font ) {
				if ( ! empty( $font['id'] ) && ! empty( $font['label'] ) ) {
					$new_font_families[] = array(
						'slug'       => sanitize_title( $font['id'] ),
						'fontFamily' => $font['id'],
						'name'       => $font['label'],
					);
				}
			}
		}

		// Merge existing fonts with new fonts (new fonts override if slug matches).
		$all_font_families = array();
		$used_slugs = array();

		// Add new fonts first.
		foreach ( $new_font_families as $font_family ) {
			if ( ! in_array( $font_family['slug'], $used_slugs, true ) ) {
				$all_font_families[] = $font_family;
				$used_slugs[] = $font_family['slug'];
			}
		}

		// Add existing fonts if their slugs aren't already used.
		foreach ( $existing_font_families as $font_family ) {
			if ( is_array( $font_family ) && isset( $font_family['slug'] ) && ! in_array( $font_family['slug'], $used_slugs, true ) ) {
				$all_font_families[] = $font_family;
				$used_slugs[] = $font_family['slug'];
			}
		}

		// Set the final font families array.
		$data['settings']['typography']['fontFamilies'] = $all_font_families;

		$theme_json->update_with( $data );
		return $theme_json;
	}

	/**
	 * Add custom (uploaded) fonts to Gutenberg block editor iframe using block_editor_settings_all filter.
	 *
	 * @param array  $editor_settings An array containing the current Editor settings.
	 * @param string $editor_context  The context of the editor.
	 * @return array Modified editor settings with the added custom CSS style.
	 */
	public function add_custom_fonts( $editor_settings, $editor_context ) {
		// Define CSS for Gutenberg block editor.
		$custom_css = ogf_return_custom_font_css();

		// Add the custom CSS to editor settings (this gets injected into the iframe).
		$editor_settings['styles'][] = array(
			'css' => $custom_css,
		);

		return $editor_settings;
	}
}

$filters = new OGF_Gutenberg_Filters();