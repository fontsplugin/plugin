<?php
/**
 * Compatibility file for ThemeIsle themes.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Modify the default element selectors to improve compatibility with ThemeIsle themes.
 *
 * @param array $elements The default elements.
 */
function ogf_themeisle_elements( $elements ) {
	$elements['ogf_post_page_h1']['selectors'] = '.entry-content h1, .post-content h1, .page-content h1, #content h1, .single-post-wrap h1, .page-content-wrap h1';
	$elements['ogf_post_page_h2']['selectors'] = '.entry-content h2, .post-content h2, .page-content h2, #content h2, .single-post-wrap h2, .page-content-wrap h2';
	$elements['ogf_post_page_h3']['selectors'] = '.entry-content h3, .post-content h3, .page-content h3, #content h3, .single-post-wrap h3, .page-content-wrap h3';
	$elements['ogf_post_page_h4']['selectors'] = '.entry-content h4, .post-content h4, .page-content h4, #content h4, .single-post-wrap h4, .page-content-wrap h4';
	$elements['ogf_post_page_h5']['selectors'] = '.entry-content h5, .post-content h5, .page-content h5, #content h5, .single-post-wrap h5, .page-content-wrap h5';
	$elements['ogf_post_page_h6']['selectors'] = '.entry-content h6, .post-content h6, .page-content h6, #content h6, .single-post-wrap h6, .page-content-wrap h6';

	return $elements;
}
add_filter( 'ogf_elements', 'ogf_themeisle_elements' );
