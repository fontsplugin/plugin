<?php
/**
 * Blocks Package
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( false !== get_theme_mod( 'ogf_disable_post_level_controls', false ) ) {
	return;
}

/**
 * Enqueue Gutenberg block assets for backend editor.
 */
function olympus_google_fonts_block_js() {
	wp_enqueue_script(
		'olympus-google-fonts-block-js',
		plugins_url( '/dist/blocks.build.js', __FILE__ ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
		OGF_VERSION,
		false
	);
	wp_localize_script( 'olympus-google-fonts-block-js', 'ogf_custom_fonts', ogf_custom_fonts() );
	wp_localize_script( 'olympus-google-fonts-block-js', 'ogf_typekit_fonts', ogf_typekit_fonts() );
}

add_action( 'enqueue_block_editor_assets', 'olympus_google_fonts_block_js' );

/**
 * Registers the 'olympus-google-fonts/google-fonts' block on server.
 */
function olympus_google_fonts_register_block() {
	// Check if the register function exists.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	register_block_type(
		'olympus-google-fonts/google-fonts',
		array(
			'attributes'      => array(
				'blockType'  => array(
					'type'    => 'string',
					'default' => 'p',
				),
				'fontID'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'variant'    => array(
					'type'    => 'string',
					'default' => 'normal',
				),
				'fontSize'   => array(
					'type' => 'number',
				),
				'lineHeight' => array(
					'type' => 'number',
				),
				'align'      => array(
					'type' => 'string',
				),
				'content'    => array(
					'type' => 'string',
				),
				'color'      => array(
					'type' => 'string',
				),
			),
			'render_callback' => 'olympus_google_fonts_block_render',
		)
	);
}
add_action( 'init', 'olympus_google_fonts_register_block' );

/**
 * Front end render function for 'olympus-google-fonts/google-fonts'.
 *
 * @param array $attributes The block attributes.
 */
function olympus_google_fonts_block_render( $attributes ) {
	$block_type  = isset( $attributes['blockType'] ) ? esc_attr( $attributes['blockType'] ) : 'p';
	$font_id     = isset( $attributes['fontID'] ) ? sanitize_text_field( $attributes['fontID'] ) : '';
	$variant     = isset( $attributes['variant'] ) ? sanitize_text_field( $attributes['variant'] ) : '';
	$font_size   = isset( $attributes['fontSize'] ) ? intval( $attributes['fontSize'] ) : '';
	$line_height = isset( $attributes['lineHeight'] ) ? floatval( $attributes['lineHeight'] ) : '';
	$align       = isset( $attributes['align'] ) ? sanitize_text_field( $attributes['align'] ) : '';
	$content     = isset( $attributes['content'] ) ? wp_kses_post( $attributes['content'] ) : '';
	$color       = isset( $attributes['color'] ) ? sanitize_text_field( $attributes['color'] ) : '';
	$class_name  = isset( $attributes['className'] ) ? sanitize_text_field( $attributes['className'] ) : '';
	$output      = '';
	$style       = '';
	$forced      = ogf_is_forced();

	if ( $font_id ) {

		// standardize the format.
		$font_id_standardized = str_replace( '+', '-', strtolower( $font_id ) );

		if ( array_key_exists( $font_id_standardized, OGF_Fonts::$google_fonts ) ) {
			$variants = OGF_Fonts::$google_fonts[ $font_id_standardized ]['v'];

			$variants_for_url = join( ',', array_keys( $variants ) );

			wp_enqueue_style( 'google-font-' . $font_id_standardized, 'https://fonts.googleapis.com/css?family=' . $font_id . ':' . $variants_for_url . '&display=swap', array(), OGF_VERSION );

			$font_family = esc_attr( str_replace( '+', ' ', $font_id ) );
		} else {
			$font_family = $font_id;
		}

		$style = "font-family: {$font_family}{$forced};";
	}

	if ( $variant && '0' !== $variant ) {
		$style .= "font-weight: {$variant}{$forced};";
	}

	if ( $font_size ) {
		$style .= "font-size: {$font_size}px{$forced};";
	}

	if ( $line_height ) {
		$style .= "line-height: {$line_height}{$forced};";
	}

	if ( $align ) {
		$style .= "text-align: {$align}{$forced};";
	}

	if ( $color ) {
		$style .= "color: {$color}{$forced};";
	}

	$output .= '<' . $block_type . ' class="fonts-plugin-block ' . $class_name . '" style="' . esc_attr( $style ) . '">';
	$output .= $content;
	$output .= '</' . $block_type . '>';

	return wp_kses_post( $output );
}
