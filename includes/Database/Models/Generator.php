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

class Generator extends AbstractDataModel {

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
	protected $table = DatabaseTable::GENERATORS;

	/**
	 * The casts
	 * @var string[]
	 */
	protected $casts = [
		'id'                => 'int',
		'chunks'            => 'int',
		'chunk_length'      => 'int',
		'activations_limit' => 'int',
		'expires_in'        => 'int',
	];

	/**
	 * The id
	 * @return int
	 */
	public function getId() {
		return $this->get( 'id' );
	}

	/**
	 * The name
	 * @return string
	 */
	public function getName() {
		return $this->get( 'name' );
	}

	/**
	 * The charset allowed
	 * @return string
	 */
	public function getCharset() {
		return $this->get( 'charset' );
	}


	/**
	 * The number of chunks
	 * @return int
	 */
	public function getChunks() {
		return $this->get( 'chunks' );
	}

	/**
	 * The chunk length
	 * @return int
	 */
	public function getChunkLength() {
		return $this->get( 'chunk_length' );
	}

	/**
	 * The limit of activations
	 * @return int
	 */
	public function getActivationsLimit() {
		return $this->get( 'activations_limit' );
	}

	/**
	 * The separator
	 * @return string
	 */
	public function getSeparator() {
		return $this->get( 'separator' );
	}

	/**
	 * The prefix
	 * @return string
	 */
	public function getPrefix() {
		return $this->get( 'prefix' );
	}

	/**
	 * The suffix
	 * @return string
	 */
	public function getSuffix() {
		return $this->get( 'suffix' );
	}

	/**
	 * The expires in, number of days.
	 * @return int
	 */
	public function getExpiresIn() {
		return $this->get( 'expires_in' );
	}

	/**
	 * The created at stamp
	 * @return string
	 */
	public function getCreatedAt() {
		return $this->get( 'created_at' );
	}

	/**
	 * The created by identifier
	 * @return int
	 */
	public function getCreatedBy() {
		return $this->get( 'created_by' );
	}

	/**
	 * The updated at stamp
	 * @return string
	 */
	public function getUpdatedAt() {
		return $this->get( 'updated_at' );
	}

	/**
	 * The updated by identifier
	 * @return int
	 */
	public function getUpdatedBy() {
		return $this->get( 'updated_by' );
	}

}