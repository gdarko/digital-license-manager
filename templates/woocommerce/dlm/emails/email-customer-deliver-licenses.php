<?php
/**
 * The template for the ordered license keys inside the delivery email (HTML).
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/emails/email-customer-deliver-licenses.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */


/**
 * Deliver Order license(s) to Customer.
 */
defined('ABSPATH') || exit;

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email);

/**
 * @hooked \IdeoLogix\DigitalLicenseManager\Emails\Main Adds the ordered license keys table.
 */
do_action( 'dlm_email_order_licenses', $order, $sent_to_admin, $plain_text, $email);

/**
 * @hooked \IdeoLogix\DigitalLicenseManager\Emails\Main Adds basic order details.
 */
do_action( 'dlm_email_order_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
