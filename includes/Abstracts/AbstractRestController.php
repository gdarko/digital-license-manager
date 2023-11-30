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

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

use IdeoLogix\DigitalLicenseManager\Database\Models\ApiKey;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\ApiKeys;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\StringHasher;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;


abstract class AbstractRestController extends WP_REST_Controller {

	/**
	 * List of possible errors during the validation
	 * @var \string[][]
	 */
	protected $errors = [
		'route_disabled' => [
			'code'    => 'dlm_rest_route_disabled_error',
			'message' => 'This route is disabled via the plugin settings.',
		]
	];

	/**
	 * Returns standardized rest response
	 *
	 * @param $success
	 * @param $data
	 * @param $code
	 * @param $route
	 *
	 * Code inspired by "License Manager for WooCommerce" plugin
	 * @copyright  2019-2022  Drazen Bebic
	 * @copyright  2022-2023 WPExperts.io
	 * @copyright  2020-2023 Darko Gjorgjijoski
	 *
	 * @return WP_REST_Response
	 */
	protected function response( $success, $data, $code = 200, $route = '' ) {
		return new WP_REST_Response( array( 'success' => $success, 'data' => apply_filters( 'dlm_rest_api_pre_response', $data, $_SERVER['REQUEST_METHOD'], $route ) ), $code );
	}

	/**
	 * Returns the error response based on WP_Error object.
	 *
	 * @param WP_Error $error
	 *
	 * @return WP_Error
	 */
	protected function maybeErrorResponse( $error ) {
		if ( ! is_wp_error( $error ) ) {
			return $error;
		}

		return $this->responseError( $error->get_error_code(), $error->get_error_message(), $error->get_error_data() );
	}


	/**
	 * Respond with specific error message
	 *
	 * @param $code
	 * @param $message
	 * @param array $data
	 *
	 * @return WP_Error
	 */
	public function responseError( $code, $message, $data = array() ) {
		return self::_responseError( $code, $message, $data );
	}

	/**
	 * Respond with specific error message
	 *
	 * @param $code
	 * @param $message
	 * @param array $data
	 *
	 * @return WP_Error
	 */
	public static function _responseError( $code, $message, $data = array() ) {
		$prefix = apply_filters( 'dlm_rest_code_prefix', '' );
		$code   = str_replace( sprintf( '%s', $prefix ), '', $code );

		return new WP_Error( sprintf( '%s%s', $prefix, $code ), $message, $data );
	}

	/**
	 * Returns true if specified route is enabled
	 *
	 * @param $settings
	 * @param $routeId
	 *
	 * @return bool
	 */
	protected function isRouteEnabled( $settings, $routeId ) {
		$credentials = self::getCredentials();
		$consumerKey = isset( $credentials['consumer_key'] ) ? $credentials['consumer_key'] : null;
		if ( ! $consumerKey ) {
			return false;
		}
		$apiKey   = self::getCurrentConsumer( $consumerKey );
		$endpints = $apiKey ? $apiKey->getEndpoints() : [];

		return isset( $endpints[ $routeId ] ) && ! empty( $endpints[ $routeId ] );
	}

	/**
	 * Returns status integer based on input
	 *
	 * @depreacted 1.5.0
	 *
	 * @param $name
	 *
	 * @return int
	 */
	protected function getLicenseStatus( $name ) {
		return LicenseStatus::inputToStatus( $name );
	}

	/**
	 * Permission callback for the REST endpoints
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function permissionCallback( $request ) {

		$request = apply_filters( 'dlm_rest_permission_callback', $request );

		return is_wp_error( $request ) ? $request : true;
	}

	/**
	 * Checks if the current user has permission to perform the request.
	 *
	 * @param string $cap Capability slug
	 *
	 * @return bool
	 */
	protected function capabilityCheck( $cap ) {
		$hasPermission = current_user_can( $cap );

		return apply_filters( 'dlm_rest_capability_check', $hasPermission, $cap );
	}

	/**
	 * Validate the current request.
	 *
	 * @param $request
	 * @param $route_id
	 * @param $capability
	 *
	 * @return mixed|void|WP_Error
	 */
	protected function validateRequest( $request, $route_id, $capability ) {

		if ( ! $this->isRouteEnabled( $this->settings, $route_id ) ) {
			return $this->responseError( $this->errors['route_disabled']['code'], $this->errors['route_disabled']['message'] );
		}

		if( ! is_user_logged_in() ) {
			return $this->responseError(
				'permission_denied',
				__( 'API Key could not be authenticated.', 'digital-license-manager' ),
				array(
					'status' => 401
				)
			);
		}

		if ( ! $this->capabilityCheck( $capability ) ) {
			return $this->responseError(
				'permission_denied',
				__( 'Sorry, you don\'t have access to this resource.', 'digital-license-manager' ),
				array(
					'status' => is_user_logged_in() ? 403 : 401
				)
			);
		}

		$state = apply_filters( 'dlm_rest_api_validate_request', true, $request, $capability, $this );

		return apply_filters( 'dlm_rest_api_' . $route_id . '_validate_request', $state, $request, $capability, $this );
	}

	/**
	 * Prepares query parameters from input ( removes unwanted data )
	 *
	 * @param $params
	 *
	 * @since 1.5.6
	 *
	 * @return mixed
	 */
	protected function prepareInput( $params ) {

		if ( ! is_array( $params ) || empty( $params ) ) {
			return $params;
		}

		$excluded = [ 'consumer_key', 'consumer_secret' ];

		foreach ( $excluded as $key ) {
			if ( isset( $params[ $key ] ) ) {
				unset( $params[ $key ] );
			}
		}

		return apply_filters( 'dlm_rest_api_prepare_input', $params );
	}

	/**
	 * Return the request credentials
	 * @return array
	 */
	public static function getCredentials() {

		$result = array();

		// If the $_GET parameters are present, use those first.
		if ( ! empty( $_GET['consumer_key'] ) && ! empty( $_GET['consumer_secret'] ) ) {
			$result['consumer_key']    = sanitize_text_field( $_GET['consumer_key'] );
			$result['consumer_secret'] = sanitize_text_field( $_GET['consumer_secret'] );
		}

		// If the above is not present, we will do full basic auth.
		if ( empty( $result['consumer_key'] ) && ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
			$result['consumer_key']    = sanitize_text_field( $_SERVER['PHP_AUTH_USER'] );
			$result['consumer_secret'] = sanitize_text_field( $_SERVER['PHP_AUTH_PW'] );
		}

		return $result;
	}

	/**
	 * Return the user data for the given consumer_key.
	 *
	 * @param string $consumerKey Part of the user authentication
	 *
	 * @return false|object|ApiKey
	 */
	public static function getCurrentConsumer( $consumerKey ) {

		$consumerKey = StringHasher::make( sanitize_text_field( $consumerKey ) );

		static $cache = array();

		if ( empty( $cache[ $consumerKey ] ) ) {
			$cache[ $consumerKey ] = ApiKeys::instance()->findBy( [ 'consumer_key' => $consumerKey ] );
			if ( isset( $cache[ $consumerKey ]->endpoints ) ) {
				$cache[ $consumerKey ]->endpoints = JsonFormatter::decode( $cache[ $consumerKey ]->endpoints, true );
			}
		}

		return $cache[ $consumerKey ];
	}

}
