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

}
