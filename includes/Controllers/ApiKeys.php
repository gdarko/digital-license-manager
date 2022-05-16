<?php

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\ApiKey as ApiKeyResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey as ApiKeyResourceRepository;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher as AdminNotice;
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
		add_action( 'admin_post_dlm_api_key_update', array( $this, 'saveApiKey' ), 10 );
	}

	/**
	 * Store a created API key to the database or updates an existing key.
	 */
	public function saveApiKey() {

		$action = sanitize_text_field( wp_unslash( $_POST['dlm_action'] ) );

		$error = null;
		// Check the nonce.
		check_admin_referer( 'dlm-api-key-update' );

		$cap = $action == 'create' ? 'dlm_create_api_keys' : 'dlm_edit_api_keys';

		if ( ! current_user_can( $cap ) ) {
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

		$keyId       = absint( $_POST['id'] );
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
			AdminNotice::error( $error );
			wp_redirect( sprintf( 'admin.php?page=%s&tab=rest_api&create_key=1', PageSlug::SETTINGS ) );
			exit();
		}

		if ( $action === 'create' ) {

			$consumerKey    = 'ck_' . StringHasher::random();
			$consumerSecret = 'cs_' . StringHasher::random();

			/** @var ApiKeyResourceModel $apiKey */
			$apiKey = ApiKeyResourceRepository::instance()->insert(
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
				AdminNotice::success( __( 'API key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.', 'digital-license-manager' ) );
				set_transient( 'dlm_consumer_key', $consumerKey, 60 );
				set_transient( 'dlm_api_key', $apiKey, 60 );
			} else {
				AdminNotice::error( __( 'There was a problem generating the API key.', 'digital-license-manager' ) );
			}

			wp_redirect( sprintf( 'admin.php?page=%s&tab=rest_api&show_key=1', PageSlug::SETTINGS ) );
			exit();
		} elseif ( $action === 'edit' ) {

			$apiKey = ApiKeyResourceRepository::instance()->update(
				$keyId,
				array(
					'user_id'     => $userId,
					'endpoints'   => JsonFormatter::encode( $_POST['endpoints'] ),
					'description' => $description,
					'permissions' => $permissions
				)
			);

			if ( $apiKey ) {
				AdminNotice::success( __( 'API key updated successfully.', 'digital-license-manager' ) );
			} else {
				AdminNotice::error( __( 'There was a problem updating the API key.', 'digital-license-manager' ) );
			}

			wp_redirect( sprintf( 'admin.php?page=%s&tab=rest_api&edit_key=%s', PageSlug::SETTINGS, $keyId ) );
			exit();
		}
	}
}
