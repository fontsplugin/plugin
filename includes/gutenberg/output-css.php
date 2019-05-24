<?php
/**
 * Output the Google Fonts CSS in Gutenberg.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2019, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


/**
 * Enqeue the Google Fonts URL.
 */
function ogf_gutenberg_enqueue_fonts() {

  $fonts = new OGF_Fonts();

   if ( $fonts->has_custom_fonts() ) {
     $url = $fonts->build_url();
     wp_enqueue_style( 'olympus-google-fonts', $url, array(), OGF_VERSION );
   }

 }

add_action( 'enqueue_block_editor_assets', 'ogf_gutenberg_enqueue_fonts' );


/**
 * Output the font CSS to wp_head.
 */
function ogf_gutenberg_output_css() {

	// Only load on Gutenberg-enabled pages.
	global $current_screen;
	$current_screen = get_current_screen();
	if ( ! method_exists( $current_screen, 'is_block_editor' ) || ! $current_screen->is_block_editor() ) {
			return;
	}

	?>
	<!-- Fonts Plugin Gutenberg CSS - https://fontsplugin.com/ -->
	<style>
		<?php

		do_action( 'ogf_gutenberg_inline_styles' );

		$elements = array(
			'ogf_body' => array(
				'selectors' => '.editor-writing-flow, .editor-styles-wrapper p, .editor-styles-wrapper h3, #editor .editor-styles-wrapper .editor-post-title__block .editor-post-title__input',
			),
			'ogf_headings' => array(
				'selectors' => '#editor .editor-styles-wrapper .editor-post-title__block .editor-post-title__input, .editor-styles-wrapper h1, .editor-styles-wrapper h2, .editor-styles-wrapper h3, .editor-styles-wrapper h4, .editor-styles-wrapper h5, .editor-styles-wrapper h6',
			),
			'ogf_inputs' => array(
				'selectors' => 'button, input, select, textarea',
			),
			'ogf_post_page_content' => array(
				'selectors' => '.editor-styles-wrapper p',
			),
			'ogf_post_page_h1' => array(
				'selectors' => '#editor .editor-styles-wrapper .editor-post-title__block .editor-post-title__input, .editor-styles-wrapper h1',
			),
			'ogf_post_page_h2' => array(
				'selectors' => '.editor-styles-wrapper h2',
			),
			'ogf_post_page_h3' => array(
				'selectors' => '.editor-styles-wrapper h3',
			),
			'ogf_post_page_h4' => array(
				'selectors' => '.editor-styles-wrapper h4',
			),
			'ogf_post_page_h5' => array(
				'selectors' => '.editor-styles-wrapper h5',
			),
			'ogf_post_page_h6' => array(
				'selectors' => '.editor-styles-wrapper h6',
			),
		);

		$elements = apply_filters( 'ogf_gutenberg_elements', $elements );

		foreach ( $elements as $id => $values ) {
			ogf_generate_css( $values['selectors'], $id );
		}
		?>
	</style>
	<!-- Fonts Plugin Gutenberg CSS -->
	<?php
}

// Output custom CSS to live site.
add_action( 'admin_head', 'ogf_gutenberg_output_css' );
