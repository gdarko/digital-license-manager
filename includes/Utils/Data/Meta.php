<?php

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