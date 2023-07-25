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

class DLM_Helper_Generator {

	/**
	 * Creates a generator
	 *
	 * @param $args
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator|WP_Error
	 */
	public static function create( $args = [] ) {

		$params = wp_parse_args( $args, [
			'name'              => sprintf( 'Test Generator %d', mt_rand( 0, 100 ) ),
			'charset'           => 'ABCDEFGHUIJKLOP1234567890',
			'chunks'            => 4,
			'chunk_length'      => 4,
			'expires_in'        => 365,
			'activations_limit' => 2,
		] );

		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $service->create( $params );

	}

	/**
	 * Find a generator
	 *
	 * @param $id
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator|WP_Error
	 */
	public static function find( $id ) {

		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $service->find( $id );

	}

	/**
	 * Query generators
	 *
	 * @param $query
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel[]|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator[]|WP_Error
	 */
	public static function get( $query ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $service->get( $query );
	}


	/**
	 * Updates generator
	 *
	 * @param $id
	 * @param $args
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel[]|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator[]|WP_Error
	 */
	public static function update( $id, $args ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $service->update( $id, $args );
	}

	/**
	 * Deletes generator
	 *
	 * @param $id
	 *
	 * @return \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceModel[]|\IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator[]|WP_Error
	 */
	public static function delete( $id ) {
		$service = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();

		return $service->delete( $id );
	}

	/**
	 * Generates licenses
	 *
	 * @param $generator_id
	 * @param $product_id
	 * @param $max
	 *
	 * @return array|WP_Error
	 */
	public static function generate( $generator_id, $product_id, $max ) {
		$generator = is_object( $generator_id ) ? $generator_id : self::find( $generator_id );
		$product   = is_object( $product_id ) ? $product_id : wc_get_product( $product_id );

		$gService = new \IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService();
		$licenses = $gService->generateLicenses( $max, $generator, [], null, $product );

		$lService = new \IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService();
		$lService->saveGeneratedLicenseKeys( null, $product->get_id(), $licenses, \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::ACTIVE, $generator );

		return $licenses;

	}

}