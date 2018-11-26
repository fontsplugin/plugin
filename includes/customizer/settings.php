<?php
/**
 * Build the URL to load the chosen Google Fonts.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2018, Danny Cooper
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * An array containing the customizer sections, settings and controls.
 *
 * @param object $wp_customize Access to the $wp_customize object.
 */
function ogf_customize_register( $wp_customize ) {
	require OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-typography-control.php';
	require OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-repeater-control.php';

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
					'label'   => esc_html__( 'Custom Elements', 'customizer-repeater' ),
					'section' => 'ogf_custom',
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
					'default'   => 'normal',
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

}
add_action( 'customize_register', 'ogf_customize_register' );
