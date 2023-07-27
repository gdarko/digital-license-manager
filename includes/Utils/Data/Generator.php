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

namespace IdeoLogix\DigitalLicenseManager\Utils\Data;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\Generator as GeneratorModel;
use WP_Error;

/**
 * Generator CRUD
 * @deprecated 1.3.9
 * @package IdeoLogix\DigitalLicenseManager\Utils\Data
 */
class Generator {

	/**
	 * Find a single item from the database.
	 *
	 * @param mixed $id The license key to be deleted.
	 *
	 * @return AbstractResourceModel|GeneratorModel|\WP_Error
	 */
	public static function find( $id ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\GeneratorsService::find' );

		$instance = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $instance->find( $id );
	}

	/**
	 * Retrieves a single license from the database by ID
	 *
	 * @param $licenseId
	 *
	 * @return AbstractResourceModel|WP_Error
	 */
	public static function findById( $id ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\GeneratorsService::findById' );

		return self::find( $id );
	}

	/**
	 * Retrieves multiple items by a query array.
	 *
	 * @param array $query Key/value pairs with the generator table column names as keys
	 *
	 * @return AbstractResourceModel[]|GeneratorModel[]|WP_Error
	 */
	public static function get( $query = [] ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\GeneratorsService::get' );

		$instance = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $instance->get( $query );
	}

	/**
	 * Creates a new entry to the database
	 *
	 * @param array $data
	 *
	 * @return AbstractResourceModel|GeneratorModel|\WP_Error
	 */
	public static function create( $data = [] ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\GeneratorsService::create' );

		$instance = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $instance->create( $data );
	}

	/**
	 * Updates specific entry in the database
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return AbstractResourceModel|GeneratorModel|WP_Error
	 */
	public static function update( $id, $data = [] ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\GeneratorsService::update' );

		$instance = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $instance->update( $id, $data );
	}

	/**
	 * Deletes specific entry from the database
	 *
	 * @param int|int[] $id
	 *
	 * @return bool|WP_Error
	 */
	public static function delete( $id ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\GeneratorsService::delete' );

		$instance = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $instance->delete( $id );
	}

	/**
	 * Bulk create license keys, if possible for given parameters.
	 *
	 * @param int $amount Number of license keys to be generated
	 * @param GeneratorModel $generator Generator used for the license keys
	 * @param array $licenses Number of license keys to be generated
	 * @param \WC_Order|null $order
	 * @param \WC_Product|null $product
	 *
	 * @return array|WP_Error
	 */
	public static function generateLicenseKeys( $amount, $generator, $licenses = array(), $order = null, $product = null ) {
		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\GeneratorsService::generateLicenses' );

		$instance = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $instance->generateLicenses( $amount, $generator, $licenses, $order, $product );
	}
}
