<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2023  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (c) 2020-203   WooCommerce, Automattic. All Rights Reserved.
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

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractRestController;
use IdeoLogix\DigitalLicenseManager\Database\Models\ApiKey;
use IdeoLogix\DigitalLicenseManager\Settings;
use stdClass;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Class Authentication
 *
 * This class is borowing concepts from the WooCommerce REST Authentication,
 * most of the copyright is held by WooCommerce, Automattic.
 *
 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L14
 *
 * @package IdeoLogix\DigitalLicenseManager\RestAPI
 */
class Authentication {

	/**
	 * The most recent error
	 * @var WP_Error
	 */
	protected $error = null;

	/**
	 * The current API Key that is used
	 * @var ApiKey
	 */
	protected $consumer = null;

	/**
	 * The authentication method
	 * @var string
	 */
	protected $authMethod = '';

	/**
	 * Authentication constructor.
	 */
	public function __construct() {
		add_filter( 'determine_current_user', array( $this, 'authenticate' ), 15 );
		add_filter( 'rest_authentication_errors', array( $this, 'checkAuthenticationError' ), 15 );
		add_filter( 'rest_post_dispatch', array( $this, 'sendUnauthorizedHeaders' ), 50 );
		add_filter( 'rest_pre_dispatch', array( $this, 'checkUserPermissions' ), 10, 3 );
	}

	/**
	 * Checks if the request is meant to be processed by the REST API.
	 *
	 * This code was inspired by and taken from the following copyright holders:
	 * @return bool
	 * @copyright WooCommerce/Automattic
	 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L53
	 *
	 */
	protected function isRequestToRestApi() {

		$requestUri = ! empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

		if ( empty( $requestUri ) ) {
			return false;
		}

		$restPrefix = trailingslashit( rest_get_url_prefix() );

		if ( false !== strpos( $requestUri, $restPrefix . 'dlm/' ) ) {
			return true;
		}

		/**
		 * Compatibility layer for License Manager for WooCommerce
		 * @url https://docs.codeverve.com/digital-license-manager/migration/migrate-from-license-manager-for-woocommerce/
		 */
		if ( apply_filters( 'dlm_compatibility_layer_for_lmfwc', false ) ) {
			if ( false !== strpos( $requestUri, $restPrefix . 'lmfwc/' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Authenticates the user.
	 *
	 * This code was inspired by and taken from the following copyright holders:
	 * @copyright WooCommerce/Automattic
	 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L76
	 *
	 * @param int|false $userId The user ID
	 *
	 * @return int|false
	 */
	public function authenticate( $userId ) {
		// Do not authenticate twice and check if is a request to our endpoint in the WP REST API.
		if ( ! empty( $userId ) || ! $this->isRequestToRestApi() ) {
			return $userId;
		}

		if ( is_ssl() || Settings::get( 'disable_api_ssl', Settings::SECTION_GENERAL ) ) {
			$userId = $this->performBasicAuthentication();
		} else {
			$this->setError(
				$this->responseError(
					'rest_no_ssl_error',
					__( 'The connection is not secure, therefore the API cannot be used.', 'digital-license-manager' ),
					array( 'status' => 403 )
				)
			);

			return false;
		}

		if ( $userId ) {
			return $userId;
		}

		return false;
	}

	/**
	 * Checks for authentication errors.
	 *
	 * This code was inspired by and taken from the following copyright holders:
	 * @copyright WooCommerce/Automattic
	 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L126
	 *
	 * @param WP_Error|null|bool $error WordPress Error object
	 *
	 * @return WP_Error|null|bool
	 */
	public function checkAuthenticationError( $error ) {
		// Pass through other errors.
		if ( ! empty( $error ) ) {
			return $error;
		}

		return $this->getError();
	}

	/**
	 * Basic Authentication.
	 *
	 * SSL-encrypted requests are not subject to sniffing or man-in-the-middle
	 * attacks, so the request can be authenticated by simply looking up the user
	 * associated with the given consumer key and confirming the consumer secret
	 * provided is valid.
	 *
	 *
	 * This code was inspired by and taken from the following copyright holders:
	 * @copyright WooCommerce/Automattic
	 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L83
	 *
	 * @return int|bool
	 */
	private function performBasicAuthentication() {

		$this->authMethod = 'basic_auth';

		$credentials    = AbstractRestController::getCredentials();
		$consumerKey    = isset( $credentials['consumer_key'] ) ? $credentials['consumer_key'] : null;
		$consumerSecret = isset( $credentials['consumer_secret'] ) ? $credentials['consumer_secret'] : null;

		// Stop if we don't have any key.
		if ( ! $consumerKey || ! $consumerSecret ) {
			$this->setError(
				$this->responseError(
					'authentication_error',
					__( 'Consumer key or secret is missing.', 'digital-license-manager' ),
					array( 'status' => 401 )
				)
			);

			return false;
		}

		// Get user data.
		$this->consumer = AbstractRestController::getCurrentConsumer( $consumerKey );

		if ( empty( $this->consumer ) ) {
			$this->setError(
				$this->responseError(
					'authentication_error',
					__( 'Consumer key is invalid.', 'digital-license-manager' ),
					array( 'status' => 401 )
				)
			);

			return false;
		}

		// Validate user secret.
		if ( ! hash_equals( $this->consumer->getConsumerSecret(), $consumerSecret ) ) {
			$this->setError(
				$this->responseError( 'authentication_error',
					__( 'Consumer secret is invalid.', 'digital-license-manager' ),
					array( 'status' => 401 )
				)
			);

			return false;
		}

		return $this->consumer->getUserId();
	}

	/**
	 * Check that the API keys provided have the proper key-specific permissions to either read or write API resources.
	 *
	 * This code was inspired by and taken from the following copyright holders:
	 * @copyright WooCommerce/Automattic
	 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L556
	 *
	 * @param string $method Current HTTP method being used
	 *
	 * @return bool|WP_Error
	 */
	private function checkPermissions( $method ) {
		$permissions = $this->user->permissions;

		switch ( $method ) {
			case 'HEAD':
			case 'GET':
				if ( 'read' !== $permissions && 'read_write' !== $permissions ) {
					return $this->responseError(
						'authentication_error',
						__( 'The API key provided does not have read permissions.', 'digital-license-manager' ),
						array( 'status' => 401 )
					);
				}
				break;
			case 'POST':
			case 'PUT':
			case 'PATCH':
			case 'DELETE':
				if ( 'write' !== $permissions && 'read_write' !== $permissions ) {
					return $this->responseError(
						'authentication_error',
						__( 'The API key provided does not have write permissions.', 'digital-license-manager' ),
						array( 'status' => 401 )
					);
				}
				break;
			case 'OPTIONS':
				return true;

			default:
				return $this->responseError(
					'authentication_error',
					__( 'Unknown request method.', 'digital-license-manager' ),
					array( 'status' => 401 )
				);
		}

		return true;
	}

	/**
	 * Updates API Key last access timestamp.
	 *
	 * This code was inspired by and taken from the following copyright holders:
	 * @copyright WooCommerce/Automattic
	 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L587
	 */
	private function updateLastAccess() {

		if ( empty( $this->consumer ) ) {
			return;
		}
		$this->consumer->last_access = current_time( 'mysql' );
		$this->consumer->save();
	}

	/**
	 * If the consumer_key and consumer_secret $_GET parameters are NOT provided
	 * and the Basic auth headers are either not present or the consumer secret does not match the consumer
	 * key provided, then return the correct Basic headers and an error message.
	 *
	 * This code was inspired by and taken from the following copyright holders:
	 * @copyright WooCommerce/Automattic
	 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L620
	 *
	 * @param WP_REST_Response $response WordPress REST Response object
	 *
	 * @return WP_REST_Response
	 */
	public function sendUnauthorizedHeaders( $response ) {
		if ( is_wp_error( $this->getError() ) && 'basic_auth' === $this->authMethod ) {
			$authMessage = __( 'Digital License Manager API. Use a consumer key in the username field and a consumer secret in the password field.', 'digital-license-manager' );
			$response->header( 'WWW-Authenticate', 'Basic realm="' . $authMessage . '"', true );
		}

		return $response;
	}

	/**
	 * Check for user permissions and register last access.
	 *
	 * @param mixed $result
	 * @param WP_REST_Server $server
	 * @param WP_REST_Request $request
	 *
	 * This code was inspired by and taken from the following copyright holders:
	 * @copyright WooCommerce/Automattic
	 * @url https://github.com/woocommerce/woocommerce/blob/7.9.0/plugins/woocommerce/includes/class-wc-rest-authentication.php#L620
	 *
	 * @return mixed
	 */
	public function checkUserPermissions( $result, $server, $request ) {

		if ( empty( $this->user ) ) {
			return $result;
		}

		$allowed = $this->checkPermissions( $request->get_method() );
		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}

		$this->updateLastAccess();

		$error = apply_filters( 'dlm_rest_api_validation', $result, $server, $request );

		if ( $error instanceof WP_Error ) {
			return $error;
		}

		return $result;
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
	protected function responseError( $code, $message, $data = array() ) {
		return AbstractRestController::_responseError( $code, $message, $data );
	}

	/**
	 * Sets an authentication error.
	 *
	 * @param WP_Error $error Authentication error data.
	 */
	protected function setError( $error ) {
		// Reset user.
		$this->consumer = null;

		$this->error = $error;
	}

	/**
	 * Get authentication error.
	 *
	 * @return WP_Error|null
	 */
	protected function getError() {
		return $this->error;
	}
}
