<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Models;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class Generator extends AbstractDataModel {

	/**
	 * The primary key
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * The table name
	 * @var string
	 */
	protected $table = DatabaseTable::GENERATORS;


	/**
	 * The id
	 * @return int
	 */
	public function getId() {
		return $this->get( 'id' );
	}

	/**
	 * The name
	 * @return string
	 */
	public function getName() {
		return $this->get( 'name' );
	}

	/**
	 * The charset allowed
	 * @return string
	 */
	public function getCharset() {
		return $this->get( 'charset' );
	}


	/**
	 * The number of chunks
	 * @return int
	 */
	public function getChunks() {
		return $this->get( 'chunks' );
	}

	/**
	 * The chunk length
	 * @return int
	 */
	public function getChunkLength() {
		return $this->get( 'chunk_length' );
	}

	/**
	 * The limit of activations
	 * @return int
	 */
	public function getActivationsLimit() {
		return $this->get( 'activations_limit' );
	}

	/**
	 * The separator
	 * @return string
	 */
	public function getSeparator() {
		return $this->get( 'separator' );
	}

	/**
	 * The prefix
	 * @return string
	 */
	public function getPrefix() {
		return $this->get( 'prefix' );
	}

	/**
	 * The suffix
	 * @return string
	 */
	public function getSuffix() {
		return $this->get( 'prefix' );
	}

	/**
	 * The expires in, number of days.
	 * @return int
	 */
	public function getExpiresIn() {
		return $this->get( 'expires_in' );
	}

	/**
	 * The created at stamp
	 * @return string
	 */
	public function getCreatedAt() {
		return $this->get( 'created_at' );
	}

	/**
	 * The created by identifier
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
	 * The updated by identifier
	 * @return int
	 */
	public function getUpdatedBy() {
		return $this->get( 'updated_by' );
	}

}