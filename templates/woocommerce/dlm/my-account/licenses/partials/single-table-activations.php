<?php
/**
 * Copyright (C) 2024 Darko Gjorgjijoski <dg@darkog.com> - All Rights Reserved
 * Copyright (C) 2024 IDEOLOGIX MEDIA DOOEL <info@ideologix.com> - All Rights Reserved
 *
 * The template for the overview of all license activations on the single license page in "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/my-account/licenses/single-table-activations.php
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
 * @var bool $can_activate
 * @var int $manual_activations_enabled
 * @var array $rowActions
 * @var \IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation[] $activations
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Enums\LicensePrivateStatus;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Activations;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Controller;

?>

<div class="dlm-myaccount-license-section dlm-myaccount-license-section--license-activations">
    <div class="dlm-myaccount-license-section--header">
        <h3 class="dlm-myaccount-page-subtitle product-name"><?php esc_html_e( 'Activations', 'digital-license-manager' ); ?></h3>
		<?php if ( $manual_activations_enabled ): ?>
            <button id="dlm-myaccount-license--new-activation" class="woocommerce-button button dlm-button">
                <span class="dlm-icon-plus"></span>
				<?php echo apply_filters( 'dlm_myaccount_manual_activation_button', esc_html__( 'Activate', 'digital-license-manager' ) ); ?>
            </button>
		<?php endif; ?>
    </div>
    <div class="dlm-myaccount-license-section--content">
        <table class="dlm-myaccount-table dlm-myaccount-table--license-activations shop_table shop_table_responsive">
            <thead>
            <tr>
                <th class="table-col table-col-label"><?php esc_html_e( 'Label', 'digital-license-manager' ); ?></th>
                <th class="table-col table-col-status"><?php esc_html_e( 'Status', 'digital-license-manager' ); ?></th>
                <th class="table-col table-col-source"><?php esc_html_e( 'Source', 'digital-license-manager' ); ?></th>
                <th class="table-col table-col-date"><?php esc_html_e( 'Date', 'digital-license-manager' ); ?></th>
			    <?php if ( ! empty( $rowActions ) ): ?>
                    <th class="table-col table-col-actions"></th>
			    <?php endif; ?>
            </tr>
            </thead>
            <tbody>
		    <?php if ( count( $activations ) > 0 ): ?>
			    <?php foreach ( $activations as $activation ): ?>
                    <tr>
                        <td class="table-col table-col-label" data-title="<?php esc_html_e( 'Label', 'digital-license-manager' ); ?>">
						    <?php
						    $label = $activation->getLabel();
						    if ( empty( $label ) ) {
							    $label = substr( $activation->getToken(), 0, 12 );
						    }
						    echo esc_html( $label );
						    ?>
                        </td>
                        <td class="table-col table-col-status" data-title="<?php esc_html_e( 'Status', 'digital-license-manager' ); ?>">
						    <?php
						    if ( $activation->getDeactivatedAt() ) {
							    echo wp_kses(
								    LicensePrivateStatus::statusToHtml( 'disabled', [
									    'style' => 'inline',
									    'text'  => esc_html__( 'Disabled', 'digital-license-manager' )
								    ] ),
								    \IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags()
							    );
						    } else {
							    echo wp_kses(
								    LicensePrivateStatus::statusToHtml( 'delivered', [
									    'style' => 'inline',
									    'text'  => esc_html__( 'Enabled', 'digital-license-manager' )
								    ] ),
								    \IdeoLogix\DigitalLicenseManager\Utils\SanitizeHelper::ksesAllowedHtmlTags()
							    );
						    }
						    ?>
                        </td>
                        <td class="table-col table-col-source" data-title="<?php esc_html_e( 'Source', 'digital-license-manager' ); ?>">
						    <?php
						    echo esc_html( ActivationSource::format( $activation->getSource() ) );
						    ?>
                        </td>
                        <td class="table-col table-col-date" data-title="<?php esc_html_e( 'Date', 'digital-license-manager' ); ?>">
						    <?php

						    if ( $activation->getCreatedAt() ) {
							    try {
								    $date = new \DateTime( $activation->getCreatedAt() );
								    printf( '<b>%s</b>', $date->format( $date_format ) );
							    } catch ( Exception $e ) {
								    esc_html_e( 'N/A', 'digital-license-manager' );
							    }
						    } else {
							    esc_html_e( 'N/A', 'digital-license-manager' );
						    }
						    ?>
                        </td>
					    <?php if ( ! empty( $rowActions ) ): ?>
                            <td class="table-col table-col-actions">
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
                    <td class="table-col table-col-404" colspan="5">
                        <p><?php esc_html_e( 'No activations found.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

		    <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

<?php if ( $manual_activations_enabled ): ?>
    <div class="modal micromodal-slide" id="dlm-manual-activation-add" aria-hidden="true">
        <div class="modal__overlay" tabindex="-1" data-micromodal-close>
            <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="dlm-manual-activation-add--title">
                <header class="modal__header">
                    <h2 class="modal__title" id="dlm-manual-activation-add--title">
						<?php esc_html_e( 'New Activation', 'digital-license-manager' ); ?>
                    </h2>
                    <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
                </header>
                <form method="POST" action="<?php echo esc_url( \IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\MyAccount::getProcessingEndpointUrl() ); ?>" id="dlm-manual-activation-add--form">
                    <main class="modal__content" id="dlm-manual-activation-add--content">
                        <div class="dlm-form-row">
                            <label for="label"><?php esc_html_e( 'Label', 'digital-license-manager' ); ?></label>
                            <input type="text" id="label" name="label"/>
                        </div>
                    </main>
                    <footer class="modal__footer">
                        <input type="hidden" name="dlm_action" value="manual_activation">
                        <input type="hidden" name="dlm_nonce" value="<?php echo wp_create_nonce( Activations::NONCE ); ?>">
                        <input type="hidden" name="license_id" value="<?php echo esc_attr( $license->getId() ); ?>">
                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Create', 'digital-license-manager' ); ?></button>
                    </footer>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>