<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Emails\CustomerDeliverLicenseKeys;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Emails\Templates;
use IdeoLogix\DigitalLicenseManager\Settings;
use WC_Email;
use WC_Order;

defined( 'ABSPATH' ) || exit;

/**
 * Class Email
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class Emails {

	/**
	 * Email constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_email_after_order_table', array( $this, 'afterOrderTable' ), 10, 4 );
		add_action( 'woocommerce_email_classes', array( $this, 'registerClasses' ), 90, 1 );
	}

	/**
	 * Adds the bought license keys to the "Order complete" email, or displays a notice - depending on the settings.
	 *
	 * @param WC_Order $order
	 * @param bool $isAdminEmail
	 * @param bool $plainText
	 * @param WC_Email $email
	 */
	public function afterOrderTable( $order, $isAdminEmail, $plainText, $email ) {

		// Return if the order isn't complete.
		if ( $order->get_status() !== 'completed' && ! Orders::isComplete( $order->get_id() ) ) {
			return;
		}

		$args = array(
			'order' => $order,
			'data'  => null
		);

		$customerLicenseKeys = Orders::getLicenseKeys( $args );
		if ( empty( $customerLicenseKeys['data'] ) ) {
			return;
		}

		if ( (int) Settings::get( 'auto_delivery', Settings::SECTION_GENERAL ) ) {
			// Send the keys out if the setting is active.
			if ( $plainText ) {
				wc_get_template(
					'emails/dlm/plain/email-order-licenses.php',
					array(
						'heading'       => apply_filters( 'dlm_licenses_table_heading',  __( 'Your digital license(s)', 'digital-license-manager' ) ),
						'valid_until'   => apply_filters( 'dlm_licenses_table_valid_until', __( 'Valid until', 'digital-license-manager' ) ),
						'data'          => $customerLicenseKeys['data'],
						'date_format'   => get_option( 'date_format' ),
						'order'         => $order,
						'sent_to_admin' => $isAdminEmail,
						'plain_text'    => true,
						'email'         => $email,
						'args'          => apply_filters( 'dlm_template_args_emails_email_order_licenses', array() )
					),
					'',
					DLM_TEMPLATES_DIR
				);
			} else {
				echo wc_get_template_html(
					'emails/dlm/email-order-licenses.php',
					array(
						'heading'       => apply_filters( 'dlm_licenses_table_heading',  __( 'Your digital license(s)', 'digital-license-manager' ) ),
						'valid_until'   => apply_filters( 'dlm_licenses_table_valid_until', __( 'Valid until', 'digital-license-manager' ) ),
						'data'          => $customerLicenseKeys['data'],
						'date_format'   => get_option( 'date_format' ),
						'order'         => $order,
						'sent_to_admin' => $isAdminEmail,
						'plain_text'    => false,
						'email'         => $email,
						'args'          => apply_filters( 'dlm_template_args_emails_email_order_licenses', array() )
					),
					'',
					DLM_TEMPLATES_DIR
				);
			}
		} else {
			// Only display a notice.
			if ( $plainText ) {
				wc_get_template(
					'emails/dlm/plain/email-order-license-notice.php',
					array(
						'args' => apply_filters( 'dlm_template_args_emails_email_order_license_notice', array() )
					),
					'',
					DLM_TEMPLATES_DIR
				);
			} else {
				echo wc_get_template_html(
					'emails/dlm/email-order-license-notice.php',
					array(
						'args' => apply_filters( 'dlm_template_args_emails_email_order_license_notice', array() )
					),
					'',
					DLM_TEMPLATES_DIR
				);
			}
		}
	}

	/**
	 * Registers the plugin email classes to work with WooCommerce.
	 *
	 * @param array $emails
	 *
	 * @return array
	 */
	public function registerClasses( $emails ) {
		new Templates();

		$pluginEmails = array(
			'DLM_Customer_Deliver_License_Keys' => new CustomerDeliverLicenseKeys()
		);

		return array_merge( $emails, $pluginEmails );
	}
}