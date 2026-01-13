<?php
/**
 * Integration tests for Customizer functionality.
 *
 * These tests require WordPress to be loaded.
 * Run via: composer test:integration (requires WP test suite setup)
 *
 * NOTE: Integration tests run automatically in GitHub Actions CI.
 * For local testing, run: bin/install-wp-tests.sh <db-name> <db-user> <db-pass>
 *
 * @package OGF\Tests\Integration
 */

/**
 * Test class for Customizer integration.
 */
class CustomizerTest extends WP_UnitTestCase {

	/**
	 * Test that plugin is loaded.
	 */
	public function test_plugin_loaded() {
		$this->assertTrue(
			class_exists( 'Jeremyandlauren_Google_Fonts' ) || defined( 'OGF_VERSION' ),
			'Plugin should be loaded'
		);
	}

	/**
	 * Test theme mod saving and retrieval.
	 */
	public function test_theme_mod_round_trip() {
		$test_font = 'Roboto';
		set_theme_mod( 'ogf_test_font', $test_font );

		$retrieved = get_theme_mod( 'ogf_test_font' );
		$this->assertEquals( $test_font, $retrieved, 'Theme mod should round-trip correctly' );

		// Cleanup.
		remove_theme_mod( 'ogf_test_font' );
	}
}
