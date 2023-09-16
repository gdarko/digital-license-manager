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
 * Class ArrayFormatter
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class ArrayFormatter {

	/**
	 * Return only specific parts of the data
	 *
	 * @param $data
	 * @param $keys
	 *
	 * @return array
	 */
	public static function only( $data, $keys ) {

		$valid = array();
		foreach ( $keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$valid[ $key ] = $data[ $key ];
			}
		}

		return $valid;
	}


	/**
	 * Check if array is list
	 *
	 * @param $array
	 *
	 * @return bool
	 */
	public static function isList( $array ) {
		if ( function_exists( '\array_is_list' ) ) {
			return array_is_list( $array );
		} else {
			$expectedKey = 0;
			foreach ( $array as $i => $_value ) {
				if ( $i !== $expectedKey ) {
					return false;
				}
				$expectedKey ++;
			}

			return true;
		}
	}

	/**
	 * To camel case keys
	 *
	 * @param $array
	 *
	 * @return array
	 */
	public static function camelCaseKeys( $array ) {
		$newArr = [];

		foreach ( $array as $i => $v ) {
			$k            = lcfirst( implode( '', array_map( 'ucfirst', explode( '_', $i ) ) ) );
			$newArr[ $k ] = $v;
		}

		return $newArr;
	}

	/**
	 * Obtain element from array
	 *
	 * @param $array
	 * @param $key
	 * @param $default
	 *
	 * @return void
	 */
	public static function get( $array, $key, $default = null ) {
		return isset( $array[ $key ] ) ? $array[ $key ] : $default;
	}

}
