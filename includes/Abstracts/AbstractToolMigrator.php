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

abstract class AbstractToolMigrator {

	/**
	 * The unique identifier of the migrator
	 * @var mixed
	 */
	protected $id;

	/**
	 * The name of the migrator
	 * @var string
	 */
	protected $name;

	/**
	 * Returns the unique identifier of the migrator
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Returns the name of the migrator
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the migrator steps
	 * @return array|\WP_Error
	 */
	abstract public function getSteps();


	/**
	 * Check if the it is possible to faciliate migration
	 * @return bool|\WP_Error
	 */
	abstract public function checkAvailability();

	/**
	 * Initializes the process
	 *
	 * @param array $data
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function init( $data = array() );

	/**
	 * Initializes the process
	 *
	 * @param $step
	 * @param array $data
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function doStep( $step, $page, $data = array() );

	/**
	 * Undoes the migration process
	 *
	 * @return bool
	 */
	abstract public function undo();

}
