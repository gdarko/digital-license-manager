<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template for the overview of a single license inside "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/my-account/licenses/single.php
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
 * @var License $license
 * @var string $license_key
 * @var WC_Product $product
 * @var WC_Order $order
 * @var string $date_format
 * @var string $license_key
 * @var stdClass $message
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Enums\LicensePrivateStatus;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Certificates;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Controller;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;

defined( 'ABSPATH' ) || exit;

$licenseNonce     = wp_create_nonce( 'dlm_nonce' );
$timesActivated   = $license->getTimesActivated() ? $license->getTimesActivated() : '0';
$activationsLimit = $license->getActivationsLimit() ? $license->getActivationsLimit() : '&infin;';

?>

<?php
do_action( 'dlm_myaccount_single_page_start', $license, $order, $product, $date_format, $license_key ); // DEPRECATED as of 1.7.1.
do_action( 'dlm_myaccount_licenses_single_page_start', $license, $order, $product, $date_format, $license_key );
?>

<div class="dlm-myaccount-license-section">
    <div class="dlm-myaccount-license-section--content">
        <table class="dlm-myaccount-table dlm-myaccount-table--license-details woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
            <tr class="dlm-myaccount-table-row dlm-myaccount-table-row--product">
                <th scope="row"><?php esc_html_e( 'Product', 'digital-license-manager' ); ?></th>
                <td>
					<?php if ( $product ): ?>
                        <a target="_blank" href="<?php echo esc_url( get_post_permalink( $product->get_id() ) ); ?>">
                            <span><?php echo esc_attr( $product->get_name() ); ?></span>
                        </a>
					<?php else: ?>
                        <span><?php echo esc_html( sprintf( __( 'License #%s', 'digital-license-manager' ), $license->getId() ) ); ?></span>
					<?php endif; ?>
                </td>
            </tr>
            <tr class="dlm-myaccount-table-row dlm-myaccount-table-row--license-key woocommerce-table__line-item license_keys">
                <th scope="row"><?php esc_html_e( 'License key', 'digital-license-manager' ); ?></th>
                <td>
					<?php
					echo wc_get_template_html( 'dlm/my-account/licenses/partials/license-key.php', array(
						'license' => $license,
					), '', Controller::getTemplatePath() );
					?>
                </td>
            </tr>
            <tr class="dlm-myaccount-table-row dlm-myaccount-table-row--activations woocommerce-table__line-item activations_limit">
                <th scope="row"><?php esc_html_e( 'Activations', 'digital-license-manager' ); ?></th>
                <td>
                    <p>
                        <span><?php echo esc_html( $timesActivated ); ?></span>
                        <span>/</span>
                        <span><?php echo esc_attr( $activationsLimit ); ?></span>
                    </p>
                </td>
            </tr>
            <tr class="dlm-myaccount-table-row dlm-myaccount-table-row--status woocommerce-table__line-item license_status">
                <th scope="row"><?php esc_html_e( 'Status', 'digital-license-manager' ); ?></th>
                <td>
					<?php
					echo wc_get_template_html( 'dlm/my-account/licenses/partials/license-status.php', array(
						'license' => $license,
						'inline'  => true
					), '', Controller::getTemplatePath() );
					?>
                </td>
            </tr>

			<?php if ( in_array( $license->getStatus(), [ LicensePrivateStatus::SOLD, LicensePrivateStatus::DELIVERED ] ) ): ?>
                <tr class="dlm-myaccount-table-row dlm-myaccount-table-row--valid-until woocommerce-table__line-item valid_until">
                    <th scope="row"><?php esc_html_e( 'Expires', 'digital-license-manager' ); ?></th>
                    <td class="dlm-inline-child">
						<?php
						echo wp_kses( DateFormatter::toHtml( $license->getExpiresAt(), [ 'expires' => true ] ), \IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags() );
						?>
                    </td>
                </tr>

				<?php if ( Certificates::isLicenseCertificationEnabled() ): ?>
                    <tr class="dlm-myaccount-table-row dlm-myaccount-table-row--certificate woocommerce-table__line-item certificate">
                        <th scope="row"><?php esc_html_e( 'Certificate', 'digital-license-manager' ); ?></th>
                        <td class="dlm-inline-child dlm-license-certificate">
							<?php
							echo wc_get_template_html( 'dlm/my-account/licenses/partials/single-certificate-button.php', array(
								'license' => $license,
							), '', Controller::getTemplatePath() );
							?>
                        </td>
                    </tr>
				<?php endif; ?>

			<?php endif; ?>

			<?php do_action( 'dlm_myaccount_licenses_single_page_table_details', $license, $order, $product, $date_format, $license_key ); ?>

            </tbody>

        </table>
    </div>
	<?php if ( ! empty( $order ) ): ?>
        <div class="dlm-myaccount-license-section-footer dlm-license-details--view-order">
            <p>
                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="woocommerce-button button dlm-button"><span class="dlm-icon-angle-left"></span> <?php esc_html_e( 'View Order', 'digital-license-manager' ); ?></a>
            </p>
        </div>
	<?php endif; ?>
</div>


<?php do_action( 'dlm_myaccount_licenses_single_page_end', $license, $order, $product, $date_format, $license_key ); ?>

