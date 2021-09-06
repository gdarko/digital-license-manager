<?php

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
	 * Setup class constructor.
	 */
	public function __construct() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( '\WP_REST_Server' ) ) {
			return;
		}

		// Init REST API routes.
		add_action( 'rest_api_init', array( $this, 'registerRoutes' ), 10 );
	}

	/**
	 * Initializes the plugin API controllers.
	 */
	public function registerRoutes() {
		foreach ( $this->getControllers() as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
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
