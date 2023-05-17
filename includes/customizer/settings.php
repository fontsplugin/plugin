<?php
/**
 * Register the customizer settings.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Register the custom Typography control.
 *
 * @param object $wp_customize Access to the $wp_customize object.
 */
function ogf_register_typography_control( $wp_customize ) {
	if ( ! class_exists( 'OGF_Customize_Typography_Control' ) ) {
		require_once OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-typography-control.php';
		$wp_customize->register_control_type( 'OGF_Customize_Typography_Control' );
	}
}
add_action( 'customize_register', 'ogf_register_typography_control', 10 );

/**
 * An array containing the customizer sections, settings and controls.
 *
 * @param object $wp_customize Access to the $wp_customize object.
 */
function ogf_customize_register( $wp_customize ) {
	require_once OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-multiple-fonts-control.php';
	require_once OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-repeater-control.php';
	require_once OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-upsell-control.php';
	require_once OGF_DIR_PATH . 'includes/customizer/controls/class-ogf-customize-multiple-checkbox-control.php';

	$wp_customize->register_control_type( 'OGF_Customize_Multiple_Fonts_Control' );
	$wp_customize->register_control_type( 'OGF_Customize_Multiple_Checkbox_Control' );

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
				'type'        => 'ogf-typography-multiselect',
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
				$id . '_font_size_tablet',
				array(
					'transport' => 'refresh',
				)
			);

			$wp_customize->add_setting(
				$id . '_font_size_mobile',
				array(
					'transport' => 'refresh',
				)
			);

			$wp_customize->add_setting(
				$id . '_line_height',
				array(
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_line_height_tablet',
				array(
					'transport' => 'refresh',
				)
			);

			$wp_customize->add_setting(
				$id . '_line_height_mobile',
				array(
					'transport' => 'refresh',
				)
			);

			$wp_customize->add_setting(
				$id . '_font_color',
				array(
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_letter_spacing',
				array(
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_text_transform',
				array(
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_setting(
				$id . '_text_decoration',
				array(
					'transport' => 'postMessage',
				)
			);

			$wp_customize->add_control(
				new OGF_Customize_Typography_Control(
					$wp_customize,
					$id . '_typography',
					array(
						'priority'    => ( isset( $values['priority'] ) ? absint( $values['priority'] ) : 10 ),
						'label'       => ( isset( $values['label'] ) ? esc_attr( $values['label'] ) : '' ),
						'description' => ( isset( $values['description'] ) ? esc_attr( $values['description'] ) : '' ),
						'section'     => ( isset( $values['section'] ) ? esc_attr( $values['section'] ) : '' ),
						'type'        => 'ogf-typography',
						'settings'    =>
							apply_filters(
								'ogf_typography_control_settings',
								array(
									'family'             => $id . '_font',
									'weight'             => $id . '_font_weight',
									'style'              => $id . '_font_style',
									'size'               => $id . '_font_size',
									'size_tablet'        => $id . '_font_size_tablet',
									'size_mobile'        => $id . '_font_size_mobile',
									'line_height'        => $id . '_line_height',
									'line_height_tablet' => $id . '_line_height_tablet',
									'line_height_mobile' => $id . '_line_height_mobile',
									'color'              => $id . '_font_color',
									'letter_spacing'     => $id . '_letter_spacing',
									'text_transform'     => $id . '_text_transform',
								),
								$id
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
			'description' => esc_html__( 'If your choices are not displaying correctly, check this box.', 'olympus-google-fonts' ),
			'section'     => 'ogf_debugging',
			'settings'    => 'ogf_force_styles',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'ogf_disable_post_level_controls',
		array(
			'default'           => '',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);

	$wp_customize->add_control(
		'ogf_disable_post_level_controls',
		array(
			'label'       => esc_html__( 'Disable Editor Controls', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Remove font controls from the individual post editor screen (Gutenberg and Classic).', 'olympus-google-fonts' ),
			'section'     => 'ogf_debugging',
			'settings'    => 'ogf_disable_post_level_controls',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'ogf_use_px',
		array(
			'default'           => 'true',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);

	$wp_customize->add_control(
		'ogf_use_px',
		array(
			'label'       => esc_html__( 'Use px Font Sizes', 'olympus-google-fonts' ),
			'description' => esc_html__( 'Replace the default (pt) font sizes with px values in the Classic Editor.', 'olympus-google-fonts' ),
			'section'     => 'ogf_debugging',
			'settings'    => 'ogf_use_px',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'ogf_font_display',
		array(
			'sanitize_callback' => 'ogf_sanitize_select',
			'default'           => 'swap',
		)
	);

	$wp_customize->add_control(
		'ogf_font_display',
		array(
			'label'       => esc_html__( 'Font Display', 'olympus-google-fonts' ),
			'description' => '<a href = "https: //fontsplugin.com/google-fonts-font-display-swap/#values">' . esc_html__( 'Learn more →', 'olympus-google-fonts' ) . '</a>',
			'type'        => 'select',
			'section'     => 'ogf_debugging',
			'choices'     => array(
				'swap'     => esc_html__( 'Swap', 'olympus-google-fonts' ),
				'block'    => esc_html__( 'Block', 'olympus-google-fonts' ),
				'fallback' => esc_html__( 'Fallback', 'olympus-google-fonts' ),
				'optional' => esc_html__( 'Optional', 'olympus-google-fonts' ),
			),
		)
	);

	$fonts   = new OGF_Fonts();
	$subsets = [];

	if ( $fonts->has_google_fonts() ) {

		// Build the selective font loading controls.
		foreach ( $fonts->choices as $font_id ) {

			if ( ! ogf_is_google_font( $font_id ) ) {
				continue;
			}

			$weights      = $fonts->get_font_weights( $font_id );
			$name         = $fonts->get_font_name( $font_id );
			$all_variants = ogf_font_variants();
			$new_variants = array();

			foreach ( $weights as $key => $value ) {
				$new_variants[ $key ] = $all_variants[ $key ];
			}

			// remove the 'default' value.
			unset( $new_variants[0] );

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
						'type'        => 'ogf-multiple-checkbox',
					)
				)
			);

			$subsets = array_merge( $subsets, $fonts->get_font_subsets( $font_id ) );
		}

		$wp_customize->add_setting(
			'fpp_disable_subsets',
			array(
				'default'   => array(),
				'transport' => 'refresh',
			)
		);

		if ( defined( 'OGF_PRO' ) ) {

			$wp_customize->add_control(
				new OGF_Customize_Multiple_Checkbox_Control(
					$wp_customize,
					'fpp_disable_subsets',
					array(
						'label'   => 'Remove Subsets',
						'section' => 'ogf_font_subsets',
						'choices' => array_unique( $subsets ),
						'type'    => 'ogf-multiple-checkbox',
					)
				)
			);
		}
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
		'ogf_optimization',
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
					'type'     => 'ogf-upsell',
				)
			)
		);

	}

}
add_action( 'customize_register', 'ogf_customize_register', 20 );

/**
 * Sanitize value from select field.
 *
 * @param string $input The selected input.
 * @param object $setting The setting.
 * @return bool
 */
function ogf_sanitize_select( $input, $setting ) {
	// Ensure input is a slug.
	$input = sanitize_key( $input );

	// Get list of choices from the control associated with the setting.
	$choices = $setting->manager->get_control( $setting->id )->choices;

	// If the input is a valid key, return it; otherwise, return the default.
	return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}
