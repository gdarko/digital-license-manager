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
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Database\Models\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\License as LicenseModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation;
use WP_Error;

/**
 * Class License
 * @deprecated 1.3.9
 * @package IdeoLogix\DigitalLicenseManager\Utils\Data
 */
class License {

	/**
	 * Retrieves a single license from the database.
	 *
	 * @param string $licenseKey The license key to be deleted.
	 *
	 * @return LicenseModel|WP_Error
	 */
	public static function find( $licenseKey ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::find' );

		$instance = new LicensesService();

		return $instance->find( $licenseKey );

	}

	/**
	 * Retrieves a single license from the database by ID
	 *
	 * @param $licenseId
	 *
	 * @return LicenseModel|WP_Error
	 */
	public static function findById( $licenseId ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::findById' );

		$instance = new LicensesService();

		return $instance->findById( $licenseId );

	}

	/**
	 * Retrieves multiple license keys by a query array.
	 *
	 * @param array $query Key/value pairs with the license table column names as keys
	 *
	 * @return LicenseModel[]|WP_Error
	 */
	public static function get( $query = array() ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::get' );

		$instance = new LicensesService();

		return $instance->get( $query );
	}

	/**
	 * Adds a new license to the database.
	 *
	 * @param string $licenseKey The license key being added
	 * @param array $licenseData Key/value pairs with the license table column names as keys
	 *
	 * @return LicenseModel|\WP_Error
	 */
	public static function create( $licenseKey, $licenseData = array() ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::create' );

		$instance = new LicensesService();

		$licenseData['license_key'] = $licenseKey;

		return $instance->create( $licenseData );

	}

	/**
	 * Updates the specified license.
	 *
	 * @param string $licenseKey The license key being updated.
	 * @param array $licenseData Key/value pairs of the updated data.
	 *
	 * @return LicenseModel|WP_Error
	 */
	public static function update( $licenseKey, $licenseData ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::update' );

		$instance = new LicensesService();

		return $instance->update( $licenseKey, $licenseData );

	}

	/**
	 * Deletes the specified license.
	 *
	 * @param string $licenseKey The license key to be deleted.
	 *
	 * @return bool|WP_Error
	 */
	public static function delete( $licenseKey ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::delete' );

		$instance = new LicensesService();

		return $instance->delete( $licenseKey );

	}

	/**
	 * Activates license and returns the activation data.
	 *
	 * @param string $licenseKey The license key to be activated.
	 * @param array $params
	 *
	 * @return LicenseActivation|WP_Error
	 */
	public static function activate( $licenseKey, $params ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::activate' );

		$instance = new LicensesService();

		return $instance->activate( $licenseKey, $params );

	}

	/**
	 * Reactivate license
	 *
	 * @param $activationToken
	 * @param null $licenseKey
	 *
	 * @return bool|AbstractResourceModel|WP_Error
	 */
	public static function reactivate( $activationToken, $licenseKey = null ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::reactivate' );

		$instance = new LicensesService();

		return $instance->reactivate( $activationToken, $licenseKey );

	}

	/**
	 * Deactivates license in the database
	 *
	 * @param $activationToken
	 *
	 * @return bool|AbstractResourceModel|WP_Error
	 */
	public static function deactivate( $activationToken ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::deactivate' );

		$instance = new LicensesService();

		return $instance->deactivate( $activationToken );

	}

	/**
	 * Checks if the license has an expiry date and if it has expired already.
	 *
	 * @param LicenseModel $license
	 *
	 * @return false|WP_Error
	 */
	public static function hasLicenseExpired( $license ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::hasLicenseExpired' );

		$instance = new LicensesService();

		return $instance->hasLicenseExpired( $license );

	}

	/**
	 * Checks if the license is disabled.
	 *
	 * @param LicenseModel $license
	 *
	 * @return false|WP_Error
	 */
	public static function isLicenseDisabled( $license ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::isLicenseDisabled' );

		$instance = new LicensesService();

		return $instance->isLicenseDisabled( $license );

	}

	/**
	 * Checks if a license key already exists inside the database table.
	 *
	 * @param string $licenseKey
	 * @param null|int $licenseKeyId
	 *
	 * @return bool
	 */
	public static function isKeyDuplicate( $licenseKey, $licenseKeyId = null ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::isKeyDuplicate' );

		$instance = new LicensesService();

		return $instance->isKeyDuplicate( $licenseKey, $licenseKeyId );

	}

	/**
	 * Imports an array of un-encrypted license keys.
	 *
	 * @param array $licenseKeys License keys to be stored
	 * @param int $status License key status
	 * @param int $orderId WooCommerce Order ID
	 * @param int $productId WooCommerce Product ID
	 * @param int $userId WordPress User ID
	 * @param int $validFor Validity period (in days)
	 * @param int $activationsLimit Maximum activation count
	 *
	 * @return array|WP_Error
	 */
	public static function saveImportedLicenseKeys( $licenseKeys, $status, $orderId, $productId, $userId, $validFor, $activationsLimit ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::saveImportedLicenseKeys' );

		$instance = new LicensesService();

		return $instance->saveImportedLicenseKeys( $licenseKeys, $status, $orderId, $productId, $userId, $validFor, $activationsLimit );

	}

	/**
	 * Save the license keys for a given product to the database.
	 *
	 * @param int $orderId WooCommerce Order ID
	 * @param int $productId WooCommerce Product ID
	 * @param string[] $licenseKeys License keys to be stored
	 * @param int $status License key status
	 * @param GeneratorResourceModel $generator
	 * @param int $validFor
	 *
	 * @return array|bool|WP_Error
	 */
	public static function saveGeneratedLicenseKeys( $orderId, $productId, $licenseKeys, $status, $generator, $validFor = null, $activationsLimit = null ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::saveGeneratedLicenseKeys' );

		$instance = new LicensesService();

		return $instance->saveGeneratedLicenseKeys( $orderId, $productId, $licenseKeys, $status, $generator, $validFor, $activationsLimit );
	}

	/**
	 * Queries available licenses for selling from stock
	 *
	 * @param $product
	 * @param $params
	 *
	 * @return bool|AbstractResourceModel[]
	 */
	public static function getStockLicensesQuery( $product, $params = [] ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::getStockLicensesQuery' );

		$instance = new LicensesService();

		return $instance->isKeyDuplicate( $product, $params );

	}

	/**
	 * Returns a count for licenses available in stock
	 *
	 * @param $product
	 * @param $params
	 *
	 * @return false|int
	 */
	public static function getLicensesStockCount( $product, $params = [] ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::getLicensesStockCount' );

		$instance = new LicensesService();

		return $instance->getLicensesStockCount( $product, $params );

	}

	/**
	 * Queries licenses from available stock
	 *
	 * @param $product
	 * @param $params
	 *
	 * @return bool|AbstractResourceModel[]
	 */
	public static function getLicensesFromStock( $product, $amount = - 1, $params = [] ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::getLicensesFromStock' );

		$instance = new LicensesService();

		return $instance->getLicensesFromStock( $product, $amount, $params );

	}

	/**
	 * Assign licenses from stock
	 *
	 * @param $product
	 * @param $order
	 * @param $amount
	 *
	 * @return LicenseModel[]|WP_Error
	 */
	public static function assignLicensesFromStock( $product, $order, $amount, $activationsLimit = null ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::assignLicensesFromStock' );

		$instance = new LicensesService();

		return $instance->assignLicensesFromStock( $product, $order, $amount, $activationsLimit );

	}

	/**
	 * Mark imported license keys as sold
	 *
	 * @param LicenseModel[] $licenses License key resource models
	 * @param int $orderId WooCommerce Order ID
	 * @param int $amount Amount to be marked as sold
	 *
	 * @return bool|WP_Error
	 * @deprecated 1.3.5
	 *
	 */
	public static function sellImportedLicenseKeys( $licenses, $orderId, $amount ) {

		$instance = new LicensesService();

		return $instance->sellImportedLicenseKeys( $licenses, $orderId, $amount );

	}

	/**
	 * Check the activations limit.
	 *
	 * @param LicenseModel $license
	 *
	 * @return bool|WP_Error
	 */
	private static function validateActivationLimit( $license, $licenseKey = null ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::validateActivationLimit' );

		$instance = new LicensesService();

		return $instance->validateActivationLimit( $license, $licenseKey );
	}

	/**
	 * Generates activation token
	 *
	 * @param $licenseKey
	 *
	 * @return string|null
	 */
	public static function generateActivationToken( $licenseKey ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::validateActivationLimit' );

		$instance = new LicensesService();

		return $instance->generateActivationToken( $licenseKey );

	}
}