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

use IdeoLogix\DigitalLicenseManager\Database\Models\ApiKey;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\ApiKeys as ApiKeysRepository;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Utils\HttpHelper;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher;
use IdeoLogix\DigitalLicenseManager\Utils\StringHasher;

defined( 'ABSPATH' ) || exit;

/**
 * Class ApiKeys
 * @package IdeoLogix\DigitalLicenseManager\Controllers
 */
class ApiKeys {

	/**
	 * ApiKeys constructor.
	 */
	public function __construct() {
		// Admin POST requests
		add_action( 'admin_post_dlm_api_key_update', array( $this, 'handle' ), 10 );
	}

	/**
	 * Handles the request
	 * @return void
	 */
	public function handle() {
		$action = sanitize_text_field( wp_unslash( $_POST['dlm_action'] ) );

		switch ( $action ) {
			case 'edit':
				$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
				$this->edit( $id );
				break;
			case 'create':
				$this->create();
				break;
		}
	}

	/**
	 * Handles the UPDATE action
	 * @return void
	 */
	private function edit( $id ) {

		$error = '';

		if ( ! current_user_can( 'dlm_edit_api_keys' ) ) {
			$error = __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' );
		}

		if ( empty( $_POST['description'] ) ) {
			$error = __( 'Description is missing.', 'digital-license-manager' );
		}

		if ( empty( $_POST['user'] ) || $_POST['user'] == - 1 ) {
			$error = __( 'User is missing.', 'digital-license-manager' );
		}

		if ( empty( $_POST['permissions'] ) ) {
			$error = __( 'Permissions are missing.', 'digital-license-manager' );
		}

		if ( empty( $_POST['endpoints'] ) ) {
			$error = __( 'Endpoints are missing..', 'digital-license-manager' );
		}

		$description = sanitize_text_field( wp_unslash( $_POST['description'] ) );
		$permissions = 'read';
		$userId      = absint( $_POST['user'] );

		// Set the correct permissions from the form
		if ( in_array( $_POST['permissions'], array( 'read', 'write', 'read_write' ) ) ) {
			$permissions = sanitize_text_field( $_POST['permissions'] );
		}

		// Check if current user can edit other users
		if ( $userId && ! current_user_can( 'edit_user', $userId ) ) {
			if ( get_current_user_id() !== $userId ) {
				$error = __( 'You do not have permission to assign API keys to the selected user.', 'digital-license-manager' );
			}
		}

		if ( $error ) {
			NoticeFlasher::error( $error );
			HttpHelper::redirect( sprintf( 'admin.php?page=%s&tab=rest_api&create_key=1', PageSlug::SETTINGS ) );
		}

		$apiKey = ApiKeysRepository::instance()->update(
			$id,
			array(
				'user_id'     => $userId,
				'endpoints'   => JsonFormatter::encode( $_POST['endpoints'] ),
				'description' => $description,
				'permissions' => $permissions
			)
		);

		if ( $apiKey ) {
			NoticeFlasher::success( __( 'API key updated successfully.', 'digital-license-manager' ) );
		} else {
			NoticeFlasher::error( __( 'There was a problem updating the API key.', 'digital-license-manager' ) );
		}

		HttpHelper::redirect( sprintf( 'admin.php?page=%s&tab=rest_api&edit_key=%s', PageSlug::SETTINGS, $id ) );
	}


	/**
	 * Handles the CREATE action
	 * @return void
	 */
	private function create() {

		$error = '';

		if ( ! current_user_can( 'dlm_create_api_keys' ) ) {
			$error = __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' );
		}

		if ( empty( $_POST['description'] ) ) {
			$error = __( 'Description is missing.', 'digital-license-manager' );
		}

		if ( empty( $_POST['user'] ) || $_POST['user'] == - 1 ) {
			$error = __( 'User is missing.', 'digital-license-manager' );
		}

		if ( empty( $_POST['permissions'] ) ) {
			$error = __( 'Permissions are missing.', 'digital-license-manager' );
		}

		if ( empty( $_POST['endpoints'] ) ) {
			$error = __( 'Endpoints are missing..', 'digital-license-manager' );
		}

		$description = sanitize_text_field( wp_unslash( $_POST['description'] ) );
		$userId      = absint( $_POST['user'] );
		$permissions = 'read';

		// Set the correct permissions from the form
		if ( in_array( $_POST['permissions'], array( 'read', 'write', 'read_write' ) ) ) {
			$permissions = sanitize_text_field( $_POST['permissions'] );
		}

		// Check if current user can edit other users
		if ( $userId && ! current_user_can( 'edit_user', $userId ) ) {
			if ( get_current_user_id() !== $userId ) {
				$error = __( 'You do not have permission to assign API keys to the selected user.', 'digital-license-manager' );
			}
		}

		if ( $error ) {
			NoticeFlasher::error( $error );
			HttpHelper::redirect( sprintf( 'admin.php?page=%s&tab=rest_api&create_key=1', PageSlug::SETTINGS ) );
		}

		$consumerKey    = 'ck_' . StringHasher::random();
		$consumerSecret = 'cs_' . StringHasher::random();

		/** @var ApiKey $apiKey */
		$apiKey = ApiKeysRepository::instance()->insert(
			array(
				'user_id'         => $userId,
				'description'     => $description,
				'permissions'     => $permissions,
				'endpoints'       => JsonFormatter::encode( $_POST['endpoints'] ),
				'consumer_key'    => StringHasher::make( $consumerKey ),
				'consumer_secret' => $consumerSecret,
				'truncated_key'   => substr( $consumerKey, - 7 ),
			)
		);

		if ( $apiKey ) {
			NoticeFlasher::success( __( 'API key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.', 'digital-license-manager' ) );
			set_transient( 'dlm_consumer_key', $consumerKey, 60 );
			set_transient( 'dlm_api_key', $apiKey, 60 );
		} else {
			NoticeFlasher::error( __( 'There was a problem generating the API key.', 'digital-license-manager' ) );
		}

		HttpHelper::redirect( sprintf( 'admin.php?page=%s&tab=rest_api&show_key=1', PageSlug::SETTINGS ) );

	}
}
