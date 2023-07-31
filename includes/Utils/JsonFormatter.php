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

namespace IdeoLogix\DigitalLicenseManager\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class JsonFormatter
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class JsonFormatter {

	/**
	 * Validates json
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public static function validate( $data ) {

		if ( function_exists( '\json_validate' ) ) {
			return \json_validate( $data );
		} else {
			json_decode( $data, true );

			return json_last_error() === JSON_ERROR_NONE;
		}
	}

	/**
	 * Decodes object. If data is not valid JSON, returns data.
	 *
	 * @param $data
	 * @param bool $associative
	 *
	 * @return mixed
	 */
	public static function decode( $data, $associative = false ) {

		if ( is_null( $data ) ) {
			return null;
		}

		$result = json_decode( $data, $associative );

		return json_last_error() === JSON_ERROR_NONE ? $result : $data;
	}

	/**
	 * Encodes object.
	 *
	 * @param $data
	 *
	 * @return bool|float|int|string
	 */
	public static function encode( $data ) {
		if ( is_scalar( $data ) ) {
			return $data;
		} else {
			return json_encode( $data );
		}
	}

}
