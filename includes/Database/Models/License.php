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

use DateTime;
use DateTimeZone;
use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;


class License extends AbstractDataModel {

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
	 * The appended attributes
	 * @var array
	 */
	protected $appends = [ 'times_activated', 'is_expired' ];

	/**
	 * The casts
	 * @var string[]
	 */
	protected $casts = [
		'id'                => 'int',
		'order_id'          => 'int',
		'product_id'        => 'int',
		'valid_for'         => 'int',
		'source'            => 'int',
		'status'            => 'int',
		'activations_limit' => 'int'
	];

	/**
	 * The table name
	 * @var string
	 */
	protected $table = DatabaseTable::LICENSES;

	/**
	 * The decyrpted key cached
	 * @var string
	 */
	protected $decrypted_key;

	/**
	 * The id
	 * @return int
	 */
	public function getId() {
		return $this->get( 'id' );
	}

	/**
	 * The order id
	 * @return int
	 */
	public function getOrderId() {
		return $this->get( 'order_id' );
	}

	/**
	 * The product id
	 * @return int
	 */
	public function getProductId() {
		return $this->get( 'product_id' );
	}

	/**
	 * The user id
	 * @return int
	 */
	public function getUserId() {
		return (int) $this->get( 'user_id' );
	}

	/**
	 * The license key
	 * @return string
	 */
	public function getLicenseKey() {
		return $this->get( 'license_key' );
	}

	/**
	 * The decrypted license key
	 * @return string|\WP_Error
	 */
	public function getDecryptedLicenseKey() {
		if ( is_null( $this->decrypted_key ) ) {
			$this->decrypted_key = CryptoHelper::decrypt( $this->getLicenseKey() );
		}

		return $this->decrypted_key;
	}

	/**
	 * The hash
	 * @return string
	 */
	public function getHash() {
		return $this->get( 'hash' );
	}

	/**
	 * The expires at stamp
	 * @return string
	 */
	public function getExpiresAt() {
		return $this->get( 'expires_at' );
	}

	/**
	 * The source
	 * @return int
	 */
	public function getSource() {
		return $this->get( 'source' );
	}

	/**
	 * The status
	 * @return int
	 */
	public function getStatus() {
		return $this->get( 'status' );
	}

	/**
	 * The number of days the license is valid after is sold from stock.
	 * This should be set before the licenses are sold from stock.
	 * @return int|null
	 */
	public function getValidFor() {
		return $this->get( 'valid_for' );
	}

	/**
	 * The activations limit
	 * @backcompat License Manager for WooCommerce
	 * @return int
	 */
	public function getTimesActivatedMax() {
		return $this->getActivationsLimit();
	}

	/**
	 * The activations limit
	 * @return int
	 */
	public function getActivationsLimit() {
		return $this->get( 'activations_limit' );
	}

	/**
	 * The created at stamp
	 * @return string
	 */
	public function getCreatedAt() {
		return $this->get( 'created_at' );
	}

	/**
	 * The created by
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
	 * The created by
	 * @return int
	 */
	public function getUpdatedBy() {
		return $this->get( 'updated_by' );
	}


	/**
	 * Is license expired?
	 * @return bool
	 */
	public function isExpired() {

		$expires_at = $this->getExpiresAt();

		if ( is_null( $expires_at ) | '0000-00-00 00:00:00' === $expires_at ) {
			return false;
		}

		try {
			$dateExpiresAt = new DateTime( $expires_at );
			$dateNow       = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		} catch ( \Exception $e ) {
			return false;
		}

		return $dateNow > $dateExpiresAt;
	}

	/**
	 * Is expired
	 * @return bool
	 */
	public function getIs_expiredAlias() {

		static $values = [];

		$id = $this->getId();

		if ( ! array_key_exists( $id, $values ) ) {
			$value         = $this->isExpired();
			$values[ $id ] = $value;
		}

		return $values[ $id ];
	}

	/**
	 * Times activated alias
	 * @return int|null
	 */
	public function getTimes_activatedAlias() {
		static $values = [];

		$id = $this->getId();

		if ( ! array_key_exists( $id, $values ) ) {
			$value         = $this->getTimesActivatedCount();
			$values[ $id ] = $value;
		}

		return $values[ $id ];
	}

	/**
	 * Returns the times activated
	 * @return int|null
	 */
	public function getTimesActivated() {
		return $this->getTimes_activatedAlias();
	}

	/**
	 * The times activated count
	 * @return int
	 */
	public function getTimesActivatedCount() {
		$params = $this->getActivationsQuery( [ 'active' => 1 ] );

		return LicenseActivations::instance()->count( $params );
	}

	/**
	 * Returns the activations
	 * @return array
	 */
	public function getActivations( $query = array() ) {
		$params = $this->getActivationsQuery( $query );

		return LicenseActivations::instance()->get( $params );
	}

	/**
	 * Returns the activations
	 * @return int
	 */
	public function getActivationsCount( $query = array() ) {
		$params = $this->getActivationsQuery( $query );

		return LicenseActivations::instance()->count( $params );
	}

	/**
	 * Returns Activations query
	 *
	 * @param $query
	 *
	 * @return int[]
	 */
	private function getActivationsQuery( $query ) {
		$params = array(
			'license_id' => $this->getId(),
		);
		if ( isset( $query['active'] ) && $query['active'] ) {
			$params['deactivated_at'] = null;
		}

		return $params;
	}

}