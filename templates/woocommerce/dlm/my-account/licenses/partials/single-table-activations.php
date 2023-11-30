<?php
/**
 * The template for the overview of all license activations on the single license page in "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/myaccount/licenses/single-table-activations.php
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
 * @var WC_Order $order
 * @var WC_Product $product
 * @var string $date_format
 * @var string $license_key
 * @var int $manual_activations_enabled
 * @var array $rowActions
 * @var \IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation[] $activations
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Activations;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Controller;

?>

<div class="dlm-license-activations">
    <div class="dlm-header">
        <h3 class="product-name"><?php _e( 'Activations', 'digital-license-manager' ); ?></h3>
		<?php if ( $manual_activations_enabled ): ?>
            <button id="dlm-myaccount-license--new-activation" class="woocommerce-button button dlm-button">
                <span class="dlm-icon-plus"></span>
				<?php echo apply_filters( 'dlm_myaccount_manual_activation_button', __( 'Activate', 'digital-license-manager' ) ); ?>
            </button>
		<?php endif; ?>
    </div>

    <table class="shop_table shop_table_responsive my_account_orders">
        <thead>
        <tr>
            <th class="table-col table-col-label"><?php _e( 'Label', 'digital-license-manager' ); ?></th>
            <th class="table-col table-col-status"><?php _e( 'Status', 'digital-license-manager' ); ?></th>
            <th class="table-col table-col-source"><?php _e( 'Source', 'digital-license-manager' ); ?></th>
            <th class="table-col table-col-date"><?php _e( 'Date', 'digital-license-manager' ); ?></th>
			<?php if ( ! empty( $rowActions ) ): ?>
                <th class="table-col table-col-actions"></th>
			<?php endif; ?>
        </tr>
        </thead>
        <tbody>
		<?php if ( count( $activations ) > 0 ): ?>
			<?php foreach ( $activations as $activation ): ?>
                <tr>
                    <td>
						<?php
						$label = $activation->getLabel();
						if ( empty( $label ) ) {
							$label = substr( $activation->getToken(), 0, 12 );
						}
						echo esc_html( $label );
						?>
                    </td>
                    <td>
						<?php
						if ( $activation->getDeactivatedAt() ) {
							echo LicenseStatus::statusToHtml( 'disabled', [
								'style' => 'inline',
								'text'  => __( 'Not Active', 'digital-license-manager' )
							] );
						} else {
							echo LicenseStatus::statusToHtml( 'delivered', [
								'style' => 'inline',
								'text'  => __( 'Active', 'digital-license-manager' )
							] );
						}
						?>
                    </td>
                    <td>
						<?php
						echo ActivationSource::format( $activation->getSource() );
						?>
                    </td>
                    <td>
						<?php

						if ( $activation->getCreatedAt() ) {
							try {
								$date = new \DateTime( $activation->getCreatedAt() );
								printf( '<b>%s</b>', $date->format( $date_format ) );
							} catch ( Exception $e ) {
								_e( 'N/A', 'digital-license-manager' );
							}
						} else {
							_e( 'N/A', 'digital-license-manager' );
						}
						?>
                    </td>
					<?php if ( ! empty( $rowActions ) ): ?>
                        <td>
							<?php
							echo wc_get_template_html(
								'dlm/my-account/licenses/partials/table-activations-row-actions.php',
								array(
									'license'    => $license,
									'activation' => $activation,
									'rowActions' => $rowActions,
									'product'    => $product,
									'order'      => $order,
									'licenseKey' => $license_key,
								),
								'',
								Controller::getTemplatePath()
							);
							?>
                        </td>
					<?php endif; ?>
                </tr>

			<?php endforeach; ?>

		<?php else: ?>

            <tr>
                <td colspan="5">
                    <p><?php _e( 'No activations found.', 'digital-license-manager' ); ?></p>
                </td>
            </tr>

		<?php endif; ?>

        </tbody>
    </table>

	<?php if ( $manual_activations_enabled ): ?>
        <div class="modal micromodal-slide" id="dlm-manual-activation-add" aria-hidden="true">
            <div class="modal__overlay" tabindex="-1" data-micromodal-close>
                <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="dlm-manual-activation-add--title">
                    <header class="modal__header">
                        <h2 class="modal__title" id="dlm-manual-activation-add--title">
							<?php _e( 'New Activation', 'digital-license-manager' ); ?>
                        </h2>
                        <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                    </header>
                    <form method="POST" action="<?php echo esc_url( \IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\MyAccount::getProcessingEndpointUrl() ); ?>" id="dlm-manual-activation-add--form">
                        <main class="modal__content" id="dlm-manual-activation-add--content">
                            <div class="dlm-form-row">
                                <label for="label"><?php _e( 'Label' ); ?></label>
                                <input type="text" id="label" name="label"/>
                            </div>
                        </main>
                        <footer class="modal__footer">
                            <input type="hidden" name="dlm_action" value="manual_activation">
                            <input type="hidden" name="dlm_nonce" value="<?php echo wp_create_nonce( Activations::NONCE ); ?>">
                            <input type="hidden" name="license" value="<?php echo esc_attr($license->getDecryptedLicenseKey()); ?>">
                            <button type="submit" class="button button-primary"><?php _e( 'Create', 'digital-license-manager' ); ?></button>
                        </footer>
                    </form>
                </div>
            </div>
        </div>
	<?php endif; ?>
</div>

