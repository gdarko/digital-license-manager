<?php
/**
 * Plugin Name: Digital License Manager
 * Description: Easily manage and sell your license keys on your website. Compatible with WooCommerce for selling licenses but also works without it.
 * Version: 1.5.6
 * Author: Darko Gjorgjijoski
 * Author URI: https://darkog.com/
 * Text Domain: digital-license-manager
 * Domain Path: /i18n/languages/
 * WC requires at least: 2.7
 * WC tested up to: 8.3.1
 * License: GPLv3
 *
 ****************************************************************************
 *
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2023  Darko Gjorgjijoski. All Rights Reserved.
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
 * You should have received a copy of the GNU General Public License
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/licenses.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DLM_VERSION' ) ) {
	define( 'DLM_VERSION', '1.5.6' );
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

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

if ( ! function_exists( 'digital_license_manager' ) ) {
	function digital_license_manager() {
		return IdeoLogix\DigitalLicenseManager\Boot::instance();
	}
}

digital_license_manager();
