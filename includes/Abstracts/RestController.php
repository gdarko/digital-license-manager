<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Utils\Hash;
use IdeoLogix\DigitalLicenseManager\Utils\Json;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

abstract class RestController extends WP_REST_Controller {
	/**
	 * Returns a structured response object for the API.
	 *
	 * @param  bool  $success  Indicates whether the request was successful or not
	 * @param  array  $data  Contains the response data
	 * @param  int  $code  Contains the response HTTP status code
	 * @param  string  $route  Contains the request route name
	 *
	 * @return WP_REST_Response
	 */
	protected function response( $success, $data, $code = 200, $route = '' ) {
		return new WP_REST_Response(
			array(
				'success' => $success,
				'data'    => apply_filters( 'dlm_rest_api_pre_response', $data, $_SERVER['REQUEST_METHOD'], $route )
			),
			$code
		);
	}

	/**
	 * Prepare the error response based on WP_Error object.
	 *
	 * @param  WP_Error  $error
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
	 * @param  array  $data
	 *
	 * @return WP_Error
	 */
	protected function responseError( $code, $message, $data = array() ) {
		return self::_responseError( $code, $message, $data );
	}

	/**
	 * Respond with specific error message
	 *
	 * @param $code
	 * @param $message
	 * @param  array  $data
	 *
	 * @return WP_Error
	 */
	public static function _responseError( $code, $message, $data = array() ) {
		$prefix = apply_filters( 'dlm_rest_code_prefix', '' );
		$code   = str_replace( sprintf( '%s', $prefix ), '', $code );

		return new WP_Error( sprintf( '%s%s', $prefix, $code ), $message, $data );
	}

	/**
	 * Checks if the given string is a JSON object.
	 *
	 * @param  string  $string
	 *
	 * @return bool
	 */
	protected function isJson( $string ) {
		json_decode( $string );

		return ( json_last_error() === JSON_ERROR_NONE );
	}

	/**
	 * Checks whether a specific API route is enabled.
	 *
	 * @param  array  $settings  Plugin settings array
	 * @param  string  $routeId  Unique plugin API endpoint ID
	 *
	 * @return bool
	 */
	protected function isRouteEnabled( $settings, $routeId ) {
		$credentials = self::getCredentials();
		$consumerKey = isset( $credentials['consumer_key'] ) ? $credentials['consumer_key'] : null;
		if ( ! $consumerKey ) {
			return false;
		}
		$user = self::getUserDataByConsumerKey( $consumerKey );

		return isset( $user->endpoints[ $routeId ] ) && ! empty( $user->endpoints[ $routeId ] );
	}

	/**
	 * Returns the default error for disabled routes.
	 *
	 * @return WP_Error
	 */
	protected function routeDisabledError() {
		return new WP_Error(
			'dlm_rest_route_disabled_error',
			'This route is disabled via the plugin settings.',
			array( 'status' => 404 )
		);
	}

	/**
	 * Converts the passed status string to a valid enumerator value.
	 *
	 * @param  string  $enumerator
	 *
	 * @return int
	 */
	protected function getLicenseStatus( $enumerator ) {
		$status = LicenseStatus::INACTIVE;

		if ( strtoupper( $enumerator ) === 'SOLD' ) {
			return LicenseStatus::SOLD;
		}

		if ( strtoupper( $enumerator ) === 'DELIVERED' ) {
			return LicenseStatus::DELIVERED;
		}

		if ( strtoupper( $enumerator ) === 'ACTIVE' ) {
			return LicenseStatus::ACTIVE;
		}

		if ( strtoupper( $enumerator ) === 'INACTIVE' ) {
			return LicenseStatus::INACTIVE;
		}

		if ( strtoupper( $enumerator ) === 'DISABLED' ) {
			return LicenseStatus::DISABLED;
		}

		return $status;
	}

	/**
	 * Callback method for the "permission_callback" argument of the
	 * "register_rest_route" method.
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return bool|WP_Error
	 */
	public function permissionCallback( $request ) {
		$error = apply_filters( 'dlm_rest_permission_callback', $request );

		if ( $error instanceof WP_Error ) {
			return $error;
		}

		return true;
	}

	/**
	 * Checks if the current user has permission to perform the request.
	 *
	 * @param  string  $cap  Capability slug
	 *
	 * @return bool
	 */
	protected function capabilityCheck( $cap ) {
		$hasPermission = current_user_can( $cap );

		return apply_filters( 'dlm_rest_capability_check', $hasPermission, $cap );
	}

	/**
	 * Returns a contextual HTTP error code for authorization failure.
	 *
	 * @return int
	 */
	protected function authorizationRequiredCode() {
		return is_user_logged_in() ? 403 : 401;
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
	 * @param  string  $consumerKey  Part of the user authentication
	 *
	 * @return array
	 */
	public static function getUserDataByConsumerKey( $consumerKey ) {

		$consumerKey = Hash::make( sanitize_text_field( $consumerKey ) );

		static $cache = array();

		if ( empty( $cache[ $consumerKey ] ) ) {
			global $wpdb;
			$cache[ $consumerKey ] = $wpdb->get_row(
				$wpdb->prepare(
					"
                    SELECT id, user_id, permissions, consumer_key, consumer_secret, nonces, endpoints 
                    FROM {$wpdb->prefix}dlm_api_keys
                    WHERE consumer_key = %s
                ",
					$consumerKey
				)
			);
			if ( isset( $cache[ $consumerKey ]->endpoints ) ) {
				$cache[ $consumerKey ]->endpoints = Json::decode( $cache[ $consumerKey ]->endpoints, true );
			}
		}

		return $cache[ $consumerKey ];
	}

}
