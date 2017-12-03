<?php
/**
 * Google Fonts for WordPress
 *
 * Plugin Name: Google Fonts for WordPress
 * Plugin URI:  https://wordpress.org/plugins/olympus-google-fonts/
 * Description: The simplest Google Fonts plugin for WordPress. Add Google Fonts functionality to your WordPress website in minutes without any coding.
 * Version:     1.0.7
 * Author:      Danny Cooper
 * Author URI:  https://olympusthemes.com/
 * Text Domain: olympus-google-fonts
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package olympus-google-fonts
 */

/**
 * Main Olympus_Google_Fonts Class
 */
class Olympus_Google_Fonts {

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
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'olympus-google-fonts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Enqeue the Google Fonts URL.
	 */
	public function enqueue() {

		$url = new OGF_Google_URL();

		if ( $url->has_custom_fonts() ) {
			wp_enqueue_style( 'olympus-google-fonts',  $url->build() , false );
		}

	}

}

$ogf = new Olympus_Google_Fonts();