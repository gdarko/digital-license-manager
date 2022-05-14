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
 * @var LicenseResourceModel $license
 * @var WC_Product $product
 * @var WC_Order $order
 * @var string $date_format
 * @var string $license_key
 */


use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\LicenseActivation as LicenseActivationResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Settings;


/* @var LicenseActivationResourceModel[] */
$notAvailable              = __( 'N/A', 'digital-license-manager' );
$activations               = $license->getActivations();

$isExpired      = $license->isExpired();
$actionsEnabled = apply_filters( 'dlm_myaccount_license_activation_row_actions_enabled', false );
?>


<h3 class="product-name"><?php _e( 'Activations', 'digital-license-manager-pro' ); ?></h3>

<table class="shop_table shop_table_responsive my_account_orders">
    <thead>
    <tr>
        <th class="table-col table-col-label"><?php _e( 'Label', 'digital-license-manager-pro' ); ?></th>
        <th class="table-col table-col-status"><?php _e( 'Status', 'digital-license-manager-pro' ); ?></th>
        <th class="table-col table-col-source"><?php _e( 'Source', 'digital-license-manager-pro' ); ?></th>
        <th class="table-col table-col-date"><?php _e( 'Date', 'digital-license-manager-pro' ); ?></th>
	    <?php if ( $actionsEnabled ): ?>
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
					echo $label;
					?>
                </td>
                <td>
					<?php
					$deactivatedAt = $activation->getDeactivatedAt();
					if ( $deactivatedAt ) {
						echo sprintf(
							'<div class="dlm-status inactive">%s</div>',
							__( 'Inactive', 'digital-license-manager' )
						);
					} else {
						echo sprintf(
							'<div class="dlm-status delivered">%s</div>',
							__( 'Active', 'digital-license-manager' )
						);
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
							echo $notAvailable;
						}
					} else {
						echo $notAvailable;
					}
					?>
                </td>
				<?php if ( $actionsEnabled ): ?>
                    <td>
                        <?php do_action('dlm_myaccount_license_activation_row_actions',  $license, $activation, $license_key ); ?>
                    </td>
				<?php endif; ?>
            </tr>
		<?php endforeach; ?>

	<?php else: ?>
        <tr>
            <td colspan="4">
                <p><?php _e( 'No activations found.', 'digital-license-manager-pro' ); ?></p>
            </td>
        </tr>
	<?php endif; ?>

    </tbody>
</table>

