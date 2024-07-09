<?php
/**
 * Compatibility file for MemberPress Courses.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Modify the default allowed handles to improve compatibility with Fonts Plugin.
 *
 * @param array $allowed_handles The default elements.
 */
function ogf_mb_courses_filter( $allowed_handles ) {
	$typekit_data = get_option( 'fp-typekit-data', array() );

	if ( is_array( $typekit_data ) ) {
		foreach ( $typekit_data as $id => $values ) {

			// skip if the kit is disabled.
			if ( $values['enabled'] === false ) {
				continue;
			}
			$allowed_handles[] = 'typekit-' . $id;
		}
	}

	$allowed_handles[] = 'olympus-google-fonts';

	return $allowed_handles;
}
add_filter( 'mpcs_classroom_style_handles', 'ogf_mb_courses_filter' );

/**
 * Modify the default element selectors to improve compatibility with MemberPress Courses.
 *
 * @param array $elements The default elements.
 */
function ogf_memberpress_courses_controls( $elements ) {
	$new = array(
		'ogc_mbc_base' => array(
			'label'       => esc_html__( 'Base Font', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_memberpress_courses',
			'selectors'   => '.mpcs-classroom, .mpcs-classroom .entry-content',
		),
		'ogc_mbc_headings' => array(
			'label'       => esc_html__( 'Heading Font', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_memberpress_courses',
			'selectors'   => '.mpcs-classroom h1, .mpcs-classroom h2, .mpcs-classroom h3, .mpcs-classroom h4, .mpcs-classroom h5, .mpcs-classroom h6',
		),
		'ogc_mbc_sidebar' => array(
			'label'       => esc_html__( 'Sidebar Font', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_memberpress_courses',
			'selectors'   => '.mpcs-sidebar-content',
		),
		'ogc_mbc_section_title' => array(
			'label'       => esc_html__( 'Section Title Font', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_memberpress_courses',
			'selectors'   => '.mpcs-section-title-text',
		),
		'ogc_mbc_lesson_nav' => array(
			'label'       => esc_html__( 'Navigation Font', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_memberpress_courses',
			'selectors'   => '#mpcs-lesson-navigation, #mpcs-lesson-navigation button',
		),
		'ogc_mbc_buttons' => array(
			'label'       => esc_html__( 'Button Font', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_memberpress_courses',
			'selectors'   => '.mpcs-classroom button',
		),
	);

	return array_merge( $elements, $new );
}
add_filter( 'ogf_elements', 'ogf_memberpress_courses_controls' );
