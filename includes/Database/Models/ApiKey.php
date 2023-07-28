<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;

class ApiKey extends AbstractDataModel {

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
	protected $table = DatabaseTable::API_KEYS;

	/**
	 * The id of the row
	 * @return int
	 */
	public function getId() {
		return $this->get( 'id' );
	}

	/**
	 * The user id that owns the api key
	 * @return int
	 */
	public function getUserId() {
		return $this->get( 'user_id' );
	}

	/**
	 * The descriiption of the api key
	 * @return string
	 */
	public function getDescription() {
		return $this->get( 'description' );
	}

	/**
	 * The permissions that define who can access
	 * @return string
	 */
	public function getPermissions() {
		return $this->get( 'permissions' );
	}

	/**
	 * The allowed endpoints
	 * @return mixed|string
	 */
	public function getEndpoints() {
		return $this->getJson( 'endpoints' );
	}

	/**
	 * The consumer access key
	 * @return string
	 */
	public function getConsumerKey() {
		return $this->get( 'consumer_key' );
	}

	/**
	 * The consumer secret key
	 * @return string
	 */
	public function getConsumerSecret() {
		return $this->get( 'consumer_secret' );
	}

	/**
	 * The nonce property
	 * @return string
	 */
	public function getNonces() {
		return $this->get( 'nonces' );
	}

	/**
	 * The truncated key
	 * @return string
	 */
	public function getTruncatedKey() {
		return $this->get( 'truncated_key' );
	}

	/**
	 * The last access stamp
	 * @return string
	 */
	public function getLastAccess() {
		return $this->get( 'last_access' );
	}

}