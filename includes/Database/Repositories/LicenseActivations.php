<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataRepository;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class LicenseActivations extends AbstractDataRepository {

	/**
	 * Initializes the repository
	 * @return void
	 */
	protected function init() {
		$this->primaryKey = 'id';
		$this->dataTable  = DatabaseTable::LICENSE_ACTIVATIONS;
		$this->dataModel  = LicenseActivation::class;
	}

}