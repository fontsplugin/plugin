<?php
/**
 * Helper functions for the Elementor plugin.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Create a new group for fonts added by Fonts Plugin.
 */
add_filter(
	'elementor/fonts/groups',
	function ( $groups ) {
		return array_merge( array( 'fontsplugin' => 'Fonts Plugin' ), $groups );
	},
	10,
	1
);

/**
 * Add fonts from Typekit and uploaded fonts to the Elementor dropdown.
 */
add_filter(
	'elementor/fonts/additional_fonts',
	function ( $fonts ) {
		$custom_fonts = ogf_custom_fonts_unique();

		foreach ( $custom_fonts as $key => $value ) {
			$fonts[ $key ] = 'fontsplugin';
		}

		$typekit_fonts = ogf_typekit_fonts();

		foreach ( $typekit_fonts as $key => $value ) {
			$fonts[ $value['id'] ] = 'fontsplugin';
		}

		return $fonts;
	},
	10,
	1
);

/**
 * Modify the default element selectors to improve compatibility with Elementor.
 *
 * @param array $elements The default elements.
 */
function ogf_elementor_controls( $elements ) {
	$new = array(
		'ogf_elementor_heading' => array(
			'label'       => esc_html__( 'Elementor Heading Typography', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_elementor',
			'selectors'   => '.elementor-page .elementor-heading-title',
		),
		'ogf_elementor_button'  => array(
			'label'       => esc_html__( 'Elementor Button Typography', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_elementor',
			'selectors'   => '.elementor-page .elementor-button-link',
		),
	);

	return array_merge( $elements, $new );
}
add_filter( 'ogf_elements', 'ogf_elementor_controls' );
