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

<h2><?php esc_html_e( $heading ); ?></h2>

<?php do_action( 'dlm_myaccount_licenses_after_heading' ); ?>

<?php

foreach ( $data as $productId => $row ): ?>
    <table class="shop_table">
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

		foreach ( $row['keys'] as $license ):

			$licenseKey = $license->getDecryptedLicenseKey();
			if ( is_wp_error( $licenseKey ) ) {
				$licenseKey = '';
			}
			$actions = apply_filters( 'dlm_myaccount_licenses_keys_row_actions', array(), $license, $licenseKey, $data );
			if ( is_array( $actions ) ) {
				ksort( $actions );
			}
			?>
            <tr>
                <td colspan="<?php echo ( $license && $license->getExpiresAt() ) ? '' : '2'; ?>">
                    <?php
                    if ( $is_order_received && $is_obscure_enabled ) {
	                    echo StringFormatter::obfuscateString( $licenseKey );
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
                        <?php printf( '%s <strong>%s</strong>', $valid_until, $date ); ?>
                    </td>
				<?php endif; ?>
                <td class="license-key-actions">
					<?php
					foreach ( $actions as $key => $action ) {
						$href     = isset( $action['href'] ) ? esc_url( $action['href'] ) : '';
						$cssClass = isset( $action['class'] ) ? esc_attr( $action['class'] ) : '';
						$text     = isset( $action['text'] ) ? esc_html( $action['text'] ) : '';
						echo sprintf( '<a href="%s" class="%s">%s</a>', $href, $cssClass, $text );
					}
					?>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

