<?php

namespace IdeoLogix\DigitalLicenseManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 * @package IdeoLogix\DigitalLicenseManager
 */
class Settings {

	const SECTION_GENERAL = 'dlm_settings_general';
	const SECTION_WOOCOMMERCE = 'dlm_settings_woocommerce';
	const SECTION_DELIVERY = 'dlm_settings_delivery';

	/**
	 * Helper function to get a setting by name.
	 *
	 * @param string $field
	 * @param string $section
	 *
	 * @return bool|mixed
	 */
	public static function get( $field, $section = self::SECTION_GENERAL ) {
		$settings = get_option( $section, array() );
		$value    = false;

		if ( ! $settings ) {
			$settings = array();
		}

		if ( array_key_exists( $field, $settings ) ) {
			$value = $settings[ $field ];
		}

		return $value;
	}
}