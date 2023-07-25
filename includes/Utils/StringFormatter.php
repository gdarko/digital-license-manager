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
 * Class StringFormatter
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class StringFormatter {

	/**
	 *
	 * @param $size
	 *
	 * @return string
	 */
	public static function formatBytes( $size ) {
		$base   = log( $size ) / log( 1024 );
		$suffix = array( "", "KB", "MB", "GB", "TB" );
		$f_base = floor( $base );

		return round( pow( 1024, $base - floor( $base ) ), 1 ) . $suffix[ $f_base ];
	}

	/**
	 *  Converts dashes to camel case with first capital letter.
	 *
	 * @param $input
	 * @param string $separator
	 *
	 * @return array|string|string[]
	 */
	public static function camelize( $input, $separator = '_' ) {
		return str_replace( $separator, '', ucwords( $input, $separator ) );
	}


	/**
	 * Obuscate the given string
	 *
	 * @param string|null $string
	 *
	 * @return string|null
	 */
	public static function obfuscateString( $string = null ) {
		if ( ! $string ) {
			return null;
		}
		$string       = str_replace( '-', '*', $string );
		$length       = strlen( $string );
		$visibleCount = (int) round( $length / 4 );
		$hiddenCount  = $length - ( $visibleCount * 2 );

		return substr( $string, 0, $visibleCount ) . str_repeat( '*', $hiddenCount ) . substr( $string, ( $visibleCount * - 1 ), $visibleCount );
	}

}
