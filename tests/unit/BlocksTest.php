<?php
/**
 * Unit tests for Gutenberg blocks.
 *
 * @package OGF\Tests\Unit
 */

/**
 * Test class for Gutenberg blocks functionality.
 */
class BlocksTest extends OGF_Unit_TestCase {

	/**
	 * Test that block build files exist.
	 */
	public function test_block_build_files_exist() {
		$build_dir = dirname( dirname( __DIR__ ) ) . '/blocks/build';

		$this->assertFileExists(
			"{$build_dir}/index.js",
			'Built block JS should exist'
		);

		$this->assertFileExists(
			"{$build_dir}/index.asset.php",
			'Block asset manifest should exist'
		);
	}

	/**
	 * Test that block init file exists.
	 */
	public function test_block_init_exists() {
		$init_file = dirname( dirname( __DIR__ ) ) . '/blocks/init.php';
		$this->assertFileExists( $init_file, 'blocks/init.php should exist' );
	}

	/**
	 * Test that block source files exist.
	 */
	public function test_block_source_files_exist() {
		$src_dir = dirname( dirname( __DIR__ ) ) . '/blocks/src';

		$required_files = array(
			'index.js',
			'common.scss',
			'google-fonts/index.js',
			'google-fonts/edit.js',
			'google-fonts/fonts.json',
			'google-fonts/systemFonts.json',
			'google-fonts/transforms.js',
		);

		foreach ( $required_files as $file ) {
			$this->assertFileExists(
				"{$src_dir}/{$file}",
				"Block source file should exist: {$file}"
			);
		}
	}

	/**
	 * Test that asset manifest returns valid structure.
	 */
	public function test_asset_manifest_structure() {
		$asset_file = dirname( dirname( __DIR__ ) ) . '/blocks/build/index.asset.php';
		$assets     = require $asset_file;

		$this->assertIsArray( $assets, 'Asset manifest should return array' );
		$this->assertArrayHasKey( 'dependencies', $assets, 'Assets should have dependencies' );
		$this->assertArrayHasKey( 'version', $assets, 'Assets should have version' );
		$this->assertIsArray( $assets['dependencies'], 'Dependencies should be array' );
	}
}
