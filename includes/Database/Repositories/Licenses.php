<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataRepository;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class Licenses extends AbstractDataRepository {


	/**
	 * Initializes the repository
	 * @return void
	 */
	protected function init() {
		$this->primaryKey = 'id';
		$this->dataTable  = DatabaseTable::LICENSES;
		$this->dataModel  = License::class;
	}

}