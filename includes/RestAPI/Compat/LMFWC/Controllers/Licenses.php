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

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractRestController;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Class Licenses
 *
 * @package IdeoLogix\DigitalLicenseManager\RestAPI\V1
 *
 * @note
 *    This code is a part of a compatibility layer for License Manager for WooCommerce.
 *    Endpoint names use the same prefix as License Manager for WooCommerce, however
 *    under the hood, Digital License Manager's code is utilized.
 *
 *    This is only enabled manually with a filter and it's meant to be used for user
 *    that know what they are doing to help with their migration.
 *    More details at: https://docs.codeverve.com/digital-license-manager/migration/migrate-from-license-manager-for-woocommerce/
 *
 */
class Licenses extends AbstractRestController {

	/**
	 * @var string
	 */
	protected $namespace = 'lmfwc/v2';

	/**
	 * @var string
	 */
	protected $rest_base = '/licenses';

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var LicensesService
	 */
	protected $service;

	/**
	 * Licenses constructor.
	 */
	public function __construct() {
		$this->settings = (array) get_option( 'dlm_settings_general', array() );
		$this->service  = new LicensesService();
	}

	/**
	 * Register all the needed routes for this resource.
	 */
	public function register_routes() {
		/**
		 * GET licenses
		 *
		 * Retrieves all the available licenses from the database.
		 */
		register_rest_route(
			$this->namespace, $this->rest_base, array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get' ),
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
		 * GET licenses/{license_key}
		 *
		 * Retrieves a single licenses from the database.
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/(?P<license_key>[\w-]+)', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'show' ),
					'permission_callback' => array( $this, 'permissionCallback' ),
					'args'                => array(
						'license_key' => array(
							'description' => 'License Key',
							'type'        => 'string',
						)
					)
				)
			)
		);

		/**
		 * POST licenses
		 *
		 * Creates a new license in the database
		 */
		register_rest_route(
			$this->namespace, $this->rest_base, array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create' ),
					'permission_callback' => array( $this, 'permissionCallback' )
				)
			)
		);

		/**
		 * PUT licenses/{license_key}
		 *
		 * Updates an already existing license in the database
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/(?P<license_key>[\w-]+)', array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update' ),
					'permission_callback' => array( $this, 'permissionCallback' ),
					'args'                => array(
						'license_key' => array(
							'description' => 'License Key',
							'type'        => 'string',
						),
					),
				)
			)
		);

		/**
		 * GET licenses/activate/{license_key}
		 *
		 * Activates a license key
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/activate/(?P<license_key>[\w-]+)', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'activate' ),
					'permission_callback' => array( $this, 'permissionCallback' ),
					'args'                => array(
						'license_key' => array(
							'description' => 'License Key',
							'type'        => 'string',
						),
					),
				)
			)
		);

		/**
		 * GET licenses/deactivate/{activation_token}
		 *
		 * Deactivates a license key
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/deactivate/(?P<license_key>[\w-]+)', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'deactivate' ),
					'permission_callback' => array( $this, 'permissionCallback' ),
					'args'                => array(
						'license_key' => array(
							'description' => 'License Key',
							'type'        => 'string'
						)
					)
				)
			)
		);

		/**
		 * PUT licenses/validate/{activation_token}
		 *
		 * Validates a license key
		 */
		register_rest_route(
			$this->namespace, $this->rest_base . '/validate/(?P<license_key>[\w-]+)', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'validate' ),
					'permission_callback' => array( $this, 'permissionCallback' ),
					'args'                => array(
						'license_key' => array(
							'description' => 'License Key',
							'type'        => 'string',
						),
					),
				)
			)
		);
	}

	/**
	 * Callback for the GET licenses route. Retrieves all license keys from the database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get( $request ) {

		$isValid = $this->validateRequest( $request, '010', 'dlm_read_licenses' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$query = $this->prepareInput( $request->get_params() );

		$licenses = $this->service->get( $query );
		if ( is_wp_error( $licenses ) ) {
			return $this->maybeErrorResponse( $licenses );
		}

		$prepared = array();

		foreach ( $licenses as $license ) {
			$prepared[] = self::prepareLicense( $license );
		}

		return $this->response( true, $prepared, 200, 'v2/licenses' );
	}

	/**
	 * Callback for the GET licenses/{license_key} route. Retrieves a single license key from the database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function show( WP_REST_Request $request ) {

		$isValid = $this->validateRequest( $request, '011', 'dlm_read_licenses' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$licenseKey = sanitize_text_field( $request->get_param( 'license_key' ) );
		$license    = $this->service->find( $licenseKey );
		if ( is_wp_error( $license ) ) {
			return $this->maybeErrorResponse( $license );
		}
		$licenseData = self::prepareLicense( $license );

		return $this->response( true, $licenseData, 200, 'v2/licenses/{license_key}' );
	}

	/**
	 * Callback for the POST licenses route. Creates a new license key in the
	 * database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create( WP_REST_Request $request ) {

		$isValid = $this->validateRequest( $request, '012', 'dlm_create_licenses' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$body = $request->get_params();

		$orderId          = isset( $body['order_id'] ) ? absint( $body['order_id'] ) : null;
		$productId        = isset( $body['product_id'] ) ? absint( $body['product_id'] ) : null;
		$userId           = isset( $body['user_id'] ) ? absint( $body['user_id'] ) : null;
		$licenseKey       = isset( $body['license_key'] ) ? sanitize_text_field( $body['license_key'] ) : null;
		$expiresAt        = isset( $body['expires_at'] ) ? sanitize_text_field( $body['expires_at'] ) : null;
		$activationsLimit = isset( $body['times_activated_max'] ) ? absint( $body['times_activated_max'] ) : null;
		$status           = isset( $body['status'] ) ? sanitize_text_field( $body['status'] ) : null;

		if ( ! is_null( $status ) ) {
			$status = strtolower( $status );
			$status = isset( LicenseStatus::$values[ $status ] ) ? LicenseStatus::$values[ $status ] : null;
		}

		$license = $this->service->create( array(
			'license_key'       => $licenseKey,
			'order_id'          => $orderId,
			'product_id'        => $productId,
			'user_id'           => $userId,
			'expires_at'        => $expiresAt,
			'source'            => LicenseSource::API,
			'status'            => $status,
			'activations_limit' => $activationsLimit
		) );

		if ( is_wp_error( $license ) ) {
			return $this->maybeErrorResponse( $license );
		}

		$licenseData = self::prepareLicense( $license );

		return $this->response( true, $licenseData, 200, 'v2/licenses' );
	}

	/**
	 * Callback for the PUT licenses/{license_key} route. Updates an existing license key in the database.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update( WP_REST_Request $request ) {

		$isValid = $this->validateRequest( $request, '013', 'dlm_edit_licenses' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$urlParams = $request->get_url_params();

		$licenseKey = isset( $urlParams['license_key'] ) ? sanitize_text_field( $urlParams['license_key'] ) : '';

		if ( JsonFormatter::validate( $request->get_body() ) ) {
			$updateData = JsonFormatter::decode( $request->get_body(), true );
		} else {
			$updateData = $request->get_params();
		}

		if ( isset( $updateData['times_activated_max'] ) ) {
			$updateData['activations_limit'] = is_numeric( $updateData['times_activated_max'] ) ? absint( $updateData['times_activated_max'] ) : null;
			unset($updateData['times_activated_max']);
		}

		$status = isset( $updateData['status'] ) ? sanitize_text_field( $updateData['status'] ) : null;
		if ( ! is_null( $status ) ) {
			$status = strtolower( $status );
			$updateData['status'] = isset( LicenseStatus::$values[ $status ] ) ? LicenseStatus::$values[ $status ] : null;
		}

		$updatedLicense = $this->service->update( $licenseKey, $updateData );
		if ( is_wp_error( $updatedLicense ) ) {
			return $this->maybeErrorResponse( $updatedLicense );
		}

		$licenseData = self::prepareLicense( $updatedLicense );

		return $this->response( true, $licenseData, 200, 'v2/licenses/{license_key}' );
	}

	/**
	 * Callback for the GET licenses/activate/{license_key} route. This will activate a license key (if possible)
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function activate( WP_REST_Request $request ) {

		$isValid = $this->validateRequest( $request, '015', 'dlm_activate_licenses' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$licenseKey      = sanitize_text_field( $request->get_param( 'license_key' ) );
		$activationMeta  = $request->get_param( 'meta' );
		$activationLabel = $request->get_param( 'label' );

		$licenseActivation = $this->service->activate( $licenseKey, array(
			'label' => 'Legacy Activation',
		) );

		if ( is_wp_error( $licenseActivation ) ) {
			return $this->maybeErrorResponse( $licenseActivation );
		}

		// Refresh
		$license = $this->service->find( $licenseKey );

		return $this->response( true, self::prepareLicense($license), 200, 'v2/licenses/activate/{license_key}' );

	}

	/**
	 * Callback for the GET licenses/deactivate/{activation_token} route. This will deactivate the activation that was created before.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function deactivate( WP_REST_Request $request ) {

		$isValid = $this->validateRequest( $request, '016', 'dlm_deactivate_licenses' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}


		$license_key = sanitize_text_field( $request->get_param( 'license_key' ) );

		$license = $this->service->find( $license_key );

		if ( is_wp_error( $license ) ) {
			return $this->maybeErrorResponse( $license );
		}

		/**
		 * @var LicenseActivation[] $activations
		 */
		$activations = $license->getActivations( [ 'active' => 1 ] );
		if ( empty( $activations ) ) {
			return new WP_Error( 'data_error', sprintf( 'The license %s has not been activated so far.', $license_key ), array( 'status' => 404 ) );
		}
		$last = end( $activations );
		if(false !== $last) {
			$licenseActivation = $this->service->deactivate( $last->getToken() );
			if ( is_wp_error( $licenseActivation ) ) {
				return $this->maybeErrorResponse( $licenseActivation );
			}
		} else {
			return new WP_Error( 'data_error', sprintf( 'Unable to deactivate license: %s.', $license_key ), array( 'status' => 500 ) );
		}

		// Refresh
		$license = $this->service->find( $license_key );

		return $this->response( true, self::prepareLicense($license), 200, 'v2/licenses/deactivate/{activation_token}' );
	}

	/**
	 * Callback for the GET licenses/validate/{activation_token} route.
	 * This check and verify the activation status of a given license key.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function validate( WP_REST_Request $request ) {

		$isValid = $this->validateRequest( $request, '017', 'dlm_validate_licenses' );
		if ( is_wp_error( $isValid ) ) {
			return $isValid;
		}

		$urlParams = $request->get_url_params();

		if ( empty( $urlParams['license_key'] ) ) {
			return $this->responseError(
				'data_error',
				'Invalid license key.',
				array( 'status' => 404 )
			);
		}

		$service = new LicensesService();
		$license = $service->find( $urlParams['license_key'] );

		if ( is_wp_error( $license ) ) {
			return $this->maybeErrorResponse( $license );
		}

		$currentCount = $license->getActivationsCount(['active' => true]);
		$maxCount     = $license->getActivationsLimit();
		$remaining    = is_null( $maxCount ) || 0 === $maxCount || $maxCount <= $currentCount ? 0 : $maxCount - $currentCount;

		return $this->response( true, [
			'timesActivated'       => $currentCount,
			'timesActivatedMax'    => $maxCount,
			'remainingActivations' => $remaining,
		], 200, 'v2/licenses/validate/{activation_token}' );
	}

	/**
	 * Prepares license
	 *
	 * @param License $license
	 * @param bool $withActivations
	 *
	 * @return array
	 */
	public static function prepareLicense( $license, $withActivations = false ) {

		if ( empty( $license ) ) {
			return null;
		}

		$data = $license->toArray();

		return [
			'id'                => $data['id'],
			'orderId'           => !is_null( $data['order_id'] ) ? (int) $data['order_id'] : null,
			'productId'         => !is_null( $data['product_id'] ) ? (int) $data['product_id'] : null,
			'userId'            => !is_null( $data['user_id'] ) ? (int) $data['user_id'] : null,
			'licenseKey'        => $license->getDecryptedLicenseKey(),
			'expiresAt'         => $data['expires_at'],
			'validFor'          => (int) $data['valid_for'],
			'source'            => (int) $data['source'],
			'status'            => (int) $data['status'],
			'timesActivated'    => $license->getActivationsCount(),
			'timesActivatedMax' => $data['activations_limit'],
			'createdAt'         => $data['created_at'],
			'createdBy'         => $data['created_by'],
			'updatedAt'         => $data['updated_at'],
			'updatedBy'         => $data['updated_by'],
		];
	}

}