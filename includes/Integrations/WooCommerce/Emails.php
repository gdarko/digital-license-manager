<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Emails\ResendOrderLicenses;
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
	 * List of registered emails
	 * @var array
	 */
	protected $emails;

	/**
	 * Email constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_email_classes', array( $this, 'registerEmailClasses' ), 90, 1 );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'afterOrderTable' ), 10, 4 );
	}

	/**
	 * Adds the bought license keys to the "Order complete" email,
	 * or displays a notice - depending on the settings.
	 *
	 * @param WC_Order $order
	 * @param bool $isAdminEmail
	 * @param bool $plainText
	 * @param WC_Email $email
	 */
	public function afterOrderTable( $order, $isAdminEmail, $plainText, $email ) {

		// Return if the order isn't complete.
		if ( ! Orders::isComplete( $order->get_id() ) ) {
			return;
		}

		$args = array(
			'order' => $order,
			'data'  => null
		);

		$customerLicenseKeys = Orders::getLicenses( $args );
		if ( empty( $customerLicenseKeys['data'] ) ) {
			return;
		}

		if ( Settings::isAutoDeliveryEnabled() ) {
			// Send the keys out if the setting is active.
			if ( $plainText ) {
				wc_get_template(
					'dlm/emails/plain/email-order-licenses.php',
					array(
						'heading'       => apply_filters( 'dlm_licenses_table_heading', __( 'Your digital license(s)', 'digital-license-manager' ) ),
						'valid_until'   => apply_filters( 'dlm_licenses_table_valid_until', __( 'Valid until', 'digital-license-manager' ) ),
						'data'          => $customerLicenseKeys['data'],
						'date_format'   => get_option( 'date_format' ),
						'order'         => $order,
						'sent_to_admin' => $isAdminEmail,
						'plain_text'    => true,
						'email'         => $email,
						'args'          => apply_filters( 'dlm_template_args_email_order_licenses', array() )
					),
					'',
					Controller::getTemplatePath()
				);
			} else {
				echo wc_get_template_html(
					'dlm/emails/email-order-licenses.php',
					array(
						'heading'       => apply_filters( 'dlm_licenses_table_heading', __( 'Your digital license(s)', 'digital-license-manager' ) ),
						'valid_until'   => apply_filters( 'dlm_licenses_table_valid_until', __( 'Valid until', 'digital-license-manager' ) ),
						'data'          => $customerLicenseKeys['data'],
						'date_format'   => get_option( 'date_format' ),
						'order'         => $order,
						'sent_to_admin' => $isAdminEmail,
						'plain_text'    => false,
						'email'         => $email,
						'args'          => apply_filters( 'dlm_template_args_email_order_licenses', array() )
					),
					'',
					Controller::getTemplatePath()
				);
			}
		} else {
			// Only display a notice.
			if ( $plainText ) {
				wc_get_template(
					'dlm/emails/plain/email-order-license-notice.php',
					array(
						'args' => apply_filters( 'dlm_template_args_email_order_license_notice', array() )
					),
					'',
					Controller::getTemplatePath()
				);
			} else {
				echo wc_get_template_html(
					'dlm/emails/email-order-license-notice.php',
					array(
						'args' => apply_filters( 'dlm_template_args_email_order_license_notice', array() )
					),
					'',
					Controller::getTemplatePath()
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
	public function registerEmailClasses( $emails ) {

		$this->registerResendOrderLicensesEmail();

		return array_merge( $emails, $this->emails );
	}

	/**
	 * Register the resend.
	 * @return void
	 */
	public function registerResendOrderLicensesEmail() {
		add_action( 'dlm_email_order_details', array( $this, 'addOrderDetails' ), 10, 4 );
		add_action( 'dlm_email_order_licenses', array( $this, 'addOrderLicenseKeys' ), 10, 4 );
		$this->emails['DLM_ResendOrderLicenses'] = new ResendOrderLicenses();

		add_action( 'dlm_email_customer_deliver_licenses', array( $this->emails['DLM_ResendOrderLicenses'], 'trigger' ) );
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
				'dlm/emails/plain/email-order-details.php',
				array(
					'order'         => $order,
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $email,
					'args'          => apply_filters( 'dlm_template_args_emails_email_order_details', array() )
				),
				'',
				Controller::getTemplatePath()
			);
		} else {
			echo wc_get_template_html(
				'dlm/emails/email-order-details.php',
				array(
					'order'         => $order,
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $email,
					'args'          => apply_filters( 'dlm_template_args_emails_email_order_details', array() )
				),
				'',
				Controller::getTemplatePath()
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

		$customerLicenseKeys = Orders::getLicenses( $args );

		if ( $plainText ) {
			wc_get_template(
				'dlm/emails/plain/email-order-licenses.php',
				array(
					'heading'       => apply_filters( 'dlm_licenses_table_heading', __( 'Your digital license(s)', 'digital-license-manager' ) ),
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
				Controller::getTemplatePath()
			);
		} else {
			echo wc_get_template_html(
				'dlm/emails/email-order-licenses.php',
				array(
					'heading'       => apply_filters( 'dlm_licenses_table_heading', __( 'Your digital license(s)', 'digital-license-manager' ) ),
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
				Controller::getTemplatePath()
			);
		}
	}
}
