<?php
/**
 * Fonts Plugin | Google Fonts Typography
 *
 * Plugin Name: Fonts Plugin | Google Fonts Typography
 * Plugin URI:  https://wordpress.org/plugins/olympus-google-fonts/
 * Description: The easiest to use Google Fonts typography plugin. No coding required. 1000+ font choices.
 * Version:     2.5.5
 * Author:      Fonts Plugin
 * Author URI:  https://fontsplugin.com/?utm_source=wporg&utm_medium=readme&utm_campaign=description
 * Text Domain: olympus-google-fonts
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'OGF_VERSION' ) ) {
	define( 'OGF_VERSION', '2.5.5' );
}

if ( ! defined( 'OGF_DIR_PATH' ) ) {
	define( 'OGF_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'OGF_DIR_URL' ) ) {
	define( 'OGF_DIR_URL', plugin_dir_url( __FILE__ ) );
}

require_once OGF_DIR_PATH . 'class-olympus-google-fonts.php';
require_once OGF_DIR_PATH . 'admin/class-ogf-welcome-screen.php';


if ( false === get_theme_mod( 'ogf_disable_post_level_controls', false ) ) {
	require_once OGF_DIR_PATH . 'blocks/init.php';
}

$gfwp = new Olympus_Google_Fonts();

$current_theme      = wp_get_theme();
$theme_author       = strtolower( esc_attr( $current_theme->get( 'Author' ) ) );
$theme_author       = str_replace( ' ', '', $theme_author );
$author_compat_path = OGF_DIR_PATH . '/compatibility/' . $theme_author . '.php';
if ( file_exists( $author_compat_path ) ) {
	require_once $author_compat_path;
}

if ( ! function_exists( 'ogf_activate' ) ) {
	/**
	 * Add a redirection check on activation.
	 */
	function ogf_activate() {
		add_option( 'ogf_do_activation_redirect', true );
	}
	register_activation_hook( __FILE__, 'ogf_activate' );
}


if ( ! function_exists( 'ogf_redirect' ) ) {
	/**
	 * Redirect to the Google Fonts Welcome page.
	 */
	function ogf_redirect() {

		if ( get_option( 'ogf_do_activation_redirect', false ) ) {
			delete_option( 'ogf_do_activation_redirect' );
			if ( ! isset( $_GET['activate-multi'] ) ) {
				wp_redirect( 'admin.php?page=fonts-plugin' );
				exit;
			}
		}
	}
}
add_action( 'admin_init', 'ogf_redirect' );
