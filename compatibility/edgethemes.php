<?php
/**
 * Compatibility file for Edge Themes.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Modify the default element selectors to improve compatibility with Edge Themes.
 *
 * @param array $elements The default elements.
 */
function ogf_edgethemes_elements( $elements ) {
	$elements['ogf_body']['selectors'] = '#qodef-page-wrapper, body, #content, .entry-content, .post-content, .page-content, .post-excerpt, .entry-summary, .entry-excerpt, .widget-area, .widget, .sidebar, #sidebar, footer, .footer, #footer, .site-footer';
	$elements['ogf_post_page_content']['selectors'] = '#qodef-page-content, #qodef-page-content p, .entry-content, .entry-content p, .post-content, .page-content, .post-excerpt, .entry-summary, .entry-excerpt, .excerpt, .excerpt p, .type-post p, .type-page p';
	$elements['ogf_post_page_h1']['selectors'] = '#qodef-page-content h1, .entry-title, .entry-title a, .post-title, .post-title a, .page-title, .entry-content h1, #content h1, .type-post h1, .type-page h1';
	$elements['ogf_post_page_h2']['selectors'] = '#qodef-page-content h2, .entry-content h2, .post-content h2, .page-content h2, #content h2, .type-post h2, .type-page h2';
	$elements['ogf_post_page_h3']['selectors'] = '#qodef-page-content h3, .entry-content h3, .post-content h3, .page-content h3, #content h3, .type-post h3, .type-page h3';
	$elements['ogf_post_page_h4']['selectors'] = '#qodef-page-content h4, .entry-content h4, .post-content h4, .page-content h4, #content h4, .type-post h4, .type-page h4';
	$elements['ogf_post_page_h5']['selectors'] = '#qodef-page-content h5, .entry-content h5, .post-content h5, .page-content h5, #content h5, .type-post h5, .type-page h5';
	$elements['ogf_post_page_h6']['selectors'] = '#qodef-page-content h6, .entry-content h6, .post-content h6, .page-content h6, #content h6, .type-post h6, .type-page h6';

	return $elements;
}
add_filter( 'ogf_elements', 'ogf_edgethemes_elements' );
