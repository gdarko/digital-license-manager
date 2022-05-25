<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Digital_License_Manager
 */

$_plugin_path = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR;

if ( ! file_exists( $_plugin_path . 'vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' ) ) {
	exit( 'No polyfills installed.' );
}
require_once $_plugin_path . 'vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	$GLOBALS['DLM_PHPUNIT_RUNNING'] = true;

	$_plugins_path = dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR;
	if ( file_exists( $_plugins_path . '/woocommerce/woocommerce.php' ) ) {
		require $_plugins_path . '/woocommerce/woocommerce.php';
	}
	require dirname( dirname( __FILE__ ) ) . '/digital-license-manager.php';

	IdeoLogix\DigitalLicenseManager\Setup::install( is_multisite() );
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
