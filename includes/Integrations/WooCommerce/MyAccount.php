<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Utils\Data\Customer;
use Exception;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License as LicenseUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class MyAccount
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class MyAccount {
	/**
	 * MyAccount constructor.
	 */
	public function __construct() {
		add_rewrite_endpoint( 'digital-licenses', EP_ROOT | EP_PAGES );

		add_filter( 'the_title', array( $this, 'accountItemTitles' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'accountMenuItems' ), 10, 1 );
		add_action( 'woocommerce_account_digital-licenses_endpoint', array( $this, 'digitalLicenses' ) );
	}

	/**
	 * Change page titles
	 */
	public function accountItemTitles( $title ) {
		global $wp_query;

		if ( isset( $wp_query->query_vars['digital-licenses'] ) && in_the_loop() ) {
			return __( 'Licenses', 'digital-license-manager' );
		}

		return $title;
	}

	/**
	 * Adds the plugin pages to the "My account" section.
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function accountMenuItems( $items ) {
		$customItems = array(
			'digital-licenses' => __( 'Licenses', 'digital-license-manager' )
		);

		$customItems = array_slice( $items, 0, 2, true ) + $customItems + array_slice( $items, 2, count( $items ), true );

		return $customItems;
	}

	/**
	 * Creates an overview of all purchased license keys.
	 */
	public function digitalLicenses() {

		global $wp_query;

		wp_enqueue_style( 'dlm_main' );

		$user             = wp_get_current_user();
		$message          = new \stdClass();
		$message->type    = '';
		$message->message = '';

		if ( ! $user ) {
			return;
		}

		$licenseKey = null;
		$paged      = 1;

		// Parse query parameters.
		if ( $wp_query->query['digital-licenses'] ) {
			$page = $wp_query->query['digital-licenses'];
			if ( ! empty( $page ) ) {
				$parts = explode( '/', $page );
				if ( count( $parts ) === 2 && $parts[0] === 'page' ) {
					$paged = (int) $parts[1];
				} else {
					$licenseKey = sanitize_text_field( $parts[0] );
				}
			}
		}

		if ( ! $licenseKey ) {

			$licenseKeys = Customer::getLicenseKeys( $user->ID );

			echo wc_get_template_html(
				'myaccount/dlm/licenses-table.php',
				array(
					'dateFormat'  => get_option( 'date_format' ),
					'licenseKeys' => $licenseKeys,
					'message'     => $message,
					'page'        => $paged,
				),
				'',
				DLM_TEMPLATES_DIR
			);

		} else {

			$license = LicenseUtil::find( $licenseKey );

			if ( is_wp_error( $license ) || $license->getUserId() != $user->ID ) {
				echo sprintf( '<h3>%s</h3>', __( 'Not found', 'digital-license-manager' ) );
				echo sprintf( '<p>%s</p>', __( 'The license you are looking for is not found.', 'digital-license-manager' ) );

				return;

			}

			$decrypted = $license->getDecryptedLicenseKey();
			if ( is_wp_error( $decrypted ) ) {
				echo sprintf( '<p>%s</p>', $decrypted->get_error_message() );

				return;
			}

			do_action( 'dlm_myaccount_licenses_single_page_content', $license, $licenseKey );

		}

	}
}
