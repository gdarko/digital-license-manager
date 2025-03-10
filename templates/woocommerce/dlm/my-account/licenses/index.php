<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template for the overview of all customer licenses, across all orders, inside "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/my-account/licenses/index.php
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
 * @var $licenses  License[]
 * @var $page        int
 * @var $date_format  string
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Controller;
use IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper;

defined( 'ABSPATH' ) || exit; ?>

<?php do_action( 'dlm_myaccount_licenses_index_page_start', $licenses ); ?>

<?php if ( ! empty( $licenses ) ): ?>

    <div class="dlm-myaccount-product-licenses">

		<?php foreach ( $licenses as $productId => $licenseData ): ?>

			<?php $product = wc_get_product( $productId ); ?>

            <div class="dlm-myaccount-product-licenses--row">

                <h3 class="dlm-myaccount-page-subtitle product-name">
					<?php if ( $product ): ?>
                        <a href="<?php echo esc_url( get_post_permalink( $productId ) ); ?>">
                            <span><?php echo wp_kses( $licenseData['name'], SanitizeHelper::ksesAllowedHtmlTags() ); ?></span>
                        </a>
					<?php else: ?>
                        <span><?php echo esc_html__( 'Product', 'digital-license-manager' ) . ' #' . $productId; ?></span>
					<?php endif; ?>
                </h3>

                <table class="dlm-myaccount-table dlm-myaccount-table--licenses shop_table shop_table_responsive">
                    <thead>
                    <tr>
                        <th class="table-col table-col-license-key license-key"><?php esc_html_e( 'License key', 'digital-license-manager' ); ?></th>
                        <th class="table-col table-col-activations activation"><?php esc_html_e( 'Activations', 'digital-license-manager' ); ?></th>
                        <th class="table-col table-col-status status"><?php esc_html_e( 'Status', 'digital-license-manager' ); ?></th>
                        <th class="table-col table-col-actions actions"></th>
                    </tr>
                    </thead>

                    <tbody>

					<?php
					/** @var License $license */
					foreach ( $licenseData['licenses'] as $license ):

						$timesActivated = $license->getTimesActivated() ? $license->getTimesActivated() : '0';
						$activationsLimit = $license->getActivationsLimit() ? $license->getActivationsLimit() : '&infin;';
						$order = wc_get_order( $license->getOrderId() );
						$decrypted = $license->getDecryptedLicenseKey();
						if ( is_wp_error( $decrypted ) ) {
							$decrypted = '';
							continue;
						}

						$actions = array();
						if ( ! empty( $order ) ) {
							$actions[10] = array(
								'href'  => esc_url( $order->get_view_order_url() ),
								'class' => 'button',
								'text'  => esc_html__( 'Order', 'digital-license-manager' ),
							);
						}

						$actions = apply_filters( 'dlm_myaccount_licenses_row_actions', $actions, $license, $decrypted, $order );

						ksort( $actions );

						?>
                        <tr>
                            <td class="table-col table-col-license-key" data-title="<?php esc_html_e( 'License key', 'digital-license-manager' ); ?>">
								<?php
								echo wc_get_template_html( 'dlm/my-account/licenses/partials/license-key.php', array(
									'license' => $license,
								), '', Controller::getTemplatePath() )
								?>
                            </td>
                            <td class="table-col table-col-activations" data-title="<?php esc_html_e( 'Activations', 'digital-license-manager' ); ?>">
                                <span><?php echo esc_html( $timesActivated ); ?></span>
                                <span>/</span>
                                <span><?php echo esc_attr( $activationsLimit ); ?></span>
                            </td>
                            <td class="table-col table-col-status" data-title="<?php esc_html_e( 'Status', 'digital-license-manager' ); ?>">
								<?php
								echo wc_get_template_html( 'dlm/my-account/licenses/partials/license-status.php', array(
									'license' => $license,
								), '', Controller::getTemplatePath() );
								?>
                            </td>
                            <td class="table-col table-col-actions" data-title="<?php esc_html_e( 'Actions', 'digital-license-manager' ); ?>">
                                <div class="dlm-myaccount-license-key-actions">
	                                <?php
	                                foreach ( $actions as $key => $action ) {
		                                $href     = isset( $action['href'] ) ? esc_url( $action['href'] ) : '';
		                                $cssClass = isset( $action['class'] ) ? esc_attr( $action['class'] ) : '';
		                                $text     = isset( $action['text'] ) ? esc_html( $action['text'] ) : '';
		                                $title    = isset( $action['title'] ) ? 'title="' . esc_attr( $action['title'] ) . '"' : '';
		                                echo wp_kses( sprintf( '<a href="%s" %s class="%s">%s</a>', $href, $title, $cssClass, $text ), SanitizeHelper::ksesAllowedHtmlTags() );
	                                }
	                                ?>
                                </div>
                            </td>
                        </tr>
					<?php endforeach; ?>

                    </tbody>
                </table>

            </div>

		<?php endforeach; ?>

    </div>

<?php else: ?>

    <div class="dlm-myaccount-no-items">
        <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
			<?php esc_html_e( 'No licenses available yet', 'digital-license-manager' ); ?>
        </div>
    </div>

<?php endif; ?>

<?php do_action( 'dlm_myaccount_licenses_index_page_end', $licenses ); ?>