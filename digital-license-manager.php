<?php
/**
 * Plugin Name: Digital License Manager
 * Description: Easily manage and sell your digital licenses through your WordPress website. Compatible with WooCommerce for selling licenses, also works without it.
 * Version: 1.3.0
 * Author: Darko Gjorgjijoski
 * Author URI: https://darkog.com/
 * Requires at least: 4.7
 * Tested up to: 6.0
 * Requires PHP: 5.6
 * WC requires at least: 2.7
 * WC tested up to: 6.5
 * Text Domain: digital-license-manager
 * Domain Path: /i18n/languages/
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DLM_VERSION' ) ) {
	define( 'DLM_VERSION', '1.3.0' );
}
if ( ! defined( 'DLM_PURCHASE_URL' ) ) {
	define( 'DLM_PURCHASE_URL', 'https://codeverve.com/product/digital-license-manager-pro/' );
}
if ( ! defined( 'DLM_DOCUMENTATION_URL' ) ) {
	define( 'DLM_DOCUMENTATION_URL', 'https://docs.codeverve.com/digital-license-manager/' );
}
if ( ! defined( 'DLM_GITHUB_URL' ) ) {
	define( 'DLM_GITHUB_URL', 'https://github.com/gdarko/digital-license-manager' );
}
if ( ! defined( 'DLM_WP_FORUM_URL' ) ) {
	define( 'DLM_WP_FORUM_URL', 'https://wordpress.org/support/plugin/digital-license-manager' );
}

// Sometimes we just need to get version or other shared constants of the base plugin, instead of hard-coding it on different places.
// Eg. If this is used as composer package and we need to know the version in the extending package code.
if ( defined( 'DLM_SHORT_INIT' ) && DLM_SHORT_INIT ) {
	return;
}

if ( ! defined( 'DLM_PLUGIN_FILE' ) ) {
	define( 'DLM_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'DLM_ABSPATH' ) ) {
	define( 'DLM_ABSPATH', trailingslashit( plugin_dir_path( DLM_PLUGIN_FILE ) ) );
}
if ( ! defined( 'DLM_PLUGIN_URL' ) ) {
	define( 'DLM_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

require_once DLM_ABSPATH . 'vendor/autoload.php';

if ( ! function_exists( 'digital_license_manager' ) ) {
	function digital_license_manager() {
		return IdeoLogix\DigitalLicenseManager\Boot::instance();
	}
}

digital_license_manager();
