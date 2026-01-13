<?php
/**
 * Unit tests for Customizer files.
 *
 * @package OGF\Tests\Unit
 */

/**
 * Test class for Customizer file structure.
 */
class CustomizerFilesTest extends OGF_Unit_TestCase {

	/**
	 * Test that customizer settings file exists.
	 */
	public function test_customizer_settings_exists() {
		$settings_file = dirname( dirname( __DIR__ ) ) . '/includes/customizer/settings.php';
		$this->assertFileExists( $settings_file, 'Settings file should exist' );
	}

	/**
	 * Test that customizer panels file exists.
	 */
	public function test_customizer_panels_exists() {
		$panels_file = dirname( dirname( __DIR__ ) ) . '/includes/customizer/panels.php';
		$this->assertFileExists( $panels_file, 'Panels file should exist' );
	}

	/**
	 * Test that CSS output file exists.
	 */
	public function test_customizer_output_css_exists() {
		$css_file = dirname( dirname( __DIR__ ) ) . '/includes/customizer/output-css.php';
		$this->assertFileExists( $css_file, 'Output CSS file should exist' );
	}

	/**
	 * Test that all control classes exist.
	 */
	public function test_control_classes_exist() {
		$control_files = array(
			'class-ogf-customize-typography-control.php',
			'class-ogf-customize-repeater-control.php',
			'class-ogf-customize-multiple-checkbox-control.php',
			'class-ogf-customize-multiple-fonts-control.php',
			'class-ogf-customize-panel.php',
			'class-ogf-customize-upsell-control.php',
		);

		$controls_dir = dirname( dirname( __DIR__ ) ) . '/includes/customizer/controls';

		foreach ( $control_files as $file ) {
			$this->assertFileExists(
				"{$controls_dir}/{$file}",
				"Control file should exist: {$file}"
			);
		}
	}

	/**
	 * Test control files have valid PHP syntax.
	 */
	public function test_control_files_valid_php() {
		$controls_dir = dirname( dirname( __DIR__ ) ) . '/includes/customizer/controls';
		$files        = glob( "{$controls_dir}/*.php" );

		foreach ( $files as $filepath ) {
			$output = array();
			$result = 0;

			exec( "php -l {$filepath} 2>&1", $output, $result );

			$this->assertEquals(
				0,
				$result,
				"PHP syntax should be valid in: " . basename( $filepath ) . "\n" . implode( "\n", $output )
			);
		}
	}
}
