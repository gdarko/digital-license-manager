<?php

namespace IdeoLogix\DigitalLicenseManager\Database;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Singleton;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseMeta as LicenseMetaRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations as LicenseActivationsRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ProductDownload as ProductDownloadRepository;


class Integrity extends Singleton {

	public function __construct() {
		add_action( 'dlm_object_deleted', array( $this, 'onDelete' ), 9, 3 );
	}

	/**
	 * Handles the on-delete event.
	 *
	 * @param AbstractDataModel $object
	 * @param $table
	 * @param $model
	 *
	 * @return void
	 */
	public function onDelete( $object, $table, $model ) {
		if ( $object instanceof License ) {
			LicenseMetaRepository::instance()->deleteBy( [ 'license_id' => $object->getId() ] );
			LicenseActivationsRepository::instance()->deleteBy( [ 'license_id' => $object->getId() ] );
			ProductDownloadRepository::instance()->deleteBy( [ 'license_id' => $object->getId() ] );
		}
	}
}