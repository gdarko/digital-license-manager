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


namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\TemplateHelper;

class Frontend {

	/**
	 * Constructor
	 * @since 1.5.1
	 */
	public function __construct() {
		add_action( 'wp_ajax_dlm_licenses_check', [ $this, 'handle_licenses_check' ] );
		add_action( 'wp_ajax_nopriv_dlm_licenses_check', [ $this, 'handle_licenses_check' ] );
	}

	/**
	 * Handles license check
	 * @return void
	 * @since 1.5.1
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
		$expiresF = $expires ? wp_date( DateFormatter::getExpirationFormat(), strtotime( $expires ) ) : __( 'Permanent Activation', 'digital-license-manager' );
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

		$response['html'] = TemplateHelper::render( 'licenses-check-results', [ 'status' => $status, 'colorClass' => $colorClass, 'expires' => $expires, 'expiresF' => $expiresF, 'licenseKey' => $licenseKey ] );

		wp_send_json_success( $response );
		exit;

	}

	/**
	 * Checks the request referer
	 * @return false|int|mixed|null
	 * @since 1.5.1
	 */
	private static function check_referer() {
		return check_ajax_referer( 'dlm_frontend', '_wpnonce', false );
	}


	/**
	 * Renders the licenses check
	 * @return false|string
	 * @since 1.5.1
	 */
	public static function render_licenses_check( $params = [] ) {

		return TemplateHelper::render( 'licenses-check', [
			'emailRequired' => isset( $params['emailRequired'] ) ? $params['emailRequired'] : false,
		] );
	}

	/**
	 * Renders the licenses table
	 * @return false|string
	 * @since 1.5.1
	 */
	public static function render_licenses_table( $params = [] ) {

		$params['records'] = [];

		$statusFilter = isset( $params['statusFilter'] ) ? strtolower( $params['statusFilter'] ) : 'all';

		$currentUserId = is_user_logged_in() ? get_current_user_id() : md5( PHP_INT_MIN );

		switch ( $statusFilter ) {
			case 'valid':
				$query = [
					'expires_at' => [ 'key' => 'expires_at', 'operator' => '>', 'value' => date( 'Y-m-d H:i:s', time() ) ],
					'user_id'    => [ 'key' => 'user_id', 'operator' => '=', 'value' => $currentUserId ]
				];
				break;
			case 'expired':
				$query = [
					'expires_at' => [ 'key' => 'expires_at', 'operator' => '<', 'value' => date( 'Y-m-d H:i:s', time() ) ],
					'user_id'    => [ 'key' => 'user_id', 'operator' => '=', 'value' => $currentUserId ]
				];
				break;
			default:
				$query = [];
				break;
		}

		$query = apply_filters( 'dlm_block_licenses_table_query', $query );

		$records = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses::instance()->findAllBy( $query );

		return TemplateHelper::render( 'licenses-table', [
			'records' => $records
		] );
	}
}