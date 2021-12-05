<?php

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;

use IdeoLogix\DigitalLicenseManager\Utils\Data\Generator as GeneratorUtil;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License as LicenseUtil;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher;

defined( 'ABSPATH' ) || exit;

/**
 * Class Generators
 * @package IdeoLogix\DigitalLicenseManager\Controllers
 */
class Generators {

	/**
	 * Generators constructor.
	 */
	public function __construct() {
		// Admin POST requests
		add_action( 'admin_post_dlm_create_generators', array( $this, 'create' ), 10 );
		add_action( 'admin_post_dlm_edit_generators', array( $this, 'update' ), 10 );
		add_action( 'admin_post_dlm_generate_license_keys', array( $this, 'generate' ), 10 );
	}

	/**
	 * Save the generator to the database.
	 */
	public function create() {

		// Verify the nonce.
		check_admin_referer( 'dlm_create_generators' );

		if ( ! current_user_can( 'dlm_create_generators' ) ) {
			NoticeFlasher::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			wp_redirect( admin_url( sprintf( 'admin.php?page=%s', PageSlug::GENERATORS ) ) );
			exit();
		}

		$generator = GeneratorUtil::create( $_POST );
		if ( is_wp_error( $generator ) ) {
			if ( 'data_error' === $generator->get_error_code() ) {
				NoticeFlasher::error( $generator->get_error_message() );
				wp_redirect( admin_url( sprintf( 'admin.php?page=%s&action=add', PageSlug::GENERATORS ) ) );
				exit();
			} else {
				NoticeFlasher::error( __( 'There was a problem adding the generator.', 'digital-license-manager' ) );
				wp_redirect( admin_url( sprintf( 'admin.php?page=%s', PageSlug::GENERATORS ) ) );
				exit();
			}
		} else {
			NoticeFlasher::success( __( 'The generator was added successfully.', 'digital-license-manager' ) );
			wp_redirect( admin_url( sprintf( 'admin.php?page=%s', PageSlug::GENERATORS ) ) );
			exit();
		}

	}

	/**
	 * Update an existing generator.
	 */
	public function update() {

		// Verify the nonce.
		check_admin_referer( 'dlm_edit_generators' );

		if ( ! current_user_can( 'dlm_edit_generators' ) ) {
			NoticeFlasher::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			wp_redirect( admin_url( sprintf( 'admin.php?page=%s', PageSlug::GENERATORS ) ) );
			exit();
		}

		$id        = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';
		$generator = GeneratorUtil::update( $id, $_POST );

		if ( is_wp_error( $generator ) ) {
			if ( 'data_error' === $generator->get_error_code() ) {
				NoticeFlasher::error( $generator->get_error_message() );
			} else {
				NoticeFlasher::error( __( 'There was a problem updating the generator.', 'digital-license-manager' ) );
			}
		} else {
			NoticeFlasher::success( __( 'The generator was updated successfully.', 'digital-license-manager' ) );
		}

		wp_redirect( sprintf( 'admin.php?page=%s&action=edit&id=%d', PageSlug::GENERATORS, $id ) );
		exit();

	}

	/**
	 * Generates a chosen amount of license keys using the selected generator.
	 */
	public function generate() {
		// Verify the nonce.
		check_admin_referer( 'dlm_generate_license_keys' );

		if ( ! current_user_can( 'dlm_create_generators' ) ) {
			NoticeFlasher::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			wp_redirect( admin_url( sprintf( 'admin.php?page=%s', PageSlug::GENERATORS ) ) );
			exit();
		}

		$generatorId = absint( $_POST['generator_id'] );
		$amount      = absint( $_POST['amount'] );
		$status      = absint( $_POST['status'] );
		$orderId     = null;
		$productId   = null;

		/** @var GeneratorResourceModel $generator */
		$generator = GeneratorUtil::find( $generatorId );

		if ( is_wp_error( $generator ) ) {
			NoticeFlasher::error( __( 'The chosen generator does not exist.', 'digital-license-manager' ) );
			wp_redirect(
				admin_url(
					sprintf(
						'admin.php?page=%s&action=edit&id=%d',
						PageSlug::GENERATORS,
						$generatorId
					)
				)
			);
			exit();
		}

		if ( array_key_exists( 'order_id', $_POST ) && $_POST['order_id'] ) {
			$orderId = absint( $_POST['order_id'] );
		}

		if ( array_key_exists( 'product_id', $_POST ) && $_POST['product_id'] ) {
			$productId = absint( $_POST['product_id'] );
		}

		if ( $orderId && function_exists( 'wc_get_order' ) && ! wc_get_order( $orderId ) ) {
			NoticeFlasher::error( __( 'The chosen order does not exist.', 'digital-license-manager' ) );
			wp_redirect(
				admin_url(
					sprintf(
						'admin.php?page=%s&action=edit&id=%d',
						PageSlug::GENERATORS,
						$generatorId
					)
				)
			);
			exit();
		}

		if ( $productId && function_exists( 'wc_get_product' ) && ! wc_get_product( $productId ) ) {
			NoticeFlasher::error( __( 'The chosen product does not exist.', 'digital-license-manager' ) );
			wp_redirect(
				admin_url(
					sprintf(
						'admin.php?page=%s&action=edit&id=%d',
						PageSlug::GENERATORS,
						$generatorId
					)
				)
			);
			exit();
		}

		$licenses = GeneratorUtil::generateLicenseKeys( $amount, $generator );

		if ( ! is_wp_error( $licenses ) ) {
			// Save the license keys.
			$status = LicenseUtil::saveGeneratedLicenseKeys( $orderId, $productId, $licenses, $status, $generator );
			if ( is_wp_error( $status ) ) {
				NoticeFlasher::error( $status->get_error_message() );
			} else {
				NoticeFlasher::success( sprintf( __( 'Successfully generated %d license key(s).', 'digital-license-manager' ), $amount ) );
			}
		} else {
			NoticeFlasher::error( $licenses->get_error_message() );
		}

		wp_redirect( admin_url( sprintf( 'admin.php?page=%s&action=generate', PageSlug::GENERATORS ) ) );
		exit();
	}
}
