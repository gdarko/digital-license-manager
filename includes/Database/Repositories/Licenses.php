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

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataRepository;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class Licenses extends AbstractDataRepository {


	/**
	 * Initializes the repository
	 * @return void
	 */
	protected function init() {
		$this->primaryKey = 'id';
		$this->dataTable  = DatabaseTable::LICENSES;
		$this->dataModel  = License::class;
		$this->searchable = [ 'order_id', 'product_id', 'user_id', 'license_key', 'hash', 'valid_for', 'source', 'status' ];
	}

}