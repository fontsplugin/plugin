<?php
/**
 * Helper functions.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * An array of user-defined elements that can be customized using the plugin.
 */
function ogf_get_custom_elements() {

	$theme_mod = get_theme_mod( 'ogf_custom_selectors', false );

	if ( ! $theme_mod ) {
		return array();
	}

	$custom_selectors = json_decode( $theme_mod, true );

	foreach ( $custom_selectors as &$selector ) {
		$selector['section'] = 'ogf_advanced__custom';
	}

	return $custom_selectors;

}

/**
 * An array of elements that can be customized using the plugin.
 */
function ogf_get_elements() {

	$elements = array(
		'ogf_body' => array(
			'label'       => esc_html__( 'Base Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your content.', 'olympus-google-fonts' ),
			'section'     => 'ogf_basic',
			'selectors'   => 'body, #content, .entry-content, .post-content, .page-content, .post-excerpt, .entry-summary, .entry-excerpt, .widget-area, .widget, .sidebar, #sidebar, footer, .footer, #footer, .site-footer',
		),
		'ogf_headings' => array(
			'label'       => esc_html__( 'Headings Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_basic',
			'selectors'   => '#site-title, .site-title, #site-title a, .site-title a, .entry-title, .entry-title a, h1, h2, h3, h4, h5, h6, .widget-title',
		),
		'ogf_inputs' => array(
			'label'       => esc_html__( 'Buttons and Inputs Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your input fields and buttons.', 'olympus-google-fonts' ),
			'section'     => 'ogf_basic',
			'selectors'   => 'button, input, select, textarea',
		),
		'ogf_site_title' => array(
			'label'       => esc_html__( 'Site Title Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your site title.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__branding',
			'selectors'   => '#site-title, .site-title, #site-title a, .site-title a, #logo, #logo a, .logo, .logo a',
		),
		'ogf_site_description' => array(
			'label'       => esc_html__( 'Site Description Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your site description.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__branding',
			'selectors'   => '#site-description, .site-description',
		),
		'ogf_site_navigation' => array(
			'label'       => esc_html__( 'Navigation Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your site navigation.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__navigation',
			'selectors'   => '.menu, .page_item a, .menu-item a',
		),
		'ogf_post_page_content' => array(
			'label'       => esc_html__( 'Content Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your post and page content.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content, .entry-content p, .post-content, .page-content, .post-excerpt, .entry-summary, .entry-excerpt, .excerpt, .excerpt p',
		),
		'ogf_post_page_h1' => array(
			'label'       => esc_html__( 'Title and H1 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your title and H1 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-title, .entry-title a, .post-title, .post-title a, .page-title, .entry-content h1, #content h1',
		),
		'ogf_post_page_h2' => array(
			'label'       => esc_html__( 'H2 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H2 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h2, .post-content h2, .page-content h2, #content h2',
		),
		'ogf_post_page_h3' => array(
			'label'       => esc_html__( 'H3 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H3 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h3, .post-content h3, .page-content h3, #content h3',
		),
		'ogf_post_page_h4' => array(
			'label'       => esc_html__( 'H4 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H4 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h4, .post-content h4, .page-content h4, #content h4',
		),
		'ogf_post_page_h5' => array(
			'label'       => esc_html__( 'H5 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H5 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h5, .post-content h5, .page-content h5, #content h5',
		),
		'ogf_post_page_h6' => array(
			'label'       => esc_html__( 'H6 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H6 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h6, .post-content h6, .page-content h6, #content h6',
		),
		'ogf_sidebar_headings' => array(
			'label'       => esc_html__( 'Headings Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your sidebar headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__sidebar',
			'selectors'   => '.widget-title, .widget-area h1, .widget-area h2, .widget-area h3, .widget-area h4, .widgets-area h5, .widget-area h6',
		),
		'ogf_sidebar_content' => array(
			'label'       => esc_html__( 'Content Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your sidebar content.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__sidebar',
			'selectors'   => '.widget-area, .widget, .sidebar, #sidebar',
		),
		'ogf_footer_headings' => array(
			'label'       => esc_html__( 'Headings Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your footer headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__footer',
			'selectors'   => 'footer h1, footer h2, footer h3, footer h4, footer h5, footer h6,
												.footer h1, .footer h2, .footer h3, .footer h4, .footer h5, .footer h6
												#footer h1, #footer h2, #footer h3, #footer h4, #footer h5, #footer h6',
		),
		'ogf_footer_content' => array(
			'label'       => esc_html__( 'Content Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your footer content.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__footer',
			'selectors'   => 'footer, #footer, .footer, .site-footer',
		),
	);

	return apply_filters( 'ogf_elements', $elements );

}

/**
 * Return an array of all available Google Fonts.
 *
 * @return array    All Google Fonts.
 */
function ogf_fonts_array() {

	$fonts_json = file_get_contents( OGF_DIR_PATH . '/blocks/src/google-fonts/fonts.json' );

	// Change the object to a multidimensional array.
	$fonts_array = json_decode( $fonts_json, true );

	// Format the variants array for easier use.
	foreach ( $fonts_array['items'] as $key => $font ) {

		$variants_remove = array(
			'italic',
			'100italic',
			'200italic',
			'300italic',
			'400italic',
			'500italic',
			'600italic',
			'700italic',
			'800italic',
			'900italic',
		);

		// remove italic variants.
		$font['variants'] = array_diff( $font['variants'], $variants_remove );

		// replace regular with numberic identifier.
		$font['variants'] = str_replace( 'regular', '400', $font['variants'] );

		// flip the key/values.
		$font['variants'] = array_flip( $font['variants'] );

		$fonts_array['items'][ $key ] = $font;

	}

	// Change the array key to the font's ID.
	foreach ( $fonts_array['items'] as $font ) {
		$id           = trim( strtolower( str_replace( ' ', '-', $font['family'] ) ) );
		$fonts[ $id ] = $font;
	}

	return $fonts;
}

/**
 * Return a array of system fonts.
 */
function ogf_system_fonts() {
	$system_fonts = array(
		'arial' => array(
			'id'    => 'arial',
			'label' => esc_html__( 'Arial', 'olympus-google-fonts' ),
			'stack' => 'Arial, Helvetica Neue, Helvetica, sans-serif',
		),
		'calibri' => array(
			'id'    => 'calibri',
			'label' => esc_html__( 'Calibri', 'olympus-google-fonts' ),
			'stack' => 'Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;',
		),
		'century-gothic' => array(
			'id'    => 'century-gothic',
			'label' => esc_html__( 'Century Gothic', 'olympus-google-fonts' ),
			'stack' => 'Century Gothic, CenturyGothic, AppleGothic, sans-serif;',
		),
		'consolas' => array(
			'id'    => 'consolas',
			'label' => esc_html__( 'Consolas', 'olympus-google-fonts' ),
			'stack' => 'Consolas, monaco, monospace',
		),
		'courier-new' => array(
			'id'    => 'courier-new',
			'label' => esc_html__( 'Courier New', 'olympus-google-fonts' ),
			'stack' => 'Courier New, Courier, Lucida Sans Typewriter, Lucida Typewriter, monospace',
		),
		'helvetica' => array(
			'id'    => 'helvetica',
			'label' => esc_html__( 'Helvetica Neue', 'olympus-google-fonts' ),
			'stack' => 'Helvetica Neue, Helvetica, Arial, sans-serif',
		),
		'georgia' => array(
			'id'    => 'georgia',
			'label' => esc_html__( 'Georgia', 'olympus-google-fonts' ),
			'stack' => 'Georgia, Times, Times New Roman, serif',
		),
		'futura' => array(
			'id'    => 'futura',
			'label' => esc_html__( 'Futura', 'olympus-google-fonts' ),
			'stack' => 'Futura, Trebuchet MS, Arial, sans-serif',
		),
		'lucida-grande' => array(
			'id'    => 'lucida-grande',
			'label' => esc_html__( 'Lucida Grande', 'olympus-google-fonts' ),
			'stack' => 'Lucida Grande, Lucida Sans Unicode, Lucida Sans, Geneva, Verdana, sans-serif',
		),
		'segoe-ui' => array(
			'id'    => 'segoe-ui',
			'label' => esc_html__( 'Segoe UI', 'olympus-google-fonts' ),
			'stack' => 'Segoe UI, Frutiger, Frutiger Linotype, Dejavu Sans, Helvetica Neue, Arial, sans-serif',
		),
		'tahoma' => array(
			'id'    => 'tahoma',
			'label' => esc_html__( 'Tahoma', 'olympus-google-fonts' ),
			'stack' => 'Tahoma, Verdana, Segoe, sans-serif',
		),
		'times-new-roman' => array(
			'id'    => 'times-new-roman',
			'label' => esc_html__( 'Times New Roman', 'olympus-google-fonts' ),
			'stack' => 'TimesNewRoman, Times New Roman, Times, Baskerville, Georgia, serif',
		),
		'trebuchet' => array(
			'id'    => 'trebuchet',
			'label' => esc_html__( 'Trebuchet MS', 'olympus-google-fonts' ),
			'stack' => 'Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif',
		),
		'palatino' => array(
			'id'    => 'palatino',
			'label' => esc_html__( 'Palatino', 'olympus-google-fonts' ),
			'stack' => 'Palatino, Palatino Linotype, Palatino LT STD, Book Antiqua, Georgia, serif',
		),
		'verdana' => array(
			'id'    => 'verdana',
			'label' => esc_html__( 'Verdana', 'olympus-google-fonts' ),
			'stack' => 'Verdana, Geneva, sans-serif;',
		),
	);

	$filtered_system_fonts = apply_filters( 'ogf_system_fonts', $system_fonts );

	return $filtered_system_fonts;

}

/**
 * Return the full range of font variants.
 */
function ogf_font_variants() {
	return array(
		'0'    => esc_html__( '- Default -', 'olympus-google-fonts' ),
		'100'  => esc_html__( 'Thin', 'olympus-google-fonts' ),
		'200'  => esc_html__( 'Extra Light', 'olympus-google-fonts' ),
		'300'  => esc_html__( 'Light', 'olympus-google-fonts' ),
		'400'  => esc_html__( 'Normal', 'olympus-google-fonts' ),
		'500'  => esc_html__( 'Medium', 'olympus-google-fonts' ),
		'600'  => esc_html__( 'Semi Bold', 'olympus-google-fonts' ),
		'700'  => esc_html__( 'Bold', 'olympus-google-fonts' ),
		'800'  => esc_html__( 'Extra Bold', 'olympus-google-fonts' ),
		'900'  => esc_html__( 'Ultra Bold', 'olympus-google-fonts' ),
		'100i' => esc_html__( 'Thin Italic', 'olympus-google-fonts' ),
		'200i' => esc_html__( 'Extra Light Italic', 'olympus-google-fonts' ),
		'300i' => esc_html__( 'Light Italic', 'olympus-google-fonts' ),
		'400i' => esc_html__( 'Normal Italic', 'olympus-google-fonts' ),
		'500i' => esc_html__( 'Medium Italic', 'olympus-google-fonts' ),
		'600i' => esc_html__( 'Semi Bold Italic', 'olympus-google-fonts' ),
		'700i' => esc_html__( 'Bold Italic', 'olympus-google-fonts' ),
		'800i' => esc_html__( 'Extra Bold Italic', 'olympus-google-fonts' ),
		'900i' => esc_html__( 'Ultra Bold Italic', 'olympus-google-fonts' ),
	);
}

/**
 * Check if a font is a system font (not Google Font).
 *
 * @param string $font_id The ID of the font to check.
 */
function ogf_is_system_font( $font_id ) {
	if ( strpos( $font_id, 'sf-' ) === 0 ) {
		return true;
	}
	return false;
}
