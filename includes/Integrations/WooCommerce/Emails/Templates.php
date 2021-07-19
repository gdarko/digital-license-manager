<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Emails;

use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Orders;
use WC_Email;
use WC_Order;

defined( 'ABSPATH' ) || exit;

/**
 * Class Templates
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Emails
 */
class Templates {
	/**
	 * Templates constructor.
	 */
	function __construct() {
		add_action( 'dlm_email_order_details', array( $this, 'addOrderDetails' ), 10, 4 );
		add_action( 'dlm_email_order_licenses', array( $this, 'addOrderLicenseKeys' ), 10, 4 );
	}

	/**
	 * Adds the ordered license keys to the email body.
	 *
	 * @param WC_Order $order WooCommerce Order
	 * @param bool $sentToAdmin Determines if the email is sent to the admin
	 * @param bool $plainText Determines if a plain text or HTML email will be sent
	 * @param WC_Email $email WooCommerce Email
	 */
	public function addOrderDetails( $order, $sentToAdmin, $plainText, $email ) {
		if ( $plainText ) {
			wc_get_template(
				'emails/dlm/plain/email-order-details.php',
				array(
					'order'         => $order,
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $email,
					'args'          => apply_filters( 'dlm_template_args_emails_email_order_details', array() )
				),
				'',
				DLM_TEMPLATES_DIR
			);
		} else {
			echo wc_get_template_html(
				'emails/dlm/email-order-details.php',
				array(
					'order'         => $order,
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $email,
					'args'          => apply_filters( 'dlm_template_args_emails_email_order_details', array() )
				),
				'',
				DLM_TEMPLATES_DIR
			);
		}
	}

	/**
	 * Adds basic order info to the email body.
	 *
	 * @param WC_Order $order WooCommerce Order
	 * @param bool $sentToAdmin Determines if the email is sent to the admin
	 * @param bool $plainText Determines if a plain text or HTML email will be sent
	 * @param WC_Email $email WooCommerce Email
	 */
	public function addOrderLicenseKeys( $order, $sentToAdmin, $plainText, $email ) {
		$args = array(
			'order' => $order,
			'data'  => null
		);

		$customerLicenseKeys = Orders::getLicenseKeys( $args );

		if ( $plainText ) {
			wc_get_template(
				'emails/dlm/plain/email-order-licenses.php',
				array(
					'heading'       => apply_filters( 'dlm_licenses_table_heading',  __( 'Your digital license(s)', 'digital-license-manager' ) ),
					'valid_until'   => apply_filters( 'dlm_licenses_table_valid_until', __( 'Valid until', 'digital-license-manager' ) ),
					'data'          => $customerLicenseKeys['data'],
					'date_format'   => get_option( 'date_format' ),
					'order'         => $order,
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $email,
					'args'          => apply_filters( 'dlm_template_args_emails_order_license_keys', array() )
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
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $email,
					'args'          => apply_filters( 'dlm_template_args_emails_order_license_keys', array() )
				),
				'',
				DLM_TEMPLATES_DIR
			);
		}
	}
}