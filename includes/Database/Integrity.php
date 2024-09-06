<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-present  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

namespace IdeoLogix\DigitalLicenseManager\Database;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractDataModel;
use IdeoLogix\DigitalLicenseManager\Abstracts\Singleton;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseMeta as LicenseMetaRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations as LicenseActivationsRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ProductDownload as ProductDownloadRepository;

defined( 'ABSPATH' ) || exit;

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