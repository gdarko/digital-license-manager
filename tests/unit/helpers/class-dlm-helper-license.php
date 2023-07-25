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

class DLM_Helper_License {


	/**
	 * Creates a generator
	 *
	 * @param $args
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator|WP_Error
	 */
	public static function create( $args = [] ) {

		// TODO...

	}

	/**
	 * Find a license by key
	 *
	 * @param $id
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License|WP_Error
	 */
	public static function find( $key ) {

		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->find( $key );

	}

	/**
	 * Find a license by id
	 *
	 * @param $id
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License|WP_Error
	 */
	public static function findById( $id ) {

		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->findById( $id );

	}

	/**
	 * Query licenses
	 *
	 * @param $query
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel[]|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License[]|WP_Error
	 */
	public static function get( $query ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->get( $query );
	}


	/**
	 * Updates license
	 *
	 * @param $id
	 * @param $args
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License|WP_Error
	 */
	public static function update( $id, $args ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->update( $id, $args );
	}

	/**
	 * Deletes license
	 *
	 * @param $id
	 *
	 * @return WP_Error|bool
	 */
	public static function delete( $id ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->delete( $id );
	}

}