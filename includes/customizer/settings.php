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

	$wp_customize->add_section( 'olympus-google-fonts' , array(
		'title'      => esc_html__( 'Google Fonts', 'olympus-google-fonts' ),
		'priority'   => 1,
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
