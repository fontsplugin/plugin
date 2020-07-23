<?php
/**
 * Compatibility file for Automattic themes.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Modify the default element selectors to improve compatibility with LyraThemes themes.
 *
 * @param array $elements The default elements.
 */
function ogf_automattic_elements( $elements ) {

	$elements['ogf_body']['selectors'] = 'body, .entry-content, .widget, .sidebar, #sidebar, footer, .footer, #footer, .site-footer, ul, ol';
	$elements['ogf_post_page_content']['selectors'] = '.entry-content, .entry-content p, .entry-content ul, .entry-content ol';
	return $elements;

}

add_filter( 'ogf_elements', 'ogf_automattic_elements' );
