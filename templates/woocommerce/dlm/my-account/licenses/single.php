<?php
/**
 * The template for the overview of a single license inside "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/myaccount/licenses/single.php
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 *
 * Default variables
 *
 * @var License $license
 * @var WC_Product $product
 * @var WC_Order $order
 * @var string $date_format
 * @var string $license_key
 * @var stdClass $message
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;

defined( 'ABSPATH' ) || exit;

$licenseNonce     = wp_create_nonce( 'dlm_nonce' );
$timesActivated   = $license->getTimesActivated() ? $license->getTimesActivated() : '0';
$activationsLimit = $license->getActivationsLimit() ? $license->getActivationsLimit() : '&infin;';

?>

<?php do_action( 'dlm_myaccount_single_page_start', $license, $order, $product, $date_format, $license_key ); ?>

<h2><?php _e( 'License Details', 'digital-license-manager' ); ?></h2>

<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
    <tbody>
    <tr>
        <th scope="row"><?php _e( 'Product', 'digital-license-manager' ); ?></th>
        <td>
			<?php if ( $product ): ?>
                <a target="_blank" href="<?php echo esc_url( get_post_permalink( $product->get_id() ) ); ?>">
                    <span><?php echo esc_attr( $product->get_name() ); ?></span>
                </a>
			<?php else: ?>
                <span><?php echo sprintf( __( 'License #%s', 'digital-license-manager' ), $license->getId() ); ?></span>
			<?php endif; ?>
        </td>
    </tr>
    <tr class="woocommerce-table__line-item license_keys">
        <th scope="row"><?php _e( 'License key', 'digital-license-manager' ); ?></th>
        <td>
			<?php echo esc_html($license_key); ?>
        </td>
    </tr>
    <tr class="woocommerce-table__line-item activations_limit">
        <th scope="row"><?php _e( 'Activations', 'digital-license-manager' ); ?></th>
        <td>
            <p>
                <span><?php esc_html_e( $timesActivated ); ?></span>
                <span>/</span>
                <span><?php echo esc_attr($activationsLimit); ?></span>
            </p>
        </td>
    </tr>
    <tr class="woocommerce-table__line-item license_status">
        <th scope="row"><?php _e( 'Status', 'digital-license-manager' ); ?></th>
        <td class="dlm-inline-child dlm-license-status">
			<?php
			echo LicenseStatus::toHtml( $license, ['style' => 'inline'] );
			?>
        </td>
    </tr>
    <tr class="woocommerce-table__line-item valid_until">
        <th scope="row"><?php _e( 'Expires', 'digital-license-manager' ); ?></th>
        <td class="dlm-inline-child dlm-license-status">
			<?php
			echo DateFormatter::toHtml( $license->getExpiresAt(), ['expires' => true] );
			?>
        </td>
    </tr>

    <?php do_action( 'dlm_myaccount_licenses_single_page_table_details', $license, $order, $product, $date_format, $license_key ); ?>

    </tbody>

</table>

<p>
    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="woocommerce-button button dlm-button"><?php _e( 'View Order', 'digital-license-manager' ); ?></a>
</p>


<?php do_action( 'dlm_myaccount_licenses_single_page_end', $license, $order, $product, $date_format, $license_key ); ?>

