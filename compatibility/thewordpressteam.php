<?php
/**
 * Compatibility file for WordPress.org themes.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Modify the default element selectors to improve compatibility with WordPress.org themes.
 *
 * @param array $elements The default elements.
 */
function ogf_wporg_elements( $elements ) {
	$elements['ogf_body']['selectors']            = 'body, #content, .entry-content, .post-content, .page-content, .post-excerpt, .entry-summary, .entry-excerpt, .widget-area, .widget, .sidebar, #sidebar, footer, .footer, #footer, .site-footer, #site-footer, .entry-content p, .entry-content ol, .entry-content ul, .entry-content dl, .entry-content dt, .widget_text p, .widget_text ol, .widget_text ul, .widget_text dl, .widget_text dt, .widget-content .rssSummary';
	$elements['ogf_sidebar_content']['selectors'] = '.widget-area, .widget, .sidebar, #sidebar, .widget_text p, .widget_text ol, .widget_text ul, .widget_text dl, .widget_text dt, .widget-content .rssSummary, .widget_text p, .widget_text ol, .widget_text ul, .widget_text dl, .widget_text dt, .widget-content .rssSummary';
	return $elements;
}
add_filter( 'ogf_elements', 'ogf_wporg_elements' );
