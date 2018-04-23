<?php
/**
 * Build the URL to load the chosen Google Fonts.
 *
 * @package     olympus-google-fonts
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * An array containing the customizer sections, settings and controls.
 *
 * @param object Access to the $wp_customize object.
 */
function ogf_customize_register( $wp_customize ) {

	require_once OGF_DIR_PATH . 'includes/customizer/controls/class-wp-customize-typography-control.php';

	$wp_customize->register_control_type( 'WP_Customize_Typography_Control' );

	$wp_customize->add_panel( 'olympus_google_fonts', array(
		'priority'       => 10,
		'capability'     => 'edit_theme_options',
		'theme_supports' => '',
		'title'          => esc_html__( 'Google Fonts', 'olympus-google-fonts' ),
	) );

	$wp_customize->add_section( 'olympus-google-fonts', array(
		'title'    => esc_html__( 'Basic Settings', 'olympus-google-fonts' ),
		'priority' => 1,
		'panel'    => 'olympus_google_fonts',
	) );

	$wp_customize->add_setting( 'ogf_body_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_body_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_body_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_body_typography',
			array(
				'label'       => esc_html__( 'Base Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your content.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_body_font',
					'weight' => 'ogf_body_font_weight',
					'style'  => 'ogf_body_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_headings_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_headings_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_headings_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_heading_typography',
			array(
				'label'       => esc_html__( 'Headings Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your headings.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_headings_font',
					'weight' => 'ogf_headings_font_weight',
					'style'  => 'ogf_headings_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_inputs_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_inputs_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_inputs_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_input_typography',
			array(
				'label'       => esc_html__( 'Buttons and Inputs Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your input fields and buttons.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_inputs_font',
					'weight' => 'ogf_inputs_font_weight',
					'style'  => 'ogf_inputs_font_style',
				),
			)
		)
	);

	/* Advanced Settings */

	$wp_customize->add_section( 'olympus-google-fonts-advanced', array(
		'title'       => esc_html__( 'Advanced Settings', 'olympus-google-fonts' ),
		'priority'    => 1,
		'panel'       => 'olympus_google_fonts',
		'description' => esc_html__( 'Advanced settings allow fine-grain control and overwrite the basic settings.', 'olympus-google-fonts' ),
	) );

	$wp_customize->add_setting( 'ogf_site_title_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_site_title_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_site_title_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_site_title_typography',
			array(
				'label'       => esc_html__( 'Site Title Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your site title.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_site_title_font',
					'weight' => 'ogf_site_title_font_weight',
					'style'  => 'ogf_site_title_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_site_description_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_site_description_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_site_description_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_site_description_typography',
			array(
				'label'       => esc_html__( 'Site Description Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your site description.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_site_description_font',
					'weight' => 'ogf_site_description_font_weight',
					'style'  => 'ogf_site_description_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_navigation_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_navigation_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_navigation_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_navigation_typography',
			array(
				'label'       => esc_html__( 'Navigation Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your site navigation.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_navigation_font',
					'weight' => 'ogf_navigation_font_weight',
					'style'  => 'ogf_navigation_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_post_page_headings_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_post_page_headings_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_post_page_headings_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_post_page_headings_typography',
			array(
				'label'       => esc_html__( 'Post/Page Headings Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your post and page headings.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_post_page_headings_font',
					'weight' => 'ogf_post_page_headings_font_weight',
					'style'  => 'ogf_post_page_headings_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_post_page_content_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_post_page_content_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_post_page_content_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_post_page_content_typography',
			array(
				'label'       => esc_html__( 'Post/Page Content Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your post and page content.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_post_page_content_font',
					'weight' => 'ogf_post_page_content_font_weight',
					'style'  => 'ogf_post_page_content_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_sidebar_headings_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_sidebar_headings_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_sidebar_headings_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_sidebar_headings_typography',
			array(
				'label'       => esc_html__( 'Sidebar Headings Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your sidebar headings.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_sidebar_headings_font',
					'weight' => 'ogf_sidebar_headings_font_weight',
					'style'  => 'ogf_sidebar_headings_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_sidebar_content_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_sidebar_content_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_sidebar_content_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_sidebar_content_typography',
			array(
				'label'       => esc_html__( 'Sidebar Content Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your sidebar content.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_sidebar_content_font',
					'weight' => 'ogf_sidebar_content_font_weight',
					'style'  => 'ogf_sidebar_content_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_footer_headings_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_footer_headings_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_footer_headings_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_footer_headings_typography',
			array(
				'label'       => esc_html__( 'Footer Headings Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your footer headings.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_footer_headings_font',
					'weight' => 'ogf_footer_headings_font_weight',
					'style'  => 'ogf_footer_headings_font_style',
				),
			)
		)
	);

	$wp_customize->add_setting( 'ogf_footer_content_font', array(
		'default'   => 'default',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_footer_content_font_weight', array(
		'default'   => '0',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_setting( 'ogf_footer_content_font_style', array(
		'default'   => 'normal',
		'transport' => 'postMessage',
	) );

	$wp_customize->add_control(
		new WP_Customize_Typography_Control(
			$wp_customize,
			'ogf_footer_content_typography',
			array(
				'label'       => esc_html__( 'Footer Content Typography', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Select and configure the font for your footer content.', 'olympus-google-fonts' ),
				'section'     => 'olympus-google-fonts-advanced',
				// Tie a setting (defined via `$wp_customize->add_setting()`) to the control.
				'settings'    => array(
					'family' => 'ogf_footer_content_font',
					'weight' => 'ogf_footer_content_font_weight',
					'style'  => 'ogf_footer_content_font_style',
				),
			)
		)
	);

}
add_action( 'customize_register', 'ogf_customize_register' );

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

/**
 * Sanitize the checbox value.
 *
 * @param int $input the input to sanitize.
 * @return int 1 if checked, 0 if not.
 */
function ogf_sanitize_checkbox( $input ) {
	if ( true === $input || '1' === $input ) {
		return '1';
	}
	return '0';
}
