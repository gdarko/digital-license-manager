<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-present  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

if ( PHP_VERSION_ID >= 80000 && file_exists( $_tests_dir . '/includes/phpunit7/MockObject' ) ) {
	// WP Core test library includes patches for PHPUnit 7 to make it compatible with PHP8.
	require_once $_tests_dir . '/includes/phpunit7/MockObject/Builder/NamespaceMatch.php';
	require_once $_tests_dir . '/includes/phpunit7/MockObject/Builder/ParametersMatch.php';
	require_once $_tests_dir . '/includes/phpunit7/MockObject/InvocationMocker.php';
	require_once $_tests_dir . '/includes/phpunit7/MockObject/MockMethod.php';
}


// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {

	$_plugin_dir = dirname( __FILE__ ) . '/../../';

	if ( ! function_exists( 'activate_plugin' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	require_once dirname( __FILE__ ) . '/helpers/class-dlm-helper-settings.php';
	DLM_Helper_Settings::setDefaults();

	tests_add_filter( 'dlm_mock_is_plugin_active', '__return_true' );

	// Required files for the plugin
	$required = [
		realpath( $_plugin_dir . '..' ) . '/woocommerce/woocommerce.php',
	];
	foreach ( $required as $item ) {
		if ( file_exists( $item ) ) {
			echo 'Loaded: '.$item . PHP_EOL;
			require_once $item;
		} else {
			echo "Could not find " . plugin_basename( $item ) . " plugin which is required for unit tests.";
			exit( 1 );
		}
	}

	// Set a default currency to be used for the multi-currency tests because the default
	// is not loaded even though it's set during the tests setup.
	update_option( 'woocommerce_currency', 'USD' );
	require $_plugin_dir . '/digital-license-manager.php';

	IdeoLogix\DigitalLicenseManager\Setup::install( false );

}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Need those polyfills to run tests in CI.
require_once dirname( __FILE__ ) . '/../../vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// We use outdated PHPUnit version, which emits deprecation errors in PHP 7.4 (deprecated reflection APIs).
if ( defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID >= 70400 ) {
	error_reporting( error_reporting() ^ E_DEPRECATED ); // phpcs:ignore
}
