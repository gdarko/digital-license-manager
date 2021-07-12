<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

/**
 * Class Compat
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class Compat {

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