<?php

namespace IdeoLogix\DigitalLicenseManager\RestAPI\Controllers;

use IdeoLogix\DigitalLicenseManager\Utils\Crypto;
use IdeoLogix\DigitalLicenseManager\Utils\Hash;
use IdeoLogix\DigitalLicenseManager\Utils\Data\Generator as GeneratorUtil;
use IdeoLogix\DigitalLicenseManager\Abstracts\RestController as DLM_REST_Controller;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;

use IdeoLogix\DigitalLicenseManager\Utils\Moment;
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
	 * Generators constructor.
	 */
	public function __construct() {
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
					'permission_callback' => array( $this, 'permissionCallback' )
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
	 * @return WP_REST_Response|WP_Error
	 */
	public function getGenerators() {
		if ( ! $this->isRouteEnabled( $this->settings, '017' ) ) {
			return $this->routeDisabledError();
		}

		if ( ! $this->capabilityCheck( 'dlm_read_generators' ) ) {
			return $this->responseError(
				'cannot_view',
				__( 'Sorry, you cannot list resources.', 'digital-license-manager' ),
				array(
					'status' => $this->authorizationRequiredCode()
				)
			);
		}

		$generators = GeneratorUtil::get();
		if ( is_wp_error( $generators ) ) {
			return $this->maybeErrorResponse( $generators );
		}
		$response = array();

		/** @var GeneratorResourceModel $generator */
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
		if ( ! $this->isRouteEnabled( $this->settings, '018' ) ) {
			return $this->routeDisabledError();
		}

		if ( ! $this->capabilityCheck( 'dlm_read_generators' ) ) {
			return $this->responseError(
				'cannot_view',
				__( 'Sorry, you cannot view this resource.', 'digital-license-manager' ),
				array(
					'status' => $this->authorizationRequiredCode()
				)
			);
		}

		$generatorId = absint( $request->get_param( 'generator_id' ) );
		$generator   = GeneratorUtil::find( $generatorId );

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
		if ( ! $this->isRouteEnabled( $this->settings, '019' ) ) {
			return $this->routeDisabledError();
		}

		if ( ! $this->capabilityCheck( 'dlm_create_generators' ) ) {
			return $this->responseError(
				'cannot_create',
				__( 'Sorry, you are not allowed to create resources.', 'digital-license-manager' ),
				array(
					'status' => $this->authorizationRequiredCode()
				)
			);
		}

		$body      = $request->get_params();
		$generator = GeneratorUtil::create( $body );

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

		if ( ! $this->isRouteEnabled( $this->settings, '020' ) ) {
			return $this->routeDisabledError();
		}

		if ( ! $this->capabilityCheck( 'dlm_edit_generators' ) ) {
			return $this->responseError(
				'cannot_edit',
				__( 'Sorry, you are not allowed to edit resources.', 'digital-license-manager' ),
				array(
					'status' => $this->authorizationRequiredCode()
				)
			);
		}

		// Set and sanitize the basic parameters to be used.
		$generatorId = absint( $request->get_param( 'generator_id' ) );
		if ( $this->isJson( $request->get_body() ) ) {
			$updateData = json_decode( $request->get_body() );
		} else {
			$updateData = $request->get_params();
		}

		$updatedGenerator = GeneratorUtil::update( $generatorId, $updateData );
		if ( is_wp_error( $updatedGenerator ) ) {
			return $this->maybeErrorResponse( $updatedGenerator );
		}

		return $this->response( true, $updatedGenerator->toArray(), 200, 'v1/generators/{id}' );
	}

	/**
	 * Callback for the POST generators/{id}/generate route. Creates licenses
	 * using a generator with a save option.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function generateLicenseKeys( WP_REST_Request $request ) {

		if ( ! $this->isRouteEnabled( $this->settings, '021' ) ) {
			return $this->routeDisabledError();
		}

		if ( ! $this->capabilityCheck( 'dlm_create_licenses' ) ) {
			return $this->responseError(
				'cannot_create',
				__( 'Sorry, you are not allowed to create resources.', 'digital-license-manager' ),
				array( 'status' => 404 )
			);
		}

		$generatorId = null;

		// Set and sanitize the basic parameters to be used.
		if ( $request->get_param( 'generator_id' ) ) {
			$generatorId = absint( $request->get_param( 'generator_id' ) );
		}

		if ( $this->isJson( $request->get_body() ) ) {
			$body = json_decode( $request->get_body(), true );
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

		$save       = (bool) $body['save'];
		$statusEnum = sanitize_text_field( $body['status'] );
		$status     = ! empty( $statusEnum ) ? $this->getLicenseStatus( $statusEnum ) : 'inactive';

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

		/** @var GeneratorResourceModel $generator */
		$generator = GeneratorResourceRepository::instance()->find( $generatorId );

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

		/** @var string[] $licenses */
		$licenses = GeneratorUtil::generateLicenseKeys( $amount, $generator );

		if ( is_wp_error( $licenses ) ) {
			return $this->maybeErrorResponse( $licenses );
		}


		if ( $save ) {
			foreach ( $licenses as $licenseKey ) {

				$expiresAt = null;
				if ( is_numeric( $generator->getExpiresIn() ) && $generator->getExpiresIn() > 0 ) {
					$expiresAt = Moment::addDaysInFuture( $generator->getExpiresIn(), 'Y-m-d H:i:s' );
				}

				$encrypted = Crypto::encrypt( $licenseKey );
				if ( is_wp_error( $encrypted ) ) {
					return $this->maybeErrorResponse( $encrypted );
				}
				$hashed = Hash::license( $licenseKey );

				$data = array(
					'license_key'       => $encrypted,
					'hash'              => $hashed,
					'valid_for'         => $generator->getExpiresIn(),
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

				LicenseResourceRepository::instance()->insert( $data );
			}
		}

		return $this->response( true, $licenses, 200, 'v1/generators/{id}/generate' );
	}
}
