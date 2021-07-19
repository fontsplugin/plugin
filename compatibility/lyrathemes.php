<?php
/**
 * Compatibility file for LyraThemes themes.
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
function ogf_lyrathemes_elements( $elements ) {
	$elements['ogf_site_description']['selectors'] = '#site-description, .site-description, .tagline';
	$elements['ogf_body']['selectors'] = 'body, #content, .single-content, .entry-content, .post-content, .page-content, .post-excerpt, .entry-summary, .entry-excerpt, .widget-area, .widget, .sidebar, #sidebar, footer, .footer, #footer, .site-footer';
	$elements['ogf_headings']['selectors'] = 'b#site-title, .site-title, #site-title a, .site-title a, .entry-title, .entry-title a, .h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6, .widget-title';
	$elements['ogf_post_page_content']['selectors'] = '.single-content, .single-content p, .entry-content, .entry-content p, .post-content, .page-content, .post-excerpt, .entry-summary, .entry-excerpt, .excerpt, .excerpt p';
	$elements['ogf_post_page_h2']['selectors'] = '.entry-content h2, .post-content h2, .page-content h2, #content h2, .single-content h2';
	$elements['ogf_post_page_h3']['selectors'] = '.entry-content h3, .post-content h3, .page-content h3, #content h3, .single-content h3';
	$elements['ogf_post_page_h4']['selectors'] = '.entry-content h4, .post-content h4, .page-content h4, #content h4, .single-content h4';
	$elements['ogf_post_page_h5']['selectors'] = '.entry-content h5, .post-content h5, .page-content h5, #content h5, .single-content h5';
	$elements['ogf_post_page_h6']['selectors'] = '.entry-content h6, .post-content h6, .page-content h6, #content h6, .single-content h6';

	return $elements;
}
add_filter( 'ogf_elements', 'ogf_lyrathemes_elements' );
