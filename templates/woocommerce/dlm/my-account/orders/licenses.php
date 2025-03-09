<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template displays the purchased licenses in the order pages (My Account, after checkout, etc)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/my-account/orders/licenses.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.1
 *
 * Default variables
 *
 * @var string $heading
 * @var string $valid_until
 * @var array $data
 * @var string $date_format
 * @var array $args
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Utils\StringFormatter;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Controller;

defined( 'ABSPATH' ) || exit; ?>


<div class="dlm-myaccount-element dlm-myaccount-element--order-licenses">

    <h2 class="dlm-myaccount-page-title dlm-myaccount-page-title--licenses"><?php echo wp_kses( $heading, \IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags() ); ?></h2>

    <?php do_action( 'dlm_myaccount_licenses_after_heading' ); ?>

    <?php
	foreach ( $data as $productId => $row ): ?>
        <table class="dlm-myaccount-table dlm-myaccount-table--order-licenses shop_table">
            <thead>
            <tr>
                <th colspan="3"><?php echo esc_html( $row['name'] ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			/** @var License $license */
			$is_order_received  = is_order_received_page();
			$is_obscure_enabled = (int) Settings::get( 'hide_license_keys', Settings::SECTION_WOOCOMMERCE );
			$should_obfuscate   = $is_order_received && $is_obscure_enabled;

			foreach ( $row['keys'] as $license ):

				$licenseKey = $license->getDecryptedLicenseKey();
				if ( is_wp_error( $licenseKey ) ) {
					$licenseKey = '';
				}
				$actions = apply_filters( 'dlm_myaccount_order_licenses_row_actions', array(), $license, $licenseKey, $data );
				$actions = apply_filters_deprecated( 'dlm_myaccount_licenses_keys_row_actions', array( $actions, $license, $licenseKey, $data ), '1.7.4', 'dlm_myaccount_order_licenses_row_actions' );
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
                    <td colspan="<?php echo (int) $totalCols; ?>">
						<?php
						if ( apply_filters( 'dlm_myaccount_licenses_should_obfuscate', $should_obfuscate, $license ) ) {
							echo esc_html( StringFormatter::obfuscateString( $licenseKey ) );
						} else {
							echo wc_get_template_html( 'dlm/my-account/licenses/partials/license-key.php', array(
								'license' => $license,
							), '', Controller::getTemplatePath() );
						}
						?>
                    </td>
					<?php if ( $license->getExpiresAt() ): ?>
						<?php
						$date = wp_date( $date_format, strtotime( $license->getExpiresAt() ) );
						?>
                        <td>
							<?php echo wp_kses(
								sprintf(
									'%s <strong>%s</strong> %s',
									$valid_until,
									$date,
									$license->isExpired() ? '(' . esc_html__( 'Expired', 'digital-license-manager' ) . ')' : ''
								),
								\IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags(),
							); ?>
                        </td>
					<?php endif; ?>
	                <?php if ( ! empty( $actions ) ): ?>
                        <td class="dlm-myaccount-license-key-actions license-key-actions">
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


