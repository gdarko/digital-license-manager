<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class ProductDownload extends AbstractDataModel {

	/**
	 * The primary key
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * The table name
	 * @var string
	 */
	protected $table = DatabaseTable::PRODUCT_DOWNLOADS;


	public function getId() {
		return $this->get( 'id' );
	}

	public function getLicenseId() {
		return $this->get( 'license_id' );
	}

	public function getActivationId() {
		return $this->get( 'activation_id' );
	}

	public function getSource() {
		return $this->get( 'source' );
	}

	public function getIpAddress() {
		return $this->get( 'ip_address' );
	}

	public function getUserAgent() {
		return $this->get( 'user_agent' );
	}

	public function getMetaData() {
		return $this->get_json( 'meta_data' );
	}

	public function getCreatedAt() {
		return $this->get( 'created_at' );
	}

	public function getUpdatedAt() {
		return $this->get( 'updated_at' );
	}

}