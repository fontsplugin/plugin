<?php
/**
 * Build the URL to load the chosen Google Fonts.
 *
 * @package     google-fonts-wp
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * @todo
 */
function mytheme_customize_register( $wp_customize ) {

	$wp_customize->add_section( 'google-fonts-wp' , array(
		'title'      => esc_html__( 'Google Fonts for WP', 'google-fonts-wp' ),
		'priority'   => 30,
	) );

	$wp_customize->add_setting( 'gfwp_header_font' , array(
		'default'   => 'right',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'header_font', array(
		'label'      => esc_html__( 'Header Font', 'mytheme' ),
		'section'    => 'google-fonts-wp',
		'settings'   => 'gfwp_header_font',
		'type'   => 'select',
		'choices'  => array(
			'default'  => 'Default',
			'left'  => 'left',
			'right' => 'right',
		),
	) );

	$wp_customize->add_setting( 'gfwp_body_font' , array(
		'default'   => 'default',
		'transport' => 'refresh',
	) );

	$wp_customize->add_control( 'body_font', array(
		'label'      => esc_html__( 'Body Font', 'mytheme' ),
		'section'    => 'google-fonts-wp',
		'settings'   => 'gfwp_body_font',
		'type'   => 'select',
		'choices'  => array(
			'default'  => 'Default',
			'Lato'  => 'Lato',
			'Open Sans' => 'Open Sans',
		),
	) );

}
add_action( 'customize_register', 'mytheme_customize_register' );


$object = wp_remote_get('https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyArY-5QrrBm_ZotJXZQKTOzZ8GgGTTTSn4');
$body = json_decode( wp_remote_retrieve_body($object), true);
// var_export($body['items']);
