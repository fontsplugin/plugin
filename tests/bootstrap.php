<?php
/**
 * PHPUnit bootstrap file for Olympus Google Fonts tests.
 *
 * @package OGF\Tests
 */

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load Brain\Monkey for unit tests.
require_once dirname( __DIR__ ) . '/vendor/brain/monkey/inc/patchwork-loader.php';

use Brain\Monkey;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Base test case for unit tests (no WordPress loaded).
 */
class OGF_Unit_TestCase extends TestCase {
	/**
	 * Set up Brain\Monkey before each test.
	 */
	protected function set_up() {
		parent::set_up();
		Monkey\setUp();
	}

	/**
	 * Tear down Brain\Monkey after each test.
	 */
	protected function tear_down() {
		Monkey\tearDown();
		parent::tear_down();
	}
}

/**
 * Check if WordPress test suite is available and load it.
 */
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// If WP test suite exists, load it (makes WP_UnitTestCase available).
if ( file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	// Give access to tests_add_filter() function.
	require_once "{$_tests_dir}/includes/functions.php";

	/**
	 * Manually load the plugin being tested.
	 */
	function _manually_load_plugin() {
		require dirname( __DIR__ ) . '/olympus-google-fonts.php';
	}
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

	// Start up the WP testing environment.
	require "{$_tests_dir}/includes/bootstrap.php";
}
