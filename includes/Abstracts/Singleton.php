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

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

/**
 * Class Singleton
 * @package IdeoLogix\DigitalLicenseManager\Abstracts
 *
 * Code inspired by "License Manager for WooCommerce" plugin,
 * modified by Darko Gjorgjijoski for the purpose of Digital License Manager.
 *
 * @copyright  2019-2022 Drazen Bebic
 * @copyright  2022-2023 WPExperts.io
 * @copyright  2020-2023 Darko Gjorgjijoski
 *
 * @depreacted 1.5.0 - Deprecated in favor of Traits/Singleton
 */
class Singleton {

	/**
	 * The instance object
	 * @var self
	 */
	private static $instances = [];

	/**
	 * @return $this
	 */
	public static function instance()
	{
		$calledClass = get_called_class();

		if ( ! isset( self::$instances[ $calledClass ] ) ) {
			self::$instances[ $calledClass ] = new $calledClass();
		}

		return self::$instances[ $calledClass ];
	}
}