<?php
/**
 * Fonts Plugin | Use Google Fonts, Adobe Fonts or Upload Fonts
 *
 * Plugin Name: Fonts Plugin | Use Google Fonts, Adobe Fonts or Upload Fonts
 * Plugin URI:  https://wordpress.org/plugins/olympus-google-fonts/
 * Description: The easiest to customize fonts in WordPress. Optimized for Speed. 1000+ font choices. Supports Google Fonts, Adobe Fonts and Upload Fonts.
 * Version:     4.2.1
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

/**
 * Check whether a Pro installation has an active license.
 *
 * The fallback check is required for older Pro builds that do not expose the
 * current license helper themselves.
 *
 * @return bool Whether Pro is licensed.
 */
function ogf_is_pro_active() {
	if ( ! defined( 'OGF_PRO' ) ) {
		return false;
	}

	if ( function_exists( 'fpp_is_license_valid' ) ) {
		return fpp_is_license_valid();
	}

	return '' !== trim( (string) get_option( 'fpp_license_key', '' ) ) && 'valid' === get_option( 'fpp_license_status', 'invalid' );
}

/**
 * Prevent legacy Pro builds from bootstrapping without a license.
 */
function ogf_block_unlicensed_pro() {
	if ( defined( 'OGF_PRO' ) && ! ogf_is_pro_active() ) {
		remove_action( 'init', 'fonts_plugin_pro_init', 20 );
		remove_action( 'init', 'fpp_plugin_updater' );
	}
}
add_action( 'plugins_loaded', 'ogf_block_unlicensed_pro', PHP_INT_MAX );

/**
 * Initiate the plugin, unless the Pro version is active.
 */
function ogf_initiate() {
	require_once 'class-olympus-google-fonts.php';
}
add_action( 'init', 'ogf_initiate' );

/**
 * Add a redirection check on activation.
 *
 * @return void
 */
function ogf_activate() {
	add_option( 'ogf_do_activation_redirect', true );
}
register_activation_hook( __FILE__, 'ogf_activate' );

/**
 * Redirect to the Google Fonts Welcome page.
 */
function ogf_redirect() {
	if ( get_option( 'ogf_do_activation_redirect', false ) ) {
		delete_option( 'ogf_do_activation_redirect' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Activation redirect, no form data processed.
		if ( ! isset( $_GET['activate-multi'] ) && ! is_network_admin() ) {
			wp_safe_redirect( 'admin.php?page=fonts-plugin' );
			exit;
		}
	}
}
add_action( 'admin_init', 'ogf_redirect' );
