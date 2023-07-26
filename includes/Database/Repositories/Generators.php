<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataRepository;
use IdeoLogix\DigitalLicenseManager\Database\Models\Generator;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class Generators extends AbstractDataRepository {

	/**
	 * Initializes the repository
	 * @return void
	 */
	protected function init() {
		$this->primaryKey = 'id';
		$this->dataTable  = DatabaseTable::GENERATORS;
		$this->dataModel  = Generator::class;
	}


}