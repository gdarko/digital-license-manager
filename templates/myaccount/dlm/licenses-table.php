<?php
/**
 * The template for the overview of all customer licenses, across all orders, inside "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dlm/licenses-table.php.
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
 * @var $licenseKeys array
 * @var $page        int
 * @var $dateFormat  string
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Utils\Moment;

defined( 'ABSPATH' ) || exit; ?>

<?php if ( ! empty( $licenseKeys ) ): ?>


	<?php foreach ( $licenseKeys as $productId => $licenseKeyData ): ?>

		<?php $product = wc_get_product( $productId ); ?>

        <h3 class="product-name">
			<?php if ( $product ): ?>
                <a href="<?php echo esc_url( get_post_permalink( $productId ) ); ?>">
                    <span><?php echo( $licenseKeyData['name'] ); ?></span>
                </a>
			<?php else: ?>
                <span><?php echo __( 'Product', 'digital-license-manager' ) . ' #' . $productId; ?></span>
			<?php endif; ?>
        </h3>

        <table class="shop_table shop_table_responsive my_account_orders">
            <thead>
            <tr>
                <th class="license-key"><?php _e( 'License key', 'digital-license-manager' ); ?></th>
                <th class="activation"><?php _e( 'Activations', 'digital-license-manager' ); ?></th>
                <th class="valid-until"><?php _e( 'Expires', 'digital-license-manager' ); ?></th>
                <th class="status"><?php _e( 'Status', 'digital-license-manager' ); ?></th>
                <th class="actions"></th>
            </tr>
            </thead>

            <tbody>

			<?php
			/** @var LicenseResourceModel $license */
			foreach ( $licenseKeyData['licenses'] as $license ):

				$timesActivated = $license->getTimesActivated() ? $license->getTimesActivated() : '0';
				$activationsLimit = $license->getActivationsLimit() ? $license->getActivationsLimit() : '&infin;';
				$order = wc_get_order( $license->getOrderId() );
				$decrypted = $license->getDecryptedLicenseKey();
				if ( is_wp_error( $decrypted ) ) {
					$decrypted = '';
					continue;
				}

				$actions = apply_filters( 'dlm_myaccount_licenses_row_actions', array(
					10 => array(
						'href'  => esc_url( $order->get_view_order_url() ),
						'class' => 'button',
						'text'  => __( 'Order', 'digital-license-manager' ),
					)
				), $license, $decrypted, $order );

				ksort( $actions );

				?>
                <tr>
                    <td><span class="dlm-myaccount-license-key"><?php echo $decrypted; ?></span></td>
                    <td>
                        <span><?php esc_html_e( $timesActivated ); ?></span>
                        <span>/</span>
                        <span><?php echo esc_attr( $activationsLimit ); ?></span>
                    </td>
                    <td>
						<?php echo Moment::toHtml( $license->getExpiresAt(), true ); ?>
                    </td>
                    <td>
						<?php echo LicenseStatus::toHtml( $license ); ?>
                    </td>
                    <td class="license-key-actions">
						<?php
						foreach ( $actions as $key => $action ) {
							$href     = isset( $action['href'] ) ? $action['href'] : '';
							$cssClass = isset( $action['class'] ) ? $action['class'] : '';
							$text     = isset( $action['text'] ) ? $action['text'] : '';
							$title    = isset( $action['title'] ) ? 'title="' . $action['title'] . '"' : '';
							echo sprintf( '<a href="%s" %s class="%s">%s</a>', $href, $title, $cssClass, $text );
						}
						?>
                    </td>
                </tr>
			<?php endforeach; ?>

            </tbody>
        </table>

	<?php endforeach; ?>

<?php else: ?>

    <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
		<?php _e( 'No licenses available yet', 'digital-license-manager' ); ?>
    </div>

<?php endif; ?>
