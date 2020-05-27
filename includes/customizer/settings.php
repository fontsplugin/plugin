<?php
/**
 * Register the customizer settings.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * An array containing the customizer sections, settings and controls.
 *
 * @param object $wp_customize Access to the $wp_customize object.
 */
function ogf_customize_register( $wp_customize ) {
	require OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-multiple-fonts-control.php';
	require OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-typography-control.php';
	require OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-repeater-control.php';
	require OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-upsell-control.php';
	require OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-multiple-checkbox-control.php';

	$wp_customize->register_control_type( 'OGF_Customize_Multiple_Fonts_Control' );
	$wp_customize->register_control_type( 'OGF_Customize_Multiple_Checkbox_Control' );
	$wp_customize->register_control_type( 'OGF_Customize_Typography_Control' );

	$wp_customize->add_setting(
		'ogf_custom_selectors',
		array(
			'transport' => 'postMessage',
		)
	);

	$wp_customize->add_control(
		new OGF_Customize_Repeater_Control(
			$wp_customize,
			'ogf_custom_selectors',
			array(
				'label'   => esc_html__( 'Custom Elements', 'olympus-google-fonts' ),
				'section' => 'ogf_custom',
			)
		)
	);

	$wp_customize->add_setting(
		'ogf_load_fonts',
		array(
			'transport' => 'postMessage',
		)
	);

	$wp_customize->add_control(
		new OGF_Customize_Multiple_Fonts_Control(
			$wp_customize,
			'ogf_load_fonts',
			array(
				'label'       => esc_html__( 'Load Fonts Only', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Load fonts but don\'t automatically assign them to an element.', 'olympus-google-fonts' ),
				'section'     => 'ogf_advanced__css',
			)
		)
	);

	/**
	 * Build customizer controls.
	 *
	 * @param array $elements array of elements to build controls based on.
	 */
	function ogf_build_customizer_controls( $elements ) {

		global $wp_customize;

		foreach ( $elements as $id => $values ) {

			$wp_customize->add_setting(
				$id . '_font',
				array(
					'default'   => 'default',
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_font_weight',
				array(
					'default'   => '0',
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_font_style',
				array(
					'default'   => 'default',
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_font_size',
				array(
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_line_height',
				array(
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_font_color',
				array(
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new OGF_Customize_Typography_Control(
					$wp_customize,
					$id . '_typography',
					array(
						'priority'  =>  ( isset( $values['priority'] ) ? absint( $values['priority'] ) : 10 ),
						'label'       => esc_attr( $values['label'] ),
						'description' => esc_attr( $values['description'] ),
						'section'     => esc_attr( $values['section'] ),
						'settings'    => array(
							'family'      => $id . '_font',
							'weight'      => $id . '_font_weight',
							'style'       => $id . '_font_style',
							'size'        => $id . '_font_size',
							'line_height' => $id . '_line_height',
							'color'       => $id . '_font_color',
						),
					)
				)
			);
		}
	}

	ogf_build_customizer_controls( ogf_get_elements() );
	ogf_build_customizer_controls( ogf_get_custom_elements() );

	$wp_customize->add_setting(
		'ogf_force_styles',
		array(
			'default'           => '',
			'transport'         => 'refresh',
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);

	$wp_customize->add_control(
		'force_styles',
		array(
			'label'       => esc_html__( 'Force Styles?', 'olympus-google-fonts' ),
			'section'     => 'ogf_debugging',
			'settings'    => 'ogf_force_styles',
			'type'        => 'checkbox',
			'description' => esc_html__( 'If your choices are not displaying correctly, check this box.', 'olympus-google-fonts' ),
		)
	);

	$fonts = new OGF_Fonts();

	$choices = $fonts->choices;

	// Build the selective font loading controls.
	foreach ( $choices as $font_id ) {

		if ( ogf_is_system_font( $font_id ) ) {
			return;
		}

		$weights      = $fonts->get_font_weights( $font_id );
		$name         = $fonts->get_font_name( $font_id );
		$all_variants = ogf_font_variants();
		$new_variants = array();
		foreach ( $weights as $key => $value ) {
			$new_variants[ $key ] = $all_variants[ $key ];
		}
		unset($new_variants[0]);


		$wp_customize->add_setting(
			$font_id . '_weights',
			array(
				'default'   => array( '100', '200', '300', '400', '500', '600', '700', '800', '900', '100i', '200i', '300i', '400i', '500i', '600i', '700i', '800i', '900i' ),
				'transport' => 'refresh',
			)
		);

		$input_attrs = array();

		if ( ! defined( 'OGF_PRO' ) ) {
			$input_attrs = array(
				'disabled' => false,
			);
		}

		$wp_customize->add_control(
			new OGF_Customize_Multiple_Checkbox_Control(
				$wp_customize,
				$font_id . '_weights',
				array(
					'label'       => $name,
					'section'     => 'ogf_font_loading',
					'choices'     => $new_variants,
					'input_attrs' => $input_attrs,
				)
			)
		);

	}

	$upsell_locations = array(
		'ogf_basic',
		'ogf_advanced',
		'ogf_advanced__branding',
		'ogf_advanced__navigation',
		'ogf_advanced__content',
		'ogf_advanced__sidebar',
		'ogf_advanced__footer',
		'ogf_font_loading',
		'ogf_debugging',
	);

	foreach ( $upsell_locations as $loc ) {

		if ( defined( 'OGF_PRO' ) ) {
			return;
		}

		$wp_customize->add_setting( 'ogf_upsell_' . $loc );

		$wp_customize->add_control(
			new OGF_Customize_Upsell_Control(
				$wp_customize,
				'ogf_upsell_' . $loc,
				array(
					'section'  => $loc,
					'priority' => 120,
				)
			)
		);

	}

}
add_action( 'customize_register', 'ogf_customize_register' );
