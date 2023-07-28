<?php

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
	 * The table name
	 * @var string
	 */
	protected $table = DatabaseTable::LICENSES;

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
		return $this->get( 'user_id' );
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
		static $decrypted = null;
		if ( is_null( $decrypted ) ) {
			$decrypted = CryptoHelper::decrypt( $this->getLicenseKey() );
		}

		return $decrypted;
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
	 * Times activated alias
	 * @return int|null
	 */
	public function getTimesActivatedAlias() {
		static $total = null;

		if ( is_null( $total ) ) {
			$total = $this->getTimesActivatedCount();
		}

		return $total;
	}

	/**
	 * Returns the times activated
	 * @return int|null
	 */
	public function getTimesActivated() {
		return $this->getTimesActivatedAlias();
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