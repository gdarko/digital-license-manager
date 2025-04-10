<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template which adds the license keys to the "order complete" email (HTML).
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/emails/email-order-licenses.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.1
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Controller;

defined( 'ABSPATH' ) || exit;

?>

<h2><?php echo wp_kses( $heading, \IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags() ); ?></h2>

<?php do_action( 'dlm_myaccount_licenses_after_heading' ); ?>

<div style="margin-bottom: 40px;">
	<?php foreach ( $data as $row ): ?>
        <table class="td" cellspacing="0" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <thead>
            <tr>
                <th class="td" scope="col" style="text-align: left;" colspan="3">
                    <span><?php echo esc_html( $row['name'] ); ?></span>
                </th>
            </tr>
            </thead>
			<tbody>
			<?php
			/** @var License $license */
			foreach ( $row['keys'] as $license ):

				$licenseKey = $license->getDecryptedLicenseKey();
				if ( is_wp_error( $licenseKey ) ) {
					$licenseKey = '';
				}
				$actions = apply_filters( 'dlm_email_order_licenses_row_actions', array(), $license, $licenseKey, $data );
				if ( is_array( $actions ) ) {
					ksort( $actions );
				}

				$totalCols = 1;
				if ( empty( $license->getExpiresAt() ) ) {
					$totalCols ++;
				}
				if ( empty( $actions ) ) {
					$totalCols ++;
				}

                ?>
                <tr>
                    <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" colspan="<?php echo (int) $totalCols; ?>">
	                    <?php
	                    echo wc_get_template_html( 'dlm/emails/partials/license-key.php', array(
		                    'mode' => 'email',
		                    'license' => $license,
	                    ), '', Controller::getTemplatePath() )
	                    ?>
                    </td>
					<?php if ( $license->getExpiresAt() ): ?><?php
						$date = wp_date( DateFormatter::getExpirationFormat(), strtotime( $license->getExpiresAt() ) );
						?>
                        <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                            <code>
                                <?php printf( '%s <strong>%s</strong>', $valid_until, $date ); ?>
                            </code>
                        </td>
					<?php endif; ?>
	                <?php if ( ! empty( $actions ) ): ?>
                        <td class="dlm-email-table-row-actions license-key-actions" style="border: 1px solid #e5e5e5; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
			                <?php
			                foreach ( $actions as $key => $action ) {
				                $href     = isset( $action['href'] ) ? esc_url( $action['href'] ) : '';
				                $cssClass = isset( $action['class'] ) ? esc_attr( $action['class'] ) : '';
				                $text     = isset( $action['text'] ) ? esc_html( $action['text'] ) : '';
				                echo wp_kses( sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $href ), $cssClass, $text ), \IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags() );
			                }
			                ?>
                        </td>
	                <?php endif; ?>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
	<?php endforeach; ?>
</div>
