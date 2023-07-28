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
 * Class LicenseSource
 * @package IdeoLogix\DigitalLicenseManager\Enums
 *
 * Code inspired by "License Manager for WooCommerce" plugin
 * @copyright  2019-2022 Drazen Bebic
 * @copyright  2022-2023 WPExperts.io
 * @copyright  2020-2023 Darko Gjorgjijoski
 *
 */
abstract class LicenseSource {

	/**
	 * Enumerator value used for generators.
	 *
	 * @var int
	 */
	const GENERATOR = 1;

	/**
	 * Enumerator value used for imports.
	 *
	 * @var int
	 */
	const IMPORT = 2;

	/**
	 * Enumerator value used for the API.
	 *
	 * @var int
	 */
	const API = 3;

	/**
	 * Enumerator value used for the API.
	 *
	 * @var int
	 */
	const MIGRATION = 4;

	/**
	 * Available enumerator values.
	 *
	 * @var array
	 */
	public static $sources = array(
		self::GENERATOR,
		self::IMPORT,
		self::API,
		self::MIGRATION
	);

	/**
	 * Returns the string representation of a specific enumerator value.
	 *
	 * @param int $source Source enumerator value
	 *
	 * @return string
	 */
	public static function getLabel( $source ) {
		$labels = array(
			self::GENERATOR => 'GENERATOR',
			self::IMPORT    => 'IMPORT',
			self::API       => 'API',
			self::MIGRATION => 'MIGRATION',
		);

		return $labels[ $source ];
	}
}
