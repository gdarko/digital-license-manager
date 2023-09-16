<?php

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;

class Frontend {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_dlm_licenses_check', [ $this, 'handle_licenses_check' ] );
		add_action( 'wp_ajax_nopriv_dlm_licenses_check', [ $this, 'handle_licenses_check' ] );
	}

	/**
	 * Handles license check
	 * @return void
	 */
	public function handle_licenses_check() {

		if ( ! self::check_referer() ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'digital-license-manager' ) ] );
			exit;
		}

		$licenseKey = isset( $_POST['licenseKey'] ) ? sanitize_text_field( $_POST['licenseKey'] ) : '';
		$email      = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$emailCheck = isset( $_POST['echeck'] ) ? (int) $_POST['echeck'] : 0;

		$service = new LicensesService();

		$license = $service->find( $licenseKey );

		if ( is_wp_error( $license ) ) {
			wp_send_json_error( [ 'message' => $license->get_error_message() ] );
			exit;
		}

		if ( $emailCheck ) {
			$userId = $license->getUserId();
			$user   = get_user_by( 'email', $email );
			if ( false === $user ) {
				wp_send_json_error( [ 'message' => __( 'Unfortunately this license does not belong you (1).', 'digital-license-manager' ) ] );
				exit;
			}
			if ( (int) $userId !== $user->ID ) {
				wp_send_json_error( [ 'message' => __( 'Unfortunately this license does not belong you (2).', 'digital-license-manager' ) ] );
				exit;
			}
		}

		$expires  = $license->getExpiresAt();
		$expiresF = $expires ? DateFormatter::convert( $license->getExpiresAt(), 'Y-m-d H:i:s', sprintf( '%s %s', get_option( 'date_format' ), get_option( 'time_format' ) ) ) : __( 'Valid permanently', 'digital-license-manager' );
		$status   = '';
		if ( $license->isExpired() ) {
			$status   = __( 'EXPIRED', 'digital-license-manager' );
			$response = [
				'exp'    => $expires,
				'expF'   => $expiresF,
				'status' => $status
			];
		} else {
			$status   = __( 'VALID', 'digital-license-manager' );
			$response = [
				'exp'    => $expires,
				'expF'   => $expiresF,
				'status' => $status
			];
		}

		$colorClass = 'default';
		if ( ! empty( $status ) ) {
			$colorClass = strtolower( $status );
		}

		ob_start();
		include( DLM_TEMPLATES_DIR . 'frontend' . DIRECTORY_SEPARATOR . 'licenses-check-results.php' );
		$response['html'] = ob_get_clean();

		wp_send_json_success( $response );
		exit;

	}

	/**
	 * Checks the request referer
	 * @return false|int|mixed|null
	 */
	private static function check_referer() {
		return check_ajax_referer( 'dlm_frontend', '_wpnonce', false );
	}


	/**
	 * Renders the licenses check
	 * @return false|string
	 */
	public static function render_licenses_check( $params = [] ) {

		return self::get_view( 'licenses-check', [
			'emailRequired' => isset( $params['emailRequired'] ) ? $params['emailRequired'] : false,
		] );
	}

	/**
	 * Renders the licenses table
	 * @return false|string
	 */
	public static function render_licenses_table( $params = [] ) {

		$params['records'] = [];

		$status_filter = isset( $params['status_filter'] ) ? $params['status_filter'] : 'all';

		$current_user_id = is_user_logged_in() ? get_current_user_id() : md5( PHP_INT_MIN );

		switch ( $status_filter ) {
			case 'active':
				$query = [
					[ 'key' => 'expires_at', 'operator', '>', date( 'Y-m-d H:i:s', time() ) ],
					[ 'user_id' => $current_user_id ]
				];
				break;
			case 'inactive':
				$query = [
					[ 'key' => 'expires_at', 'operator', '<', date( 'Y-m-d H:i:s', time() ) ],
					[ 'user_id' => $current_user_id ]
				];
				break;
			default:
				$query = [];
				break;
		}

		$query   = apply_filters( 'dlm_block_licenses_table_query', $query );
		$records = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses::instance()->findAllBy( $query );

		return self::get_view( 'licenses-table', [
			'records' => $records
		] );
	}

	/**
	 * Return the licenses table
	 *
	 * @param $path
	 * @param $data
	 *
	 * @return false|string
	 */
	private static function get_view( $path, $data = [] ) {

		if ( ! empty( $data ) ) {
			extract( $data );
		}

		$path = str_replace( '.', DIRECTORY_SEPARATOR, $path );
		$path = DLM_TEMPLATES_DIR . 'frontend' . DIRECTORY_SEPARATOR . $path . '.php';

		ob_start();
		include $path;

		return ob_get_clean();
	}

}