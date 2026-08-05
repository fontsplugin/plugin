<?php
/**
 * Bootstrap for the Classic Editor integration.
 *
 * Kept out of class-ogf-classic-editor.php so that file holds only the class,
 * as its name implies.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Whether the current admin screen should load Classic / TinyMCE integration.
 *
 * @param WP_Screen $screen Current screen.
 * @return bool
 */
function ogf_screen_needs_classic_editor_integration( $screen ) {
	if ( ! $screen instanceof WP_Screen ) {
		return false;
	}

	/**
	 * Short-circuit Classic Editor integration for a screen.
	 *
	 * @param bool|null $load   Return true to force load, false to skip, null to use default logic.
	 * @param WP_Screen $screen Current screen.
	 */
	$forced = apply_filters( 'ogf_load_classic_editor_integration', null, $screen );
	if ( true === $forced ) {
		return true;
	}
	if ( false === $forced ) {
		return false;
	}

	// Post / page / CPT screens: only when the block editor is not used for this post type.
	if ( 'post' === $screen->base ) {
		$post_type = $screen->post_type;
		if ( empty( $post_type ) ) {
			return true;
		}
		if ( function_exists( 'use_block_editor_for_post_type' ) ) {
			return ! use_block_editor_for_post_type( $post_type );
		}
		return true;
	}

	// Legacy widgets screen (TinyMCE in text widgets).
	if ( 'widgets' === $screen->id ) {
		return true;
	}

	return false;
}

/**
 * Load Classic Editor integration only when needed (skip front-end and most admin screens).
 *
 * @return void
 */
function ogf_init_classic_editor_integration() {
	if ( true === get_theme_mod( 'ogf_disable_post_level_controls', false ) ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	static $registered = false;
	if ( $registered ) {
		return;
	}
	$registered = true;

	// admin-ajax.php does not run set_current_screen(); keep previous behavior for AJAX.
	if ( wp_doing_ajax() ) {
		new OGF_Classic_Editor();
		return;
	}

	add_action(
		'current_screen',
		static function ( $screen ) {
			static $booted = false;
			if ( $booted ) {
				return;
			}
			if ( ! ogf_screen_needs_classic_editor_integration( $screen ) ) {
				return;
			}
			$booted = true;
			new OGF_Classic_Editor();
		},
		0
	);
}
add_action( 'admin_init', 'ogf_init_classic_editor_integration', 0 );
