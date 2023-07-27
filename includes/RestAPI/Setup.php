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

		foreach ( $this->getControllers() as $controller ) {
			( new $controller() )->register_routes();
		}
	}

	/**
	 * Return rest api routes
	 * @return mixed|void
	 */
	public function getControllers() {

		$controllers = array(
			Licenses::class,
			Generators::class,
		);

		return apply_filters( 'dlm_rest_controllers', $controllers );
	}
}
