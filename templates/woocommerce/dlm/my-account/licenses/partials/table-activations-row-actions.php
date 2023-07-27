<?php
/**
 * The template for the activation row actions in single license page in "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/dlm/licenses/partials/table-activations-row-actions.php.
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
 * @var LicenseActivation $activation
 * @var WC_Order $order
 * @var WC_Product $product
 * @var array $rowActions
 */

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation;

?>

<form action="<?php echo home_url(); ?>" method="POST">
    <input type="hidden" name="dlm_action" value="activation_row_actions">
    <input type="hidden" name="dlm_nonce" value="<?php echo wp_create_nonce( 'dlm_account' ); ?>">
    <input type="hidden" name="license" value="<?php echo esc_attr( $license->getDecryptedLicenseKey() ); ?>">
    <input type="hidden" name="activation" value="<?php echo esc_attr( $activation->getToken() ); ?>">
    <ul class="dlm-list-inline">
		<?php foreach ( $rowActions as $rowAction ): ?>
			<?php
			$params   = apply_filters( 'dlm_myaccount_license_activation_row_action_params', [ 'class' => [ 'button' ] ], $rowAction, $license, $activation, $order, $product );
			$classes  = isset( $params['classes'] ) ? $params['classes'] : [];
			$disabled = false;
			if ( isset( $params['disabled'] ) && $params['disabled'] ) {
				$classes[] = 'disabled';
				$disabled  = true;
			}
			?>
			<?php if ( ! empty( $rowAction['href'] ) ): ?>
                <a href="<?php echo esc_url( $rowAction['href'] ); ?>"
                   title="<?php echo isset( $params['title'] ) ? esc_attr( $params['title'] ) : ''; ?>"
                   class="<?php echo isset( $params['class'] ) ? esc_attr( $params['class'] ) : ''; ?>">
					<?php echo esc_html( $rowAction['text'] ); ?>
                </a>
			<?php else: ?>
                <button type="submit"
					<?php if ( isset( $rowAction['confirm'] ) && $rowAction['confirm'] ): ?>
                        onclick="return confirm('<?php _e( 'Are you sure you want to do this? This action can not be revered.', 'digital-license-manager' ); ?>')"
					<?php endif; ?>
                        name="<?php echo esc_attr( $rowAction['id'] ); ?>"
                        title="<?php echo isset( $params['title'] ) ? esc_attr( $params['title'] ) : ''; ?>"
                        value="<?php echo isset( $params['value'] ) ? esc_attr( $params['value'] ) : 1; ?>"
                        class="<?php echo isset( $params['class'] ) ? esc_attr( implode( ' ', $params['class'] ) ) : ''; ?>"
					<?php disabled( true, $disabled ); ?> >
					<?php echo esc_html( $rowAction['text'] ); ?>
                </button>
			<?php endif; ?>
		<?php endforeach; ?>
    </ul>
</form>
