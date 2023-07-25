<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2023  Darko Gjorgjijoski. All Rights Reserved.
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

namespace IdeoLogix\DigitalLicenseManager\Utils\Data;

use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;

/**
 * Class Meta
 * @deprecated 1.3.9
 * @package IdeoLogix\DigitalLicenseManager\Core\Services
 */
class Meta {

	/**
	 * Adds a new entry to the license meta table.
	 *
	 * @param int $licenseId License Key ID
	 * @param string $metaKey Meta key to add
	 * @param mixed $metaValue Meta value to add
	 *
	 * @return mixed|bool
	 */
	public static function addLicenseMeta( $licenseId, $metaKey, $metaValue ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::addMeta()' );

		$licensesService = new LicensesService();

		return $licensesService->addMeta( $licenseId, $metaKey, $metaValue );
	}

	/**
	 * Retrieves one or multiple license meta values
	 *
	 * @param int $licenseId License Key ID
	 * @param string $metaKey Meta key to search by
	 * @param bool $single Return a single or multiple rows (if found)
	 *
	 * @return mixed|mixed[]|bool
	 */
	public static function getLicenseMeta( $licenseId, $metaKey, $single = false ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::getMeta()' );

		$licensesService = new LicensesService();

		return $licensesService->getMeta( $licenseId, $metaKey, $single );
	}

	/**
	 * Updates existing license meta entries.
	 *
	 * @param int $licenseId
	 * @param string $metaKey
	 * @param mixed $metaValue
	 * @param mixed $previousValue
	 *
	 * @return bool
	 */
	public static function updateLicenseMeta( $licenseId, $metaKey, $metaValue, $previousValue = null ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::updateMeta()' );

		$licensesService = new LicensesService();

		return $licensesService->updateMeta( $licenseId, $metaKey, $metaValue, $previousValue );
	}

	/**
	 * Deletes one or multiple rows from the license meta table.
	 *
	 * @param int $licenseId
	 * @param string $metaKey
	 * @param mixed $metaValue
	 *
	 * @return bool
	 */
	public static function deleteLicenseMeta( $licenseId, $metaKey, $metaValue = null ) {

		_deprecated_function( __METHOD__, '1.3.9', 'Core\Services\LicensesService::deleteMeta()' );

		$licensesService = new LicensesService();

		return $licensesService->deleteMEta( $licenseId, $metaKey, $metaValue );
	}


}