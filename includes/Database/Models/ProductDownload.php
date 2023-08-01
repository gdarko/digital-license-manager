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

namespace IdeoLogix\DigitalLicenseManager\Database\Models;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class ProductDownload extends AbstractDataModel {

	/**
	 * Are timestamps created_at/updated_at supported?
	 * @var bool
	 */
	protected $timestamps = true;

	/**
	 * The primary key
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * The table name
	 * @var string
	 */
	protected $table = DatabaseTable::PRODUCT_DOWNLOADS;

	/**
	 * The casts
	 * @var string[]
	 */
	protected $casts = [
		'id'            => 'int',
		'license_id'    => 'int',
		'activation_id' => 'int',
		'meta_data'     => 'json',
	];

	public function getId() {
		return $this->get( 'id' );
	}

	public function getLicenseId() {
		return $this->get( 'license_id' );
	}

	public function getActivationId() {
		return $this->get( 'activation_id' );
	}

	public function getSource() {
		return $this->get( 'source' );
	}

	public function getIpAddress() {
		return $this->get( 'ip_address' );
	}

	public function getUserAgent() {
		return $this->get( 'user_agent' );
	}

	public function getMetaData() {
		return $this->get( 'meta_data' );
	}

	public function getCreatedAt() {
		return $this->get( 'created_at' );
	}

	public function getUpdatedAt() {
		return $this->get( 'updated_at' );
	}

}