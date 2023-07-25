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

namespace IdeoLogix\DigitalLicenseManager\Database\Models\Resources;

use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use stdClass;

defined( 'ABSPATH' ) || exit;

/**
 * Class LicenseMeta
 * @package IdeoLogix\DigitalLicenseManager\Database\Models\Resources
 */
class LicenseMeta {
	/**
	 * @var int
	 */
	protected $meta_id;

	/**
	 * @var int
	 */
	protected $license_id;

	/**
	 * @var string
	 */
	protected $meta_key;

	/**
	 * @var mixed
	 */
	protected $meta_value;

	/**
	 * License constructor.
	 *
	 * @param stdClass $licenseMeta
	 */
	public function __construct( $licenseMeta ) {
		if ( ! $licenseMeta instanceof stdClass ) {
			return;
		}

		$this->meta_id    = (int) $licenseMeta->meta_id;
		$this->license_id = (int) $licenseMeta->license_id;
		$this->meta_key   = $licenseMeta->meta_key;
		$this->meta_value = JsonFormatter::decode( $licenseMeta->meta_value, true );
	}

	/**
	 * @return int
	 */
	public function getMetaId() {
		return $this->meta_id;
	}

	/**
	 * @param int $meta_id
	 */
	public function setMetaId( $meta_id ) {
		$this->meta_id = $meta_id;
	}

	/**
	 * @return int
	 */
	public function getLicenseId() {
		return $this->license_id;
	}

	/**
	 * @param int $license_id
	 */
	public function setLicenseId( $license_id ) {
		$this->license_id = $license_id;
	}

	/**
	 * @return string
	 */
	public function getMetaKey() {
		return $this->meta_key;
	}

	/**
	 * @param string $meta_key
	 */
	public function setMetaKey( $meta_key ) {
		$this->meta_key = $meta_key;
	}

	/**
	 * @return mixed
	 */
	public function getMetaValue() {
		return $this->meta_value;
	}

	/**
	 * @param mixed $meta_value
	 */
	public function setMetaValue( $meta_value ) {
		$this->meta_value = $meta_value;
	}
}
