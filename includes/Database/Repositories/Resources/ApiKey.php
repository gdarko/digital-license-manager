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

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceRepository;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceRepositoryInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\ApiKey as ApiKeyResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\ColumnType as ColumnTypeEnum;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

class ApiKey extends AbstractResourceRepository implements ResourceRepositoryInterface {

	use Singleton;

	/**
	 * @var string
	 */
	const TABLE = DatabaseTable::API_KEYS;

	/**
	 * Country constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->table      = $wpdb->prefix . self::TABLE;
		$this->primaryKey = 'id';
		$this->model      = ApiKeyResourceModel::class;
		$this->mapping    = array(
			'user_id'         => ColumnTypeEnum::BIGINT,
			'description'     => ColumnTypeEnum::VARCHAR,
			'permissions'     => ColumnTypeEnum::VARCHAR,
			'endpoints'       => ColumnTypeEnum::SERIALIZED,
			'consumer_key'    => ColumnTypeEnum::CHAR,
			'consumer_secret' => ColumnTypeEnum::CHAR,
			'nonces'          => ColumnTypeEnum::LONGTEXT,
			'truncated_key'   => ColumnTypeEnum::CHAR,
			'last_access'     => ColumnTypeEnum::DATETIME
		);
	}
}
