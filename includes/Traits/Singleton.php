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

namespace IdeoLogix\DigitalLicenseManager\Traits;

trait Singleton {

	/**
	 * The instance object
	 * @var Singleton
	 */
	private static $instance;

	/**
	 * Prevent new instances
	 */
	final private function __construct() {
		$this->init();
	}

	/**
	 * The singleton instance
	 * @return Singleton
	 */
	final public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Allow init method for initialization
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Prevent cloning of the instance
	 * @return void
	 */
	final private function __clone() {
	}

	/**
	 * Prevent serialization of the instance
	 * @return void
	 */
	final private function __sleep() {
	}

	/**
	 * Prevent deserialization of the instance
	 * @return void
	 */
	final private function __wakeup() {
	}
}