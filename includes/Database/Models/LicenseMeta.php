<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;

class LicenseMeta extends AbstractDataModel {

	/**
	 * The primary key
	 * @var string
	 */
	protected $primary_key = 'meta_id';

	/**
	 * The table name
	 * @var string
	 */
	protected $table = DatabaseTable::LICENSE_META;

	/**
	 * The meta id
	 * @return int
	 */
	public function getMetaId() {
		return $this->get( 'meta_id' );
	}

	/**
	 * The license id
	 * @return int
	 */
	public function getLicenseId() {
		return $this->get( 'license_id' );
	}

	/**
	 * The meta key
	 * @return string
	 */
	public function getMetaKey() {
		return $this->get( 'meta_key' );
	}

	/**
	 * The meta value
	 * @return mixed
	 */
	public function getMetaValue() {
		return JsonFormatter::decode( $this->get( 'meta_value' ), true );
	}

}