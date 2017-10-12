<?php
/**
 * Google Fonts WordPress
 *
 * Plugin Name: Google Fonts for WordPress
 * Plugin URI:  https://wordpress.org/plugins/google-fonts-wp/
 * Description: Add Google Fonts functionality to your theme in minutes without any coding.
 * Version:     1.0.0
 * Author:      Danny Cooper
 * Author URI:  https://olympusthemes.com/
 * Text Domain: google-fonts-wp
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package google-fonts-wp
 */

/**
 * Main Google_Fonts_for_WP Class
 */
class Google_Fonts_WP {

	/**
	 * Initialize plugin.
	 */
	public function __construct() {

		$this->includes();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

	}

	/**
	 * Load plugin files.
	 */
	public function includes() {

		// Required files for building the Google Fonts URL.
		require plugin_dir_path( __FILE__ ) . 'includes/functions.php';
		require plugin_dir_path( __FILE__ ) . 'includes/class-google-url.php';

		// Required files for the customizer settings.
		require plugin_dir_path( __FILE__ ) . 'includes/customizer/settings.php';
		require plugin_dir_path( __FILE__ ) . 'includes/customizer/output-css.php';

	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'google-fonts-wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Enqeue the Google Fonts URL.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {

		$url = new GFWP_Google_URL();

		if ( $url->has_custom_fonts() ) {
			wp_enqueue_style( 'gfwp-google-fonts',  $url->build() , false );
		}

	}

}

$gfwp = new Google_Fonts_WP();
