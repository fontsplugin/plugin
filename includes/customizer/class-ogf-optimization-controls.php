<?php
/**
 * Build the customizer controls for Optimization options.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2019, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * OGF_Optimization_Controls Class.
 */
class OGF_Optimization_Controls {

	/**
	 * The constructor.
	 */
	public function __construct() {

		if ( DEFINED( 'OGF_PRO' ) ) {
			return;
		}

		add_action( 'customize_register', array( $this, 'register_settings' ) );
		add_action( 'customize_register', array( $this, 'register_section' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue' ), 100 );
	}

	/**
	 * Register control scripts/styles.
	 */
	public function customize_controls_enqueue() {
		wp_enqueue_script( 'ogf-customize-controls', esc_url( OGF_DIR_URL . 'assets/js/customize-controls.js' ), array( 'customize-controls' ), OGF_VERSION, true );
	}

	/**
	 * Register the Customizer section.
	 *
	 * @param WP_Customize_Manager $wp_customize the Customizer object.
	 */
	public function register_section( $wp_customize ) {
		$wp_customize->add_section(
			'ogf_optimization',
			array(
				'title'       => __( 'Optimization', 'olympus-google-fonts' ),
				'description' => __( '<p>Optimize the delivery of font files for improved performance and user-privacy.</p><p>Upgrade to <a href="https://fontsplugin.com/pro-upgrade">Fonts Plugin Pro</a> to unlock these features.</p>', 'olympus-google-fonts' ),
				'panel'       => 'ogf_google_fonts',
			)
		);
	}

	/**
	 * Register the Customizer setting.
	 *
	 * @param WP_Customize_Manager $wp_customize the Customizer object.
	 */
	public function register_settings( $wp_customize ) {

		$site_url = site_url( '', 'https' );
		$url      = preg_replace( '(^https?://)', '', $site_url );

		// Add an option to disable the logo.
		$wp_customize->add_setting(
			'ogf_host_locally',
			array(
				'default'           => false,
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'ogf_host_locally',
			array(
				'label'       => esc_html__( 'Host Google Fonts Locally', 'olympus-google-fonts' ),
				'description' => sprintf (esc_html__( 'Fonts will be served from %s instead of fonts.googleapis.com.', 'olympus-google-fonts' ),$url),
				'section'     => 'ogf_optimization',
				'type'        => 'checkbox',
				'settings'    => 'ogf_host_locally',
			)
		);

		$wp_customize->add_setting(
			'ogf_use_woff2',
			array(
				'default'           => false,
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_setting(
			'ogf_preloading',
			array(
				'default'           => false,
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'ogf_preloading',
			array(
				'label'       => esc_html__( 'Enable Preloading', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Add preload resource hints.', 'olympus-google-fonts' ),
				'section'     => 'ogf_optimization',
				'type'        => 'checkbox',
				'settings'    => 'ogf_preloading',
			)
		);

		$wp_customize->add_setting(
			'ogf_removal',
			array(
				'default'           => false,
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'ogf_removal',
			array(
				'label'       => esc_html__( 'Remove External Fonts', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Remove Google Fonts loaded by other plugins and your theme.', 'olympus-google-fonts' ),
				'section'     => 'ogf_optimization',
				'type'        => 'checkbox',
				'settings'    => 'ogf_removal',
			)
		);

		$wp_customize->add_setting(
			'ogf_rewrite',
			array(
				'default'           => false,
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'ogf_rewrite',
			array(
				'label'           => esc_html__( 'Rewrite External Fonts', 'olympus-google-fonts' ),
				'description'     => esc_html__( 'Convert fonts added by your theme and plugins to be locally hosted on your domain.', 'olympus-google-fonts' ),
				'section'         => 'ogf_optimization',
				'type'            => 'checkbox',
				'settings'        => 'ogf_rewrite',
			)
		);
	}
}

$ogf_optimization_controls = new OGF_Optimization_Controls();
