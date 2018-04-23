<?php
/**
 * Main Olympus_Google_Fonts Class
 *
 * @package     olympus-google-fonts
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue' ) );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_enqueue' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( OGF_DIR_PATH . 'olympus-google-fonts.php' ), array( $this, 'settings_link' ) );

	}

	/**
	 * Load plugin files.
	 */
	public function includes() {

		// Required files for building the Google Fonts URL.
		require OGF_DIR_PATH . 'includes/functions.php';
		require OGF_DIR_PATH . 'includes/class-ogf-google-url.php';

		// Required files for the customizer settings.
		require OGF_DIR_PATH . 'includes/customizer/settings.php';
		require OGF_DIR_PATH . 'includes/customizer/output-css.php';

		// Feedback request class.
		require OGF_DIR_PATH . 'includes/class-ogf-feedback.php';

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
			wp_enqueue_style( 'olympus-google-fonts', $url->build() );
		}

	}

	/**
	 * Register control scripts/styles.
	 */
	public function customize_controls_enqueue() {

		wp_enqueue_script( 'ogf-customize-controls', esc_url( OGF_DIR_URL . 'assets/js/customize-controls.js' ), array( 'customize-controls' ) );
		wp_enqueue_style( 'ogf-customize-controls', esc_url( OGF_DIR_URL . 'assets/css/customize-controls.css' ) );

	}

	/**
	 * Load preview scripts/styles.
	 */
	public function customize_preview_enqueue() {
		wp_enqueue_script( 'ogf-customize-preview', esc_url( OGF_DIR_URL . 'assets/js/customize-preview.js' ), array( 'jquery' ) );
	}

	/**
	 * Load preview scripts/styles.
	 *
	 * @param array $links Current links array.
	 */
	public function settings_link( $links ) {

		$customizer_url = admin_url( 'customize.php?autofocus[panel]=olympus_google_fonts' );

		$settings_link = '<a href="' . esc_url( $customizer_url ) . '">' . esc_html__( 'Settings', 'olympus-google-fonts' ) . '</a>';

		array_push( $links, $settings_link );

		return $links;

	}

}
