<?php
/**
 * Reset fonts class.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! class_exists( 'OGF_Reset' ) ) :
	/**
	 * The 'Reset Fonts' class.
	 */
	class OGF_Reset {

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
			add_action( 'wp_ajax_customizer_reset', array( $this, 'ajax_customizer_reset' ) );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_scripts' ), 101 );
		}

		/**
		 * Add localize script to assets/js/customize-controls.js.
		 */
		public function customize_scripts() {
			wp_localize_script(
				'ogf-customize-controls',
				'fontsReset',
				array(
					'confirm' => esc_html__( 'This will reset all fonts set by this plugin to their defaults. This action can not be reversed.', 'olympus-google-fonts' ),
					'nonce'   => wp_create_nonce( 'ogf_reset' ),
				)
			);
		}

		/**
		 * Store a reference to `WP_Customize_Manager` instance
		 *
		 * @param Object $wp_customize Global $wp_customize object.
		 */
		public function customize_register( $wp_customize ) {
			$wp_customize->add_control(
				'ogf_reset_fonts',
				array(
					'type'        => 'button',
					'label'       => __( 'Reset All Fonts', 'olympus-google-fonts' ),
					'description' => __( 'This will reset all fonts set by this plugin to their defaults. This action can not be reversed.', 'olympus-google-fonts' ),
					'settings'    => array(),
					'priority'    => 100,
					'section'     => 'ogf_debugging',
					'input_attrs' => array(
						'value' => __( 'Reset All Fonts', 'olympus-google-fonts' ),
						'class' => 'button button-link-delete',
					),
				)
			);
		}

		/**
		 * The reset AJAX request handler.
		 */
		public function ajax_customizer_reset() {
			if ( ! check_ajax_referer( 'ogf_reset', 'security' ) ) {
				wp_send_json_error( 'invalid_nonce' );
			}

			$this->reset_customizer();

			wp_send_json_success();
		}

		/**
		 * Perform the reset.
		 */
		public function reset_customizer() {
			$settings = ogf_get_elements();
			foreach ( $settings as $key => $value ) {
				set_theme_mod( $key . '_font', null );
				set_theme_mod( $key . '_font_weight', null );
				set_theme_mod( $key . '_font_style', null );
				set_theme_mod( $key . '_font_size', null );
				set_theme_mod( $key . '_font_color', null );
				set_theme_mod( $key . '_line_height', null );
			}
		}
	}
endif;

/*
 * Instantiate the OGF_Reset class.
 */
new OGF_Reset();
