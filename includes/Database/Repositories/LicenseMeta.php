<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataRepository;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseMeta as LicenseMetaModel;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

class LicenseMeta extends AbstractDataRepository {

	/**
	 * Initializes the repository
	 * @return void
	 */
	protected function init() {
		$this->primaryKey = 'meta_id';
		$this->dataTable  = DatabaseTable::LICENSE_META;
		$this->dataModel  = LicenseMetaModel::class;
	}

}