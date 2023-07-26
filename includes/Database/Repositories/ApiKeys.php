<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataRepository;
use IdeoLogix\DigitalLicenseManager\Database\Models\ApiKey;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class ApiKeys extends AbstractDataRepository {

	/**
	 * Initializes the repository
	 * @return void
	 */
	protected function init() {
		$this->primaryKey = 'id';
		$this->dataTable  = DatabaseTable::API_KEYS;
		$this->dataModel  = ApiKey::class;
	}

}