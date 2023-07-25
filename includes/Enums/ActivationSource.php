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

namespace IdeoLogix\DigitalLicenseManager\Enums;

/**
 * Class ActivationSource
 * @package IdeoLogix\DigitalLicenseManager\Enums
 */
abstract class ActivationSource {

	/**
	 * Enumerator value
	 *
	 * @var int
	 */
	const WEB = 1;

	/**
	 * Enumerator value
	 *
	 * @var int
	 */
	const API = 2;

	/**
	 * Enumerator value
	 */
	const MIGRATION = 3;


	/**
	 * Format source
	 *
	 * @param int $src
	 *
	 * @return string
	 */
	public static function format( $src ) {
		$src = (int) $src;
		if ( $src === self::WEB ) {
			$str = __( 'Web', 'digital-license-manager' );
		} else if ( $src === self::API ) {
			$str = __( 'API', 'digital-license-manager' );
		} else if ( $src === self::MIGRATION ) {
			$str = __( 'Migration', 'digital-license-manager' );
		} else {
			$str = __( 'Other', 'digital-license-manager' );
		}

		return $str;
	}

	/**
	 * Returns all sources formatted
	 * @return array
	 */
	public static function all() {
		$sources = array();
		foreach ( array( self::WEB, self::API ) as $source ) {
			$sources[ $source ] = self::format( $source );
		}

		return $sources;
	}

}
