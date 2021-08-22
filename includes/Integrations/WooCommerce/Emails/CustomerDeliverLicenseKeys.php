<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Emails;

use WC_Email;
use WC_Order;

defined( 'ABSPATH' ) || exit;

/**
 * Class CustomerDeliverLicenseKeys
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Emails
 */
class CustomerDeliverLicenseKeys extends WC_Email {

	/**
	 * CustomerDeliverLicenseKeys constructor.
	 */
	public function __construct() {

		$this->id             = 'dlm_email_customer_deliver_licenses';
		$this->title          = __( 'Order License Keys', 'digital-license-manager' );
		$this->description    = __( 'A manual email to send out license keys to the customer.', 'digital-license-manager' );
		$this->heading        = __( 'Your Digital License(s)', 'digital-license-manager' );
		$this->template_html  = 'emails/dlm/email-customer-deliver-licenses.php';
		$this->template_plain = 'emails/dlm/plain/email-customer-deliver-licenses.php';
		$this->template_base  = DLM_TEMPLATES_DIR;
		$this->customer_email = true;

		add_action( 'dlm_email_customer_deliver_licenses', array( $this, 'trigger' ) );

		parent::__construct();
	}

	/**
	 * Get email subject.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_default_subject() {
		return __( '[{site_title}]: Your digital licenses', 'woocommerce' );
	}

	/**
	 * Retrieves the HTML content of the email.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Retrieves the plain text content of the email.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $orderId WooCommerce order ID
	 * @param WC_Order|bool $order WooCommerce order, or a false flag
	 */
	public function trigger( $orderId, $order = false ) {
		$this->setup_locale();

		if ( $orderId && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $orderId );
		}

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                         = $order;
			$this->recipient                      = $this->object->get_billing_email();
			$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
			$this->placeholders['{order_number}'] = $this->object->get_order_number();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		$this->restore_locale();
	}
}
