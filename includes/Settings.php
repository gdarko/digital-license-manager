<?php
/**
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
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

namespace IdeoLogix\DigitalLicenseManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 * @package IdeoLogix\DigitalLicenseManager
 */
class Settings {

	const SECTION_GENERAL = 'dlm_settings_general';
	const SECTION_WOOCOMMERCE = 'dlm_settings_woocommerce';

	/**
	 * Helper function to get a setting by name.
	 *
	 * @param string $field
	 * @param string $section
	 *
	 * Code inspired by "License Manager for WooCommerce" plugin
	 * @copyright  2019-2022 Drazen Bebic
	 * @copyright  2022-2023 WPExperts.io
	 * @copyright  2020-2023 Darko Gjorgjijoski
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

	/**
	 * Is license auto delivery enabled?
	 * @return bool
	 */
	public static function isAutoDeliveryEnabled() {
		return (bool) Settings::get( 'auto_delivery', self::SECTION_WOOCOMMERCE );
	}
}
