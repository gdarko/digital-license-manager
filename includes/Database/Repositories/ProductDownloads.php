<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataRepository;
use IdeoLogix\DigitalLicenseManager\Database\Models\ProductDownload;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Traits\Singleton;

class ProductDownloads extends AbstractDataRepository {

	use Singleton;

	/**
	 * Initializes the repository
	 * @return void
	 */
	protected function init() {
		$this->primaryKey = 'id';
		$this->dataTable  = DatabaseTable::PRODUCT_DOWNLOADS;
		$this->dataModel  = ProductDownload::class;
	}

}