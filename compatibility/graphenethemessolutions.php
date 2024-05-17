<?php
/**
 * Compatibility file for Graphene theme.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Modify the default element selectors to improve compatibility with Graphen theme.
 *
 * @param array $elements The default elements.
 */
function ogf_silverks_elements( $elements ) {
	$elements['ogf_sidebar_headings']['selectors'] = '.widget-title, .widget-area h1, .widget-area h2, .widget-area h3, .widget-area h4, .widget-area h5, .widget-area h6, #secondary h1, #secondary h2, #secondary h3, #secondary h4, #secondary h5, #secondary h6, #sidebar h1, #sidebar h2, #sidebar h3, #sidebar h4, #sidebar h5, #sidebar h6, #sidebar1 h1, #sidebar1 h2, #sidebar1 h3, #sidebar1 h4, #sidebar1 h5, #sidebar1 h6, #sidebar2 h1, #sidebar2 h2, #sidebar2 h3, #sidebar2 h4, #sidebar2 h5, #sidebar2 h6';
	$elements['ogf_sidebar_content']['selectors']  = '.widget-area, .widget, .sidebar, #sidebar, #sidebar1, #sidebar2, #secondary';

	return $elements;
}
add_filter( 'ogf_elements', 'ogf_silverks_elements' );
