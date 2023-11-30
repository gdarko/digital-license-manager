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

namespace IdeoLogix\DigitalLicenseManager\RestAPI\Compat\LMFWC\Controllers;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractRestController as DLM_REST_Controller;
use IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService;
use IdeoLogix\DigitalLicenseManager\Database\Models\Generator;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Generators as GeneratorsModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\StringHasher;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Class Generators
 * @package IdeoLogix\DigitalLicenseManager\RestAPI\V1
 */
class Generators extends DLM_REST_Controller {
	/**
	 * @var string
	 */
	protected $namespace = 'lmfwc/v2';

	/**
	 * @var string
	 */
	protected $rest_base = '/generators';

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var GeneratorsService
	 */
	protected $service;

	/**
	 * Generators constructor.
	 */
	public function __construct() {
		$this->service  = new GeneratorsService();
		$this->settings = (array) get_option( 'dlm_settings_general', array() );
	}

	/**
	 * Register all the needed routes for this resource.
	 */
	public function register_routes() {
		/**
		 * GET generators
		 *
		 * Retrieves all the available generators from the database.
		 */
		register_rest_route(
			$this->namespace, $this->rest_base, array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getGenerators' ),
					'permission_callback' => array( $this, 'permissionCallback' )
				)
			)
		);

		/**
		 * GET generators/{id}
		 *
		 * Retrieves a single generator from the database.
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/(?P<generator_id>[\w-]+)', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'getGenerator' ),
					'permission_callback' => array( $this, 'permissionCallback' ),
					'args'                => array(
						'generator_id' => array(
							'description' => 'Generator ID',
							'type'        => 'integer',
						),
					),
				)
			)
		);

		/**
		 * POST generators
		 *
		 * Creates a new generator in the database
		 */
		register_rest_route(
			$this->namespace, $this->rest_base, array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'createGenerator' ),
					'permission_callback' => array( $this, 'permissionCallback' ),
					'args'                => array(
						'per_page' => array(
							'description' => 'Items per page',
							'type'        => 'integer',
						),
						'page'     => array(
							'description' => 'The page number',
							'type'        => 'integer',
						)
					)
				)
			)
		);

		/**
		 * PUT generators/{id}
		 *
		 * Updates an already existing generator in the database
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/(?P<generator_id>[\w-]+)', array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'updateGenerator' ),
					'permission_callback' => array( $this, 'permissionCallback' ),
					'args'                => array(
						'license_key' => array(
							'description' => 'Generator ID',
							'type'        => 'integer',
						),
					),
				)
			)
		);
	}

	/**
	 * Callback for the GET generators route. Retrieves all generators from the database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function getGenerators( $request ) {
		$isValid = $this->validateRequest( $request, '022', 'dlm_read_generators' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$query = $this->prepareInput( $request->get_params() );

		$generators = $this->service->get( $query );
		if ( is_wp_error( $generators ) ) {
			return $this->maybeErrorResponse( $generators );
		}
		$response = array();

		/** @var Generator $generator */
		foreach ( $generators as $generator ) {
			$response[] = self::prepareGenerator( $generator );
		}

		return $this->response( true, $response, 200, 'v1/generators' );
	}

	/**
	 * Callback for the GET generators/{id} route. Retrieves a single generator from the database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function getGenerator( WP_REST_Request $request ) {
		$isValid = $this->validateRequest( $request, '023', 'dlm_read_generators' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$generatorId = absint( $request->get_param( 'generator_id' ) );
		$generator   = $this->service->find( $generatorId );

		if ( is_wp_error( $generator ) ) {
			return $this->maybeErrorResponse( $generator );
		}

		return $this->response( true, self::prepareGenerator( $generator ), 200, 'v1/generators/{id}' );
	}

	/**
	 * Callback for the POST generators route. Creates a new generator in the database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function createGenerator( WP_REST_Request $request ) {
		$isValid = $this->validateRequest( $request, '024', 'dlm_create_generators' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$body      = $request->get_params();
		$generator = $this->service->create( $body );

		if ( is_wp_error( $generator ) ) {
			return $this->maybeErrorResponse( $generator );
		}

		return $this->response( true, self::prepareGenerator( $generator ), 200, 'v1/generators' );
	}

	/**
	 * Callback for the PUT generators/{id} route. Updates an existing generator in the database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */

	public function updateGenerator( $request ) {
		$isValid = $this->validateRequest( $request, '025', 'dlm_edit_generators' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		// Set and sanitize the basic parameters to be used.
		$generatorId = absint( $request->get_param( 'generator_id' ) );
		if ( JsonFormatter::validate( $request->get_body() ) ) {
			$updateData = JsonFormatter::decode( $request->get_body(), true );
		} else {
			$updateData = $request->get_params();
		}

		$updatedGenerator = $this->service->update( $generatorId, $updateData );
		if ( is_wp_error( $updatedGenerator ) ) {
			return $this->maybeErrorResponse( $updatedGenerator );
		}

		return $this->response( true, self::prepareGenerator( $updatedGenerator ), 200, 'v1/generators/{id}' );
	}

	/**
	 * Prepare a single generator object
	 *
	 * @param Generator $generator
	 *
	 * @return array
	 */
	public static function prepareGenerator( $generator ) {
		return [
			'id'                => $generator->getId(),
			'name'              => $generator->getName(),
			'charset'           => $generator->getCharset(),
			'chunks'            => $generator->getCharset(),
			'chunkLength'       => $generator->getChunkLength(),
			'timesActivatedMax' => $generator->getActivationsLimit(),
			'separator'         => $generator->getSeparator(),
			'prefix'            => $generator->getPrefix(),
			'suffix'            => $generator->getSuffix(),
			'expiresIn'         => $generator->getExpiresIn(),
			'createdAt'         => $generator->getCreatedAt(),
			'createdBy'         => $generator->getCreatedBy(),
			'updatedAt'         => $generator->getUpdatedAt(),
			'updatedBy'         => $generator->getUpdatedBy(),
		];
	}
}
