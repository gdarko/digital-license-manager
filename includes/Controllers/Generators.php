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

use IdeoLogix\DigitalLicenseManager\Database\Models\Generator;
use IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Utils\HttpHelper;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;


defined( 'ABSPATH' ) || exit;

/**
 * Class Generators
 * @package IdeoLogix\DigitalLicenseManager\Controllers
 */
class Generators {

	/**
	 * @var GeneratorsService
	 */
	protected $service;

	/**
	 * Generators constructor.
	 */
	public function __construct() {
		$this->service = new GeneratorsService();
		// Admin POST requests
		add_action( 'admin_post_dlm_create_generators', array( $this, 'create' ), 10 );
		add_action( 'admin_post_dlm_edit_generators', array( $this, 'update' ), 10 );
		add_action( 'admin_post_dlm_generate_license_keys', array( $this, 'generate' ), 10 );
	}

	/**
	 * Stores the Generator based on user input
	 */
	public function create() {

		// Verify the nonce.
		check_admin_referer( 'dlm_create_generators' );

		if ( ! current_user_can( 'dlm_create_generators' ) ) {
			NoticeFlasher::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s', PageSlug::GENERATORS ) ) );
		}

		$generator = $this->service->create( $_POST );
		if ( is_wp_error( $generator ) ) {
			if ( 'data_error' === $generator->get_error_code() ) {
				NoticeFlasher::error( $generator->get_error_message() );
				$redirectUrl = sprintf( 'admin.php?page=%s&action=add', PageSlug::GENERATORS );
			} else {
				NoticeFlasher::error( __( 'There was a problem creating the generator.', 'digital-license-manager' ) );
				$redirectUrl = sprintf( 'admin.php?page=%s', PageSlug::GENERATORS );
			}
		} else {
			NoticeFlasher::success( __( 'The generator was created successfully.', 'digital-license-manager' ) );
			$redirectUrl = sprintf( 'admin.php?page=%s', PageSlug::GENERATORS );
		}
		if ( $redirectUrl ) {
			HttpHelper::redirect( $redirectUrl );
		}

	}

	/**
	 * Updates existing generator based on user input
	 */
	public function update() {

		check_admin_referer( 'dlm_edit_generators' );

		if ( ! current_user_can( 'dlm_edit_generators' ) ) {
			NoticeFlasher::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s', PageSlug::GENERATORS ) ) );
		}

		$id        = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';
		$generator = $this->service->update( $id, $_POST );

		if ( is_wp_error( $generator ) ) {
			if ( 'data_error' === $generator->get_error_code() ) {
				NoticeFlasher::error( $generator->get_error_message() );
			} else {
				NoticeFlasher::error( __( 'Unable to update generator.', 'digital-license-manager' ) );
			}
		} else {
			NoticeFlasher::success( __( 'The generator has been updated successfully.', 'digital-license-manager' ) );
		}

		HttpHelper::redirect( sprintf( 'admin.php?page=%s&action=edit&id=%d', PageSlug::GENERATORS, $id ) );

	}

	/**
	 * Generates licenses based on user input in the generator page
	 * @return void
	 */
	public function generate() {

		check_admin_referer( 'dlm_generate_license_keys' );

		if ( ! current_user_can( 'dlm_create_generators' ) ) {
			NoticeFlasher::error( __( 'Permission denied. You don\'t have access to perform this action.', 'digital-license-manager' ) );
			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s', PageSlug::GENERATORS ) ) );
		}

		$generatorId = (int) $_POST['generator_id'];
		$amount      = (int) $_POST['amount'];
		$status      = (int) $_POST['status'];
		$validFor    = (int) $_POST['valid_for'];
		$orderId     = null;
		$productId   = null;

		/** @var Generator $generator */
		$generator = $this->service->find( $generatorId );

		if ( is_wp_error( $generator ) ) {
			NoticeFlasher::error( __( 'The selected generator does not exist.', 'digital-license-manager' ) );
			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s&action=edit&id=%d', PageSlug::GENERATORS, $generatorId ) ) );
		}

		if ( array_key_exists( 'order_id', $_POST ) && $_POST['order_id'] ) {
			$orderId = absint( $_POST['order_id'] );
		}

		if ( array_key_exists( 'product_id', $_POST ) && $_POST['product_id'] ) {
			$productId = absint( $_POST['product_id'] );
		}

		if ( $orderId && ! apply_filters( 'dlm_validate_order_id', true, $orderId ) ) {
			NoticeFlasher::error( __( 'The selected order does not exist.', 'digital-license-manager' ) );
			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s&action=generate', PageSlug::GENERATORS ) ) );
		}

		if ( $productId && ! apply_filters( 'dlm_validate_product_id', true, $productId ) ) {
			NoticeFlasher::error( __( 'The selected product does not exist.', 'digital-license-manager' ) );
			HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s&action=generate', PageSlug::GENERATORS ) ) );
		}

		$licenses = $this->service->generateLicenses( $amount, $generator );

		if ( ! is_wp_error( $licenses ) ) {
			// Save the license keys.
			$licensesService = new LicensesService();
			$status          = $licensesService->saveGeneratedLicenseKeys( $orderId, $productId, $licenses, $status, $generator, $validFor );
			if ( is_wp_error( $status ) ) {
				NoticeFlasher::error( $status->get_error_message() );
			} else {
				NoticeFlasher::success( sprintf( __( 'Successfully generated %d license(s).', 'digital-license-manager' ), $amount ) );
			}
		} else {
			NoticeFlasher::error( $licenses->get_error_message() );
		}

		HttpHelper::redirect( admin_url( sprintf( 'admin.php?page=%s&action=generate', PageSlug::GENERATORS ) ) );
	}
}
