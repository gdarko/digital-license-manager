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

namespace IdeoLogix\DigitalLicenseManager\RestAPI\Controllers;

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
	protected $namespace = 'dlm/v1';

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
		$this->service = new GeneratorsService();
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
						'page' => array(
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

		/**
		 * DELETE generators/{id}
		 *
		 * Updates an already existing generator in the database
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/(?P<generator_id>[\w-]+)', array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'deleteGenerator' ),
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
		 * PUT generators/{id}/generate
		 *
		 * Generates license keys using a generator.
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/(?P<generator_id>[\w-]+)/generate', array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'generateLicenseKeys' ),
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

		$query = $request->get_params();

		$generators = $this->service->get( $query );
		if ( is_wp_error( $generators ) ) {
			return $this->maybeErrorResponse( $generators );
		}
		$response = array();

		/** @var Generator $generator */
		foreach ( $generators as $generator ) {
			$response[] = $generator->toArray();
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

		return $this->response( true, $generator->toArray(), 200, 'v1/generators/{id}' );
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

		return $this->response( true, $generator->toArray(), 200, 'v1/generators' );
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

		return $this->response( true, $updatedGenerator->toArray(), 200, 'v1/generators/{id}' );
	}

	/**
	 * Callback for the DELETE generators/{id} route. Deletes an existing generator in the database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function deleteGenerator( WP_REST_Request $request ) {
		$isValid = $this->validateRequest( $request, '026', 'dlm_delete_generators' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$generatorId = absint( $request->get_param( 'generator_id' ) );
		$deleted     = $this->service->delete( $generatorId );
		if ( is_wp_error( $deleted ) ) {
			return $this->maybeErrorResponse( $deleted );
		}

		return $this->response( true, [], 200, 'v1/generators/{id}' );
	}

	/**
	 * Callback for the POST generators/{id}/generate route that generates licenses.
	 * using a generator with a save option.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function generateLicenseKeys( WP_REST_Request $request ) {
		$isValid = $this->validateRequest( $request, '027', 'dlm_create_licenses' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$generatorId = null;

		// Set and sanitize the basic parameters to be used.
		if ( $request->get_param( 'generator_id' ) ) {
			$generatorId = absint( $request->get_param( 'generator_id' ) );
		}

		if ( JsonFormatter::validate( $request->get_body() ) ) {
			$body = JsonFormatter::decode( $request->get_body(), true );
		} else {
			$body = $request->get_params();
		}

		// Validate basic parameters
		if ( ! $generatorId ) {
			return $this->responseError(
				'data_error',
				__( 'The Generator ID is missing from the request.', 'digital-license-manager' ),
				array( 'status' => 404 )
			);
		}

		if ( ! $body ) {
			return $this->responseError(
				'data_error',
				__( 'No parameters were provided.', 'digital-license-manager' ),
				array( 'status' => 404 )
			);
		}

		$save       = isset( $body['save']) ? (bool) $body['save'] : 0;
		$statusEnum = sanitize_text_field( $body['status'] );
		$status     = ! empty( $statusEnum ) ? $this->getLicenseStatus( $statusEnum ) : $this->getLicenseStatus( 'INACTIVE' );

		if ( $save ) {

			$orderId   = null;
			$productId = null;
			$userId    = null;

			if ( function_exists( 'wc_get_order' ) ) {
				if ( isset( $body['order_id'] ) ) {
					$orderId = (int) $body['order_id'];

					if ( ! wc_get_order( $orderId ) ) {
						return $this->responseError(
							'data_error',
							__( 'The order does not exist.', 'digital-license-manager' ),
							array( 'status' => 404 )
						);
					}
				}
			}

			if ( function_exists( 'wc_get_product' ) ) {
				if ( isset( $body['product_id'] ) ) {
					$productId = (int) $body['product_id'];

					if ( ! wc_get_product( $productId ) ) {
						return $this->responseError(
							'data_error',
							__( 'The product does not exist.', 'digital-license-manager' ),
							array( 'status' => 404 )
						);
					}
				}
			}

			if ( isset( $body['user_id'] ) ) {
				$userId = (int) $body['user_id'];

				if ( ! get_user_by( 'ID', $userId ) ) {
					return $this->responseError(
						'data_error',
						__( 'The user does not exist.', 'digital-license-manager' ),
						array( 'status' => 404 )
					);
				}
			}
		}

		/** @var Generator $generator */
		$generator = GeneratorsModel::instance()->find( $generatorId );

		if ( ! $generator ) {
			return $this->responseError(
				'data_error',
				sprintf( __( 'Generator with ID: %d could not be found.', 'digital-license-manager' ), $generatorId ),
				array( 'status' => 404 )
			);
		}

		$amount = null;

		if ( isset( $body['amount'] ) ) {
			$amount = (int) $body['amount'];
		}

		if ( ! $amount || ! is_int( $amount ) ) {
			return $this->responseError(
				'data_error',
				__( 'Invalid amount', 'digital-license-manager' ),
				array( 'status' => 404 )
			);
		}

		$licenses = $this->service->generateLicenses( $amount, $generator );

		if ( is_wp_error( $licenses ) ) {
			return $this->maybeErrorResponse( $licenses );
		}


		if ( $save ) {
			foreach ( $licenses as $licenseKey ) {

				$expiresAt = null;
				if ( is_numeric( $generator->getExpiresIn() ) && $generator->getExpiresIn() > 0 ) {
					$expiresAt = DateFormatter::addDaysInFuture( $generator->getExpiresIn(), 'now', 'Y-m-d H:i:s' );
				}

				$encrypted = CryptoHelper::encrypt( $licenseKey );
				if ( is_wp_error( $encrypted ) ) {
					return $this->maybeErrorResponse( $encrypted );
				}
				$hashed = StringHasher::license( $licenseKey );

				$data = array(
					'license_key'       => $encrypted,
					'hash'              => $hashed,
					'expires_at'        => $expiresAt,
					'source'            => LicenseSource::API,
					'status'            => $status,
					'activations_limit' => $generator->getActivationsLimit()
				);

				if ( $orderId !== null ) {
					$data['order_id'] = $orderId;
				}

				if ( $productId !== null ) {
					$data['product_id'] = $productId;
				}

				if ( $userId !== null ) {
					$data['user_id'] = $userId;
				}

				Licenses::instance()->insert( $data );
			}
		}

		return $this->response( true, $licenses, 200, 'v1/generators/{id}/generate' );
	}
}
