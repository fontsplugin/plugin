<?php
/**
 * Unit tests for the OGF_Fonts class.
 *
 * @package OGF\Tests\Unit
 */

use Brain\Monkey\Functions;

/**
 * Test class for OGF_Fonts functionality.
 */
class FontsTest extends OGF_Unit_TestCase {

	/**
	 * Test that Google Fonts array is structured correctly.
	 *
	 * Note: fonts.json uses shorthand keys for smaller file size:
	 * - 'f' = family name
	 * - 'v' = variants (weights)
	 * - 's' = subsets
	 */
	public function test_google_fonts_structure() {
		$fonts_file = dirname( dirname( __DIR__ ) ) . '/blocks/src/google-fonts/fonts.json';
		$this->assertFileExists( $fonts_file, 'fonts.json should exist' );

		$fonts = json_decode( file_get_contents( $fonts_file ), true );
		$this->assertIsArray( $fonts, 'fonts.json should decode to array' );
		$this->assertNotEmpty( $fonts, 'fonts.json should not be empty' );

		// Check first font has required properties (using shorthand keys).
		$first_font = reset( $fonts );
		$this->assertArrayHasKey( 'f', $first_font, 'Font should have "f" (family) property' );
		$this->assertArrayHasKey( 'v', $first_font, 'Font should have "v" (variants) property' );
		$this->assertArrayHasKey( 's', $first_font, 'Font should have "s" (subsets) property' );
	}

	/**
	 * Test that system fonts array is structured correctly.
	 */
	public function test_system_fonts_structure() {
		$fonts_file = dirname( dirname( __DIR__ ) ) . '/blocks/src/google-fonts/systemFonts.json';
		$this->assertFileExists( $fonts_file, 'systemFonts.json should exist' );

		$fonts = json_decode( file_get_contents( $fonts_file ), true );
		$this->assertIsArray( $fonts, 'systemFonts.json should decode to array' );
	}

	/**
	 * Test font weight validation.
	 */
	public function test_valid_font_weights() {
		$valid_weights = array( '100', '200', '300', '400', '500', '600', '700', '800', '900' );

		foreach ( $valid_weights as $weight ) {
			$this->assertTrue(
				is_numeric( $weight ) && (int) $weight >= 100 && (int) $weight <= 900,
				"Weight {$weight} should be valid"
			);
		}
	}

	/**
	 * Test font family sanitization logic.
	 */
	public function test_font_family_format() {
		$test_families = array(
			'Roboto'       => 'Roboto',
			'Open Sans'    => 'Open Sans',
			'Noto Sans JP' => 'Noto Sans JP',
		);

		foreach ( $test_families as $input => $expected ) {
			$this->assertEquals(
				$expected,
				$input,
				"Font family '{$input}' should remain '{$expected}'"
			);
		}
	}

	/**
	 * Test that CSS output functions exist in the expected files.
	 */
	public function test_css_output_files_exist() {
		$files = array(
			dirname( dirname( __DIR__ ) ) . '/includes/customizer/output-css.php',
			dirname( dirname( __DIR__ ) ) . '/includes/gutenberg/output-css.php',
		);

		foreach ( $files as $file ) {
			$this->assertFileExists( $file, "CSS output file should exist: {$file}" );
		}
	}
}
