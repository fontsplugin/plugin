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

	$wp_customize->add_panel( 'olympus_google_fonts', array(
		'priority' => 10,
		'capability' => 'edit_theme_options',
		'theme_supports' => '',
		'title' => esc_html__( 'Google Fonts', 'olympus-google-fonts' ),
	) );

	$wp_customize->add_section( 'olympus-google-fonts' , array(
		'title'      => esc_html__( 'Basic Settings', 'olympus-google-fonts' ),
		'priority'   => 1,
		'panel'      => 'olympus_google_fonts',
	) );

	$wp_customize->add_setting( 'ogf_body_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'body_font', array(
		'label'      => esc_html__( 'Body Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts',
		'settings'   => 'ogf_body_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_headings_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'headings_font', array(
		'label'      => esc_html__( 'Headings Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts',
		'settings'   => 'ogf_headings_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_inputs_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'inputs_font', array(
		'label'      => esc_html__( 'Buttons and Inputs Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts',
		'settings'   => 'ogf_inputs_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_force_styles' , array(
		'default'   => '',
		'transport' => 'refresh',
		'sanitize_callback' => 'ogf_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'force_styles', array(
		'label'      => esc_html__( 'Force Styles?', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts',
		'settings'   => 'ogf_force_styles',
		'type'   => 'checkbox',
		'description' => esc_html__( 'If your choices are not displaying correctly, check this box.', 'olympus-google-fonts' ),
	) );

	/* Advanced Settings */

	$wp_customize->add_section( 'olympus-google-fonts-advanced' , array(
		'title'      => esc_html__( 'Advanced Settings', 'olympus-google-fonts' ),
		'priority'   => 1,
		'panel'      => 'olympus_google_fonts',
		'description'      => esc_html__( 'Advanced settings allow fine-grain control and overwrite the basic settings.', 'olympus-google-fonts' ),
	) );

	$wp_customize->add_setting( 'ogf_site_title_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'site_title_font', array(
		'label'      => esc_html__( 'Site Title Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_site_title_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_site_description_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'site_description_font', array(
		'label'      => esc_html__( 'Site Description Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_site_description_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_navigation_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'navigation_font', array(
		'label'      => esc_html__( 'Navigation Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_navigation_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_post_page_headings_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'post_page_headings_font', array(
		'label'      => esc_html__( 'Post/Page Headings Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_post_page_headings_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_post_page_content_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'post_page_content_font', array(
		'label'      => esc_html__( 'Post/Page Content Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_post_page_content_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_sidebar_headings_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'sidebar_headings_font', array(
		'label'      => esc_html__( 'Sidebar Headings Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_sidebar_headings_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_sidebar_content_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'sidebar_content_font', array(
		'label'      => esc_html__( 'Sidebar Content Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_sidebar_content_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_footer_headings_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'footer_headings_font', array(
		'label'      => esc_html__( 'Footer Headings Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_footer_headings_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_footer_content_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'footer_content_font', array(
		'label'      => esc_html__( 'Footer Content Font', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_footer_content_font',
		'type'   => 'select',
		'choices'  => ogf_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'ogf_force_advanced_styles' , array(
		'default'   => '',
		'transport' => 'refresh',
		'sanitize_callback' => 'ogf_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'force_advanced_styles', array(
		'label'      => esc_html__( 'Force Advanced Styles?', 'olympus-google-fonts' ),
		'section'    => 'olympus-google-fonts-advanced',
		'settings'   => 'ogf_force_advanced_styles',
		'type'   => 'checkbox',
		'description' => esc_html__( 'If your choices are not displaying correctly, check this box.', 'olympus-google-fonts' ),
	) );

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
