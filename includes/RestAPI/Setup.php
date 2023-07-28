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

namespace IdeoLogix\DigitalLicenseManager\RestAPI;

use IdeoLogix\DigitalLicenseManager\RestAPI\Controllers\Generators;
use IdeoLogix\DigitalLicenseManager\RestAPI\Controllers\Licenses;

defined( 'ABSPATH' ) || exit;

/**
 * Class Setup
 * @package IdeoLogix\DigitalLicenseManager\RestAPI
 */
class Setup {

	/**
	 * Setup class constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register' ), 10 );
	}

	/**
	 * Initializes the plugin API controllers
	 */
	public function register() {

		if ( ! class_exists( '\WP_REST_Server' ) ) {
			return;
		}

		foreach ( self::getControllers() as $controller ) {
			( new $controller() )->register_routes();
		}
	}

	/**
	 * Returns the application endpoints
	 * @return mixed|null
	 */
	public static function getEndpoints() {
		return apply_filters( 'dlm_rest_endpoints', array(
			array(
				'id'         => '010',
				'name'       => 'v1/licenses',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '011',
				'name'       => 'v1/licenses/{license_key}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '012',
				'name'       => 'v1/licenses',
				'method'     => 'POST',
				'deprecated' => false,
			),
			array(
				'id'         => '013',
				'name'       => 'v1/licenses/{license_key}',
				'method'     => 'PUT',
				'deprecated' => false,
			),
			array(
				'id'         => '014',
				'name'       => 'v1/licenses/{license_key}',
				'method'     => 'DELETE',
				'deprecated' => false,
			),
			array(
				'id'         => '015',
				'name'       => 'v1/licenses/activate/{license_key}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '016',
				'name'       => 'v1/licenses/deactivate/{activation_token}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '017',
				'name'       => 'v1/licenses/validate/{activation_token}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '022',
				'name'       => 'v1/generators',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '023',
				'name'       => 'v1/generators/{id}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '024',
				'name'       => 'v1/generators',
				'method'     => 'POST',
				'deprecated' => false,
			),
			array(
				'id'         => '025',
				'name'       => 'v1/generators/{id}',
				'method'     => 'PUT',
				'deprecated' => false,
			),
			array(
				'id'         => '026',
				'name'       => 'v1/generators/{id}',
				'method'     => 'DELETE',
				'deprecated' => false,
			),
			array(
				'id'         => '027',
				'name'       => 'v1/generators/{id}/generate',
				'method'     => 'POST',
				'deprecated' => false,
			),
		) );
	}

	/**
	 * Return rest api routes
	 * @return mixed|void
	 */
	public static function getControllers() {

		$controllers = array(
			Licenses::class,
			Generators::class,
		);

		return apply_filters( 'dlm_rest_controllers', $controllers );
	}
}
