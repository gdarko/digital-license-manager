<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Settings;

class Activations {

	const NONCE = 'dlm_account';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_dlm_manual_activation', array( $this, 'handleManualActivation' ), 10 );
		add_filter( 'dlm_myaccount_license_activation_row_actions', array( $this, 'licenseActivationRowActions' ), 10, 4 );
		add_action( 'dlm_myaccount_handle_action_activation_row_actions', array( $this, 'handleLicenseActivationActions' ) );
		add_action( 'dlm_myaccount_handle_action_manual_activation', array( $this, 'handleManualLicenseActivation' ) );
		add_filter( 'dlm_myaccount_whitelisted_actions', array( $this, 'whitelistAdditionalAccountActions' ) );
	}

	/**
	 * Whitelist additional account actions
	 * @return array
	 */
	public function whitelistAdditionalAccountActions( $actions ) {

		return array_merge( $actions, array(
			'activation_row_actions',
			'manual_activation'
		) );
	}

	/**
	 * Add row actions to license activations
	 *
	 * @param $list
	 * @param $license
	 * @param $order
	 * @param $product
	 *
	 * @return array
	 */
	public function licenseActivationRowActions( $list, $license, $order, $product ) {

		if ( (int) Settings::get( 'enable_manual_activations', Settings::SECTION_WOOCOMMERCE ) ) {
			$list[50] = array(
				'id'       => 'license_activation_delete',
				'text'     => __( 'Delete', 'digital-license-manager' ),
				'title'    => __( 'Enables licenses deactivation to disable specific license activations.', 'digital-license-manager' ),
				'class'    => 'button',
				'confirm'  => true,
				'href'     => null,
				'priority' => 50,
			);
		}

		return $list;
	}

	/**
	 * Handle the License Activation row actions
	 * @return void
	 */
	public function handleLicenseActivationActions() {

		if ( isset( $_POST['license_activation_delete'] ) && (int) $_POST['license_activation_delete'] ) {

			$token   = isset( $_POST['activation'] ) && ! empty( $_POST['activation'] ) ? sanitize_text_field( $_POST['activation'] ) : null;
			$service = new LicensesService();

			$result = false;
			if ( ! empty( $token ) ) {
				$result = $service->deleteActivation( $token );
			}

			$licenseKey = isset( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : '';
			$license    = $service->find( $licenseKey );

			if ( is_wp_error( $result ) ) {
				\wc_add_notice( $result->get_error_message(), 'error' );
			} else {
				if ( $result ) {
					\wc_add_notice( __( 'License activation deleted successfully.', 'digital-license-manager' ), 'success' );
				} else {
					\wc_add_notice( __( 'Unable to delete license activation.', 'digital-license-manager' ), 'error' );
				}
			}

			wp_redirect( wc_get_account_endpoint_url( sprintf( 'digital-licenses/%s', $license->getId() ) ) );
			exit;

		}

	}

	/**
	 * Handles manual license activation
	 * @return void
	 */
	public function handleManualLicenseActivation() {

		$service      = new LicensesService();
		$licenseKey   = isset( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : null;
		$licenseLabel = isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : null;
		$license      = $service->find( $licenseKey );

		if ( is_wp_error( $license ) ) {
			\wc_add_notice( $license->get_error_message(), 'error' );
			wp_redirect( wc_get_account_endpoint_url( 'digital-licenses' ) );
			exit;
		}
		if ( current_user_can( 'administrator' ) || $license->getUserId() === get_current_user_id() ) {
			$result = $service->activate( $licenseKey, [ 'label' => $licenseLabel, 'source' => ActivationSource::WEB ] );
			if ( is_wp_error( $result ) ) {
				\wc_add_notice( $result->get_error_message(), 'error' );
			} else {
				\wc_add_notice( __( 'License activated successfully!', 'digital-license-manager' ), 'success' );
			}
		} else {
			\wc_add_notice( __( 'Permission denied. User does not have access to activate this license.', 'digital-license-manager' ), 'error' );
		}

		wp_redirect( wc_get_account_endpoint_url( sprintf( 'digital-licenses/%s', $license->getId() ) ) );
		exit;
	}

}