<?php
/**
 * The template which contains the license delivery notice, instead of the license.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/emails/email-order-license-notice.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */
defined('ABSPATH') || exit; ?>

<h2><?php esc_html_e(apply_filters( 'dlm_text_license_table_header', null)); ?></h2>

<p><?php esc_html_e('Your license keys will shortly be delivered. It can take up to 24 hours, but usually doesn\'t take longer than a few minutes. Thank you for your patience.', 'digital-license-manager'); ?></p>
