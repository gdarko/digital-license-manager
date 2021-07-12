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

		// Init related actions and filters.
		add_filter( 'dlm_rest_api_pre_response', array( $this, 'preResponse' ), 1, 3 );
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
	 * Allows developers to hook in and modify the response of any API route
	 * right before being sent out.
	 *
	 * @param string $method Contains the HTTP method which was used in the request
	 * @param string $route Contains the request endpoint name
	 * @param array $data Contains the response data
	 *
	 * @return array
	 */
	public function preResponse( $method, $route, $data ) {
		return $data;
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
