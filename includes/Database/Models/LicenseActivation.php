<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class LicenseActivation extends AbstractDataModel {

	/**
	 * The primary key
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * The table name
	 * @var string
	 */
	protected $table = DatabaseTable::LICENSE_ACTIVATIONS;

	/**
	 * The id of the activation
	 * @return int
	 */
	public function getId() {
		return $this->get( 'id' );
	}

	/**
	 * The id of the license
	 * @return int
	 */
	public function getLicenseId() {
		return $this->get( 'license_id' );
	}

	/**
	 * The id of the license
	 * @return int
	 */
	public function getLicense() {
		static $model;
		if ( is_null( $model ) ) {
			$model = Licenses::instance()->find( $this->get( 'license_id' ) );
		}

		return $model;
	}

	/**
	 * The label of the activation
	 * @return string
	 */
	public function getLabel() {
		return $this->get( 'label' );
	}

	/**
	 * The source of the integration
	 * @return int
	 */
	public function getSource() {
		return $this->get( 'source' );
	}

	/**
	 * The token of the integration
	 * @return string
	 */
	public function getToken() {
		return $this->get( 'token' );
	}

	/**
	 * The ip address of the user that acitvated
	 * @return string
	 */
	public function getIpAddress() {
		return $this->get( 'ip_address' );
	}

	/**
	 * The user agent of the user that activated
	 * @return string
	 */
	public function getUserAgent() {
		return $this->get( 'user_agent' );
	}

	/**
	 *  The metadata of the user that activated
	 * @return array|mixed
	 */
	public function getMetaData() {
		return $this->get_json('meta_data', true);
	}

	/**
	 * The created at stamp
	 * @return mixed|null
	 */
	public function getCreatedAt() {
		return $this->get( 'created_at' );
	}

	/**
	 * The updated at stamp
	 * @return mixed|null
	 */
	public function getUpdatedAt() {
		return $this->get( 'updated_at' );
	}

	/**
	 * The deactivated at stamp.
	 * Also tells if the activation is no longer active (disabled).
	 * @return mixed|null
	 */
	public function getDeactivatedAt() {
		return $this->get( 'deactivated_at' );
	}

}