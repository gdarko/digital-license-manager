<?php

namespace IdeoLogix\DigitalLicenseManager\RestAPI;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractRestController;
use IdeoLogix\DigitalLicenseManager\Settings;
use stdClass;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

/**
 * Class Authentication
 * @package IdeoLogix\DigitalLicenseManager\RestAPI
 */
class Authentication {
	/**
	 * @var WP_Error
	 */
	protected $error = null;

	/**
	 * @var stdClass
	 */
	protected $user = null;

	/**
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
	 * @return bool
	 */
	protected function isRequestToRestApi() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$restPrefix = trailingslashit( rest_get_url_prefix() );

		// Check if our endpoint.
		$dlm = ( false !== strpos( $_SERVER['REQUEST_URI'], $restPrefix . 'dlm/' ) );

		return $dlm;
	}

	/**
	 * Authenticates the user.
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
	 * Sets an authentication error.
	 *
	 * @param WP_Error $error Authentication error data.
	 */
	protected function setError( $error ) {
		// Reset user.
		$this->user = null;

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

	/**
	 * Basic Authentication.
	 *
	 * SSL-encrypted requests are not subject to sniffing or man-in-the-middle
	 * attacks, so the request can be authenticated by simply looking up the user
	 * associated with the given consumer key and confirming the consumer secret
	 * provided is valid.
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
		$this->user = AbstractRestController::getUserDataByConsumerKey( $consumerKey );

		if ( empty( $this->user ) ) {
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
		if ( ! hash_equals( $this->user->consumer_secret, $consumerSecret ) ) {
			$this->setError(
				$this->responseError(
					'authentication_error',
					__( 'Consumer secret is invalid.', 'digital-license-manager' ),
					array( 'status' => 401 )
				)
			);

			return false;
		}

		return $this->user->user_id;
	}

	/**
	 * Check that the API keys provided have the proper key-specific permissions to either read or write API resources.
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
	 */
	private function updateLastAccess() {
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . 'dlm_api_keys',
			array( 'last_access' => current_time( 'mysql' ) ),
			array( 'id' => $this->user->id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * If the consumer_key and consumer_secret $_GET parameters are NOT provided
	 * and the Basic auth headers are either not present or the consumer secret does not match the consumer
	 * key provided, then return the correct Basic headers and an error message.
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
	 * @return mixed
	 */
	public function checkUserPermissions( $result, $server, $request ) {
		if ( $this->user ) {
			// Check API Key permissions.
			$allowed = $this->checkPermissions( $request->get_method() );

			if ( is_wp_error( $allowed ) ) {
				return $allowed;
			}

			// Register last access.
			$this->updateLastAccess();
		}

		// Additional validation performed by the filter
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
}
