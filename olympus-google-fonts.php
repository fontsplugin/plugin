<?php
/**
 * Google Fonts Typography
 *
 * Plugin Name: Google Fonts Typography
 * Plugin URI:  https://wordpress.org/plugins/olympus-google-fonts/
 * Description: The easiest to use Google Fonts typography plugin. No coding required. 900+ font choices.
 * Version:     2.0.7
 * Author:      Fonts Plugin
 * Author URI:  https://fontsplugin.com/?utm_source=wporg&utm_campaign=heading
 * Text Domain: olympus-google-fonts
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

define( 'OGF_VERSION', '2.0.7' );
define( 'OGF_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'OGF_DIR_URL', plugin_dir_url( __FILE__ ) );

require OGF_DIR_PATH . 'class-olympus-google-fonts.php';
require OGF_DIR_PATH . 'blocks/init.php';
require OGF_DIR_PATH . 'admin/welcome.php';

$gfwp = new Olympus_Google_Fonts();

$current_theme      = wp_get_theme();
$theme_author       = strtolower( esc_attr( $current_theme->get( 'Author' ) ) );
$theme_author       = str_replace( ' ', '', $theme_author );
$author_compat_path = OGF_DIR_PATH . '/compatibility/' . $theme_author . '.php';
if ( file_exists( $author_compat_path ) ) {
	require $author_compat_path;
}

/**
 * Add a redirection check on activation.
 */
function ogf_activate() {
	add_option( 'ogf_do_activation_redirect', true );
}
register_activation_hook( __FILE__, 'ogf_activate' );


/**
 * Redirect to the Google Fonts Welcome page.
 */
function ogf_redirect() {

	// don't show the welcome message to users before v2.0.6.
	if ( get_site_option( 'ogf_activation_date' ) < 1593090943 ) {
		return;
	}

	if ( get_option( 'ogf_do_activation_redirect', false ) ) {
		delete_option( 'ogf_do_activation_redirect' );
		if ( ! isset( $_GET['activate-multi'] ) ) {
			wp_redirect( 'admin.php?page=olympus-google-fonts' );
			exit;
		}
	}
}
add_action( 'admin_init', 'ogf_redirect' );
