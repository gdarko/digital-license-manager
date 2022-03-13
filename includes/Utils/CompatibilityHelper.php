<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class CompatibilityHelper
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class CompatibilityHelper {

	/**
	 * Check if is plugin active
	 *
	 * @param $plugin
	 *
	 * @return bool
	 */
	public static function is_plugin_active( $plugin ) {

		if ( function_exists( '\is_plugin_active' ) ) {
			return \is_plugin_active( $plugin );
		} else {
			return in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
		}
	}

	/**
	 * Returns list of multisite sites
	 * @return array|int
	 */
	public static function get_site_ids() {

		global $wp_version;

		if ( version_compare( $wp_version, '4.6', '>=' ) ) {
			$blog_ids = get_sites( [ 'fields' => 'ids' ] );
		} else {
			global $wpdb;
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		}

		return $blog_ids;

	}

}
