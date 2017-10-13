<?php
/**
 * Build the URL to load the chosen Google Fonts.
 *
 * @package     google-fonts-wp
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * An array containing the customizer sections, settings and controls.
 *
 * @param object Access to the $wp_customize object.
 */
function gfwp_customize_register( $wp_customize ) {

	$wp_customize->add_section( 'google-fonts-wp' , array(
		'title'      => esc_html__( 'Google Fonts', 'google-fonts-wp' ),
		'priority'   => 30,
	) );

	$wp_customize->add_setting( 'gfwp_body_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'body_font', array(
		'label'      => esc_html__( 'Body Font', 'google-fonts-wp' ),
		'section'    => 'google-fonts-wp',
		'settings'   => 'gfwp_body_font',
		'type'   => 'select',
		'choices'  => gfwp_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'gfwp_headings_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'headings_font', array(
		'label'      => esc_html__( 'Headings Font', 'google-fonts-wp' ),
		'section'    => 'google-fonts-wp',
		'settings'   => 'gfwp_headings_font',
		'type'   => 'select',
		'choices'  => gfwp_font_choices_for_select(),
	) );

	$wp_customize->add_setting( 'gfwp_inputs_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'inputs_font', array(
		'label'      => esc_html__( 'Buttons and Inputs Font', 'google-fonts-wp' ),
		'section'    => 'google-fonts-wp',
		'settings'   => 'gfwp_inputs_font',
		'type'   => 'select',
		'choices'  => gfwp_font_choices_for_select(),
	) );

}
add_action( 'customize_register', 'gfwp_customize_register' );

/**
 * Build the array for the select choices setting.
 */
function gfwp_font_choices_for_select() {
	$fonts_array = gfwp_fonts_array();

	$fonts = array(
		'default' => esc_html__( '- Default -', 'google-fonts-wp' ),
	);

	foreach ( $fonts_array as $key => $value ) {
		$fonts[ $key ] = $value['family'];
	}

	return $fonts;

}
