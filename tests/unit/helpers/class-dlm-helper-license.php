<?php

class DLM_Helper_License {


	/**
	 * Creates a generator
	 *
	 * @param $args
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator|WP_Error
	 */
	public static function create( $args = [] ) {

		// TODO...

	}

	/**
	 * Find a license by key
	 *
	 * @param $id
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License|WP_Error
	 */
	public static function find( $key ) {

		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->find( $key );

	}

	/**
	 * Find a license by id
	 *
	 * @param $id
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License|WP_Error
	 */
	public static function findById( $id ) {

		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->findById( $id );

	}

	/**
	 * Query licenses
	 *
	 * @param $query
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel[]|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License[]|WP_Error
	 */
	public static function get( $query ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->get( $query );
	}


	/**
	 * Updates license
	 *
	 * @param $id
	 * @param $args
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License|WP_Error
	 */
	public static function update( $id, $args ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->update( $id, $args );
	}

	/**
	 * Deletes license
	 *
	 * @param $id
	 *
	 * @return WP_Error|bool
	 */
	public static function delete( $id ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();

		return $service->delete( $id );
	}

}