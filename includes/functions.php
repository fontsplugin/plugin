<?php
/**
 * Helper functions.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2019, Danny Cooper
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
		'ogf_body'              => array(
			'label'       => esc_html__( 'Base Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your content.', 'olympus-google-fonts' ),
			'section'     => 'ogf_basic',
			'selectors'   => 'body',
		),
		'ogf_headings'          => array(
			'label'       => esc_html__( 'Headings Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_basic',
			'selectors'   => '#site-title, .site-title, #site-title a, .site-title a, .entry-title, .entry-title a, h1, h2, h3, h4, h5, h6',
		),
		'ogf_inputs'            => array(
			'label'       => esc_html__( 'Buttons and Inputs Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your input fields and buttons.', 'olympus-google-fonts' ),
			'section'     => 'ogf_basic',
			'selectors'   => 'button, input, select, textarea',
		),
		'ogf_site_title'        => array(
			'label'       => esc_html__( 'Site Title Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your site title.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__branding',
			'selectors'   => '#site-title, .site-title, #site-title a, .site-title a, #logo, #logo a, .logo, .logo a',
		),
		'ogf_site_description'  => array(
			'label'       => esc_html__( 'Site Description Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your site description.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__branding',
			'selectors'   => '#site-description, .site-description',
		),
		'ogf_site_navigation'   => array(
			'label'       => esc_html__( 'Navigation Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your site navigation.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__navigation',
			'selectors'   => '.menu, .page_item a, .menu-item a',
		),
		'ogf_post_page_content' => array(
			'label'       => esc_html__( 'Content Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your post and page content.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content, .post-content, .page-content, .post-excerpt, .entry-summary, .entry-excerpt',
		),
		'ogf_post_page_h1'      => array(
			'label'       => esc_html__( 'Title and H1 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your title and H1 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-title, .entry-title a, .post-title, .post-title a, .page-title, .entry-content h1',
		),
		'ogf_post_page_h2'      => array(
			'label'       => esc_html__( 'H2 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H2 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h2, .post-content h2, .page-content h2',
		),
		'ogf_post_page_h3'      => array(
			'label'       => esc_html__( 'H3 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H3 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h3, .post-content h3, .page-content h3',
		),
		'ogf_post_page_h4'      => array(
			'label'       => esc_html__( 'H4 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H4 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h4, .post-content h4, .page-content h4',
		),
		'ogf_post_page_h5'      => array(
			'label'       => esc_html__( 'H5 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H5 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h5, .post-content h5, .page-content h5',
		),
		'ogf_post_page_h6'      => array(
			'label'       => esc_html__( 'H6 Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your H6 headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__content',
			'selectors'   => '.entry-content h6, .post-content h6, .page-content h6',
		),
		'ogf_sidebar_headings'  => array(
			'label'       => esc_html__( 'Headings Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your sidebar headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__sidebar',
			'selectors'   => '.widget-title, .widget-area h1, .widget-area h2, .widget-area h3, .widget-area h4, .widgets-area h5, .widget-area h6',
		),
		'ogf_sidebar_content'   => array(
			'label'       => esc_html__( 'Content Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your sidebar content.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__sidebar',
			'selectors'   => '.widget-area',
		),
		'ogf_footer_headings'   => array(
			'label'       => esc_html__( 'Headings Typography', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Select and configure the font for your footer headings.', 'olympus-google-fonts' ),
			'section'     => 'ogf_advanced__footer',
			'selectors'   => 'footer h1, footer h2, footer h3, footer h4, footer h5, footer h6,
												.footer h1, .footer h2, .footer h3, .footer h4, .footer h5, .footer h6
												#footer h1, #footer h2, #footer h3, #footer h4, #footer h5, #footer h6',
		),
		'ogf_footer_content'    => array(
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

	// Change the array key to the font's ID.
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

		$font['variants'] = array_diff( $font['variants'], $variants_remove );

		$font['variants'] = str_replace( 'regular', '400', $font['variants'] );

		$font['variants'] = array_flip( $font['variants'] );

		$weights = array(
			'100' => esc_html__( 'Thin', 'olympus-google-fonts' ),
			'200' => esc_html__( 'Extra Light', 'olympus-google-fonts' ),
			'300' => esc_html__( 'Light', 'olympus-google-fonts' ),
			'400' => esc_html__( 'Normal', 'olympus-google-fonts' ),
			'500' => esc_html__( 'Medium', 'olympus-google-fonts' ),
			'600' => esc_html__( 'Semi Bold', 'olympus-google-fonts' ),
			'700' => esc_html__( 'Bold', 'olympus-google-fonts' ),
			'800' => esc_html__( 'Extra Bold', 'olympus-google-fonts' ),
			'900' => esc_html__( 'Ultra Bold', 'olympus-google-fonts' ),
		);

		foreach ( $font['variants'] as $k => $v ) {
			$font['variants'][ $k ] = $weights[ $k ];
		}

		$font['variants']['0'] = esc_html__( '- Default -', 'olympus-google-fonts' );

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
 * Build the array for the select choices setting.
 */
function ogf_font_choices_for_select() {

	$fonts_array = ogf_fonts_array();

	$fonts = array(
		'default' => esc_html__( '- Default -', 'olympus-google-fonts' ),
	);

	foreach ( $fonts_array as $key => $value ) {
		$fonts[ $key ] = $value['family'];
	}

	return $fonts;

}
