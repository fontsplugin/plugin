<?php
/**
 * Reset fonts class.
 *
 * @package   fonts-plugin-pro
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! class_exists( 'OGF_Clear_Cache' ) ) :
	/**
	 * The 'Reset Fonts' class.
	 */
	class OGF_Clear_Cache {

		/**
		 * WP_Customize object.
		 *
		 * @var WP_Customize_Manager
		 */
		private $wp_customize;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'customize_register', array( $this, 'customize_register' ) );
			add_action( 'wp_ajax_customizer_clear_cache', array( $this, 'ajax_customizer_clear_cache' ) );
			add_action( 'customize_register', array( $this, 'customize_register' ) );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_scripts' ), 101 );
		}

		/**
		 * Add localize script to assets/js/customize-controls.js.
		 */
		public function customize_scripts() {
			wp_localize_script(
				'fpp-customize-controls',
				'clearCache',
				array(
					'confirm' => esc_html__( 'This will clear the local font cache.', 'fonts-plugin-pro' ),
					'nonce'   => wp_create_nonce( 'fpp_clear_cache' ),
				)
			);
		}

		/**
		 * Store a reference to `WP_Customize_Manager` instance
		 *
		 * @param Object $wp_customize Global $wp_customize object.
		 */
		public function customize_register( $wp_customize ) {
			$this->wp_customize = $wp_customize;

			$wp_customize->add_control(
				'fpp_reset_fonts',
				array(
					'type'        => 'button',
					'label'   => __( 'Clear Font Cache', 'fonts-plugin-pro' ),
					'description'   => __( 'This will clear the local font cache.', 'fonts-plugin-pro' ),
					'settings'    => array(),
					'priority'    => 100,
					'section'     => 'ogf_debugging',
					'input_attrs' => array(
						'value' => __( 'Clear Cache', 'fonts-plugin-pro' ),
						'class' => 'button button-link-delete',
					),
				)
			);
		}

		/**
		 * The reset AJAX request handler.
		 */
		public function ajax_customizer_clear_cache() {
			if ( ! $this->wp_customize->is_preview() ) {
				wp_send_json_error( 'not_preview' );
			}

			if ( ! check_ajax_referer( 'fpp_clear_cache', 'security' ) ) {
				wp_send_json_error( 'invalid_nonce' );
			}

			$this->clear();

			wp_send_json_success();
		}

		/**
		 * Perform the reset.
		 */
		public function clear() {
			$loader = new FPP_Host_Google_Fonts_Locally();
			$loader->delete_fonts_folder();
		}
	}
endif;

/*
 * Instantiate the OGF_Clear_Cache class.
 */
new OGF_Clear_Cache();
