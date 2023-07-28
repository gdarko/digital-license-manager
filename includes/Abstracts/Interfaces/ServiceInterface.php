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

namespace IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use WP_Error;

interface ServiceInterface {

	/**
	 * Find a single item from the database.
	 *
	 * @param mixed $id
	 *
	 * @return AbstractResourceModel|\WP_Error
	 */
	public function find( $id );


	/**
	 * Retrieves single item from the database by ID
	 *
	 * @param $id
	 *
	 * @return AbstractResourceModel|WP_Error
	 */
	public function findById( $id );

	/**
	 * Retrieves multiple items by a query array.
	 *
	 * @param array $query
	 *
	 * @return AbstractResourceModel[]|WP_Error
	 */
	public function get( $query = array() );


	/**
	 * Creates a new entry to the database
	 *
	 * @param array $data
	 *
	 * @return AbstractResourceModel|\WP_Error
	 */
	public function create( $data = array() );


	/**
	 * Updates specific entry in the database
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return AbstractResourceModel|WP_Error
	 */
	public function update( $id, $data = array() );


	/**
	 * Deletes specific entry from the database
	 *
	 * @param int|int[] $id
	 *
	 * @return bool|WP_Error
	 */
	public function delete( $id );


}