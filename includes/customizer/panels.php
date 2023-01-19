<?php
/**
 * Add multi-level panel functionality.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Enqueue customizer JS.
 */
function ogf_panels_customize_controls_scripts() {
	wp_enqueue_script( 'ogf-panels', OGF_DIR_URL . 'assets/js/panels.js', array(), '1.0', true );
}
add_action( 'customize_controls_enqueue_scripts', 'ogf_panels_customize_controls_scripts' );

/**
 * Register the multi-level panels.
 *
 * @param object $wp_customize Access to the $wp_customize object.
 */
function ogf_panels_customize_register( $wp_customize ) {
	require_once OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-panel.php';
	$wp_customize->register_panel_type( 'OGF_Customize_Panel' );

	$ogf_panel = new OGF_Customize_Panel(
		$wp_customize,
		'ogf_google_fonts',
		array(
			'title'    => esc_html__( 'Fonts Plugin', 'olympus-google-fonts' ),
			'priority' => 1,
		)
	);
	$wp_customize->add_panel( $ogf_panel );

	$wp_customize->add_section(
		'ogf_theme',
		array(
			'title' => esc_html__( 'Theme Settings', 'olympus-google-fonts' ),
			'panel' => 'ogf_google_fonts',
		)
	);

	$wp_customize->add_section(
		'ogf_basic',
		array(
			'title' => __( 'Basic Settings', 'olympus-google-fonts' ),
			'panel' => 'ogf_google_fonts',
		)
	);

	$ogf_advanced_panel = new OGF_Customize_Panel(
		$wp_customize,
		'ogf_advanced',
		array(
			'title' => __( 'Advanced Settings', 'olympus-google-fonts' ),
			'panel' => 'ogf_google_fonts',
		)
	);

	$wp_customize->add_panel( $ogf_advanced_panel );

	$wp_customize->add_section(
		'ogf_custom',
		array(
			'title'       => esc_html__( 'Custom Elements', 'olympus-google-fonts' ),
			/* Translators: %s Custom Elements Customizer Panel URL */
			'description' => sprintf( __( 'Define your Custom Elements here and then customize them under <a href="%s">Advanced Settings &rarr; Custom Elements</a>.', 'olympus-google-fonts' ), esc_url( admin_url( '/customize.php?autofocus[section]=ogf_advanced__custom' ) ) ),
			'panel'       => 'ogf_google_fonts',
		)
	);

	$wp_customize->add_section(
		'ogf_font_loading',
		array(
			'title'       => esc_html__( 'Font Loading', 'olympus-google-fonts' ),
			'description' => __( 'Optimize your site\'s performance by unchecking any font weights you don\'t need.', 'olympus-google-fonts' ),
			'panel'       => 'ogf_google_fonts',
		)
	);

	$wp_customize->add_section(
		'ogf_font_subsets',
		array(
			'title'       => esc_html__( 'Font Subsets', 'olympus-google-fonts' ),
			'description' => 'Optimize your site\'s performance by removing font subsets that you don\'t need.',
			'panel'       => 'ogf_google_fonts',
		)
	);

	$wp_customize->add_section(
		'ogf_debugging',
		array(
			'title' => esc_html__( 'Debugging', 'olympus-google-fonts' ),
			'panel' => 'ogf_google_fonts',
		)
	);

	$wp_customize->add_section(
		'ogf_advanced__custom',
		array(
			'title'       => esc_html__( 'Custom Elements', 'olympus-google-fonts' ),
			/* Translators: %s Custom Elements Customizer Panel URL */
			'description' => sprintf( __( 'Custom Elements allow you to apply Google Fonts to any part of your website, they can be setup under <a href="%s">Google Fonts &rarr; Custom Elements</a>.', 'olympus-google-fonts' ), esc_url( admin_url( '/customize.php?autofocus[section]=ogf_custom' ) ) ),
			'panel'       => 'ogf_advanced',
		)
	);

	$wp_customize->add_section(
		'ogf_advanced__theme',
		array(
			'title' => esc_html__( 'Theme Elements', 'olympus-google-fonts' ),
			'panel' => 'ogf_advanced',
		)
	);

	$wp_customize->add_section(
		'ogf_advanced__branding',
		array(
			'title' => esc_html__( 'Branding', 'olympus-google-fonts' ),
			'panel' => 'ogf_advanced',
		)
	);

	$wp_customize->add_section(
		'ogf_advanced__navigation',
		array(
			'title' => esc_html__( 'Navigation', 'olympus-google-fonts' ),
			'panel' => 'ogf_advanced',
		)
	);

	$wp_customize->add_section(
		'ogf_advanced__content',
		array(
			'title' => esc_html__( 'Content', 'olympus-google-fonts' ),
			'panel' => 'ogf_advanced',
		)
	);

	$wp_customize->add_section(
		'ogf_advanced__sidebar',
		array(
			'title' => esc_html__( 'Sidebar', 'olympus-google-fonts' ),
			'panel' => 'ogf_advanced',
		)
	);

	$wp_customize->add_section(
		'ogf_advanced__footer',
		array(
			'title' => esc_html__( 'Footer', 'olympus-google-fonts' ),
			'panel' => 'ogf_advanced',
		)
	);

	$wp_customize->add_section(
		'ogf_advanced__css',
		array(
			'title' => esc_html__( 'Load Fonts Only', 'olympus-google-fonts' ),
			'panel' => 'ogf_advanced',
		)
	);

	if ( ogf_is_woocommerce_activated() ) {

		$ogf_woocommerce_panel = new OGF_Customize_Panel(
			$wp_customize,
			'ogf_woocommerce',
			array(
				'title' => __( 'WooCommerce', 'olympus-google-fonts' ),
				'panel' => 'ogf_google_fonts',
			)
		);

		$wp_customize->add_panel( $ogf_woocommerce_panel );

		$wp_customize->add_section(
			'ogf_wc_shop',
			array(
				'title' => esc_html__( 'WooCommerce Shop Page', 'olympus-google-fonts' ),
				'panel' => 'ogf_woocommerce',
			)
		);
		$wp_customize->add_section(
			'ogf_wc_single',
			array(
				'title' => esc_html__( 'WooCommerce Single Product', 'olympus-google-fonts' ),
				'panel' => 'ogf_woocommerce',
			)
		);
		$wp_customize->add_section(
			'ogf_wc_block',
			array(
				'title' => esc_html__( 'WooCommerce Blocks', 'olympus-google-fonts' ),
				'panel' => 'ogf_woocommerce',
			)
		);
		$wp_customize->add_section(
			'ogf_wc_cart',
			array(
				'title' => esc_html__( 'WooCommerce Cart', 'olympus-google-fonts' ),
				'panel' => 'ogf_woocommerce',
			)
		);
		$wp_customize->add_section(
			'ogf_wc_checkout',
			array(
				'title' => esc_html__( 'WooCommerce Checkout', 'olympus-google-fonts' ),
				'panel' => 'ogf_woocommerce',
			)
		);
	}
}
add_action( 'customize_register', 'ogf_panels_customize_register' );
