<?php

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;

defined( 'ABSPATH' ) || exit;

if ( empty( $expiresAt ) || '0000-00-00 00:00:00' === $expiresAt ) {
	$expiresAt = '';
}

/** @var LicenseResourceModel $license */
?>

<h1 class="wp-heading-inline"><?php esc_html_e( 'Edit license', 'digital-license-manager' ); ?></h1>
<hr class="wp-header-end">

<div class="postbox">
    <div class="inside">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="source" value="<?php echo esc_html( $license->getSource() ); ?>">
            <input type="hidden" name="action" value="dlm_update_license_key">
			<?php wp_nonce_field( 'dlm_update_license_key' ); ?>

            <table class="form-table">
                <tbody>

                <!-- LICENSE ID -->
                <tr scope="row">
                    <th scope="row">
                        <label for="edit__license_id"><?php esc_html_e( 'ID', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <input name="license_id" id="edit__license_id" class="regular-text" type="text" value="<?php echo esc_html( $license->getId() ); ?>" readonly>
                    </td>
                </tr>

                <!-- LICENSE KEY -->
                <tr scope="row">
                    <th scope="row">
                        <label for="edit__license_key"><?php esc_html_e( 'License key', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <input name="license_key" id="edit__license_key" class="regular-text" type="text" value="<?php echo esc_html( $licenseKey ); ?>">
                        <p class="description"><?php esc_html_e( 'The license key will be encrypted before it is stored inside the database.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- EXPIRES AT -->
                <tr scope="row">
                    <th scope="row">
                        <label for="edit__expires_at"><?php esc_html_e( 'Expires at', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <input name="expires_at" id="edit__expires_at" class="regular-text" type="text" value="<?php echo esc_html( $expiresAt ); ?>">
                        <p class="description"><?php esc_html_e( 'The exact date at midnight UTC that this license key expires on. Leave blank if the license key does not expire. Cannot be used at the same time as the "Valid for (days)" field.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- TIMES ACTIVATED MAX -->
                <tr scope="row">
                    <th scope="row">
                        <label for="edit__activations_limit"><?php esc_html_e( 'Maximum activation count', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <input name="activations_limit" id="edit__activations_limit" class="regular-text" type="number" value="<?php echo esc_html( $license->getActivationsLimit() ); ?>">
                        <p class="description"><?php esc_html_e( 'Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- STATUS -->
                <tr scope="row">
                    <th scope="row">
                        <label for="edit__status"><?php esc_html_e( 'Status', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <select id="edit__status" name="status" class="regular-text">
							<?php foreach ( $statusOptions as $option ): ?>
                                <option value="<?php echo esc_html( $option['value'] ); ?>" <?php selected( $option['value'], $license->getStatus(), true ); ?>>
                                    <span><?php echo esc_html( $option['name'] ); ?></span>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <!-- ORDER -->
                <tr scope="row">
                    <th scope="row">
                        <label for="edit__order"><?php esc_html_e( 'Order', 'digital-license-manager' ); ?></label></th>
                    <td>
                        <select name="order_id" id="edit__order" class="regular-text">
							<?php
							if ( $license->getOrderId() ) {
								/** @var WC_Order $order */
								$order = wc_get_order( $license->getOrderId() );
								if ( $order ) {
									echo sprintf(
										'<option value="%d" selected="selected">#%d %s <%s></option>',
										$order->get_id(),
										$order->get_id(),
										$order->get_formatted_billing_full_name(),
										$order->get_billing_email()
									);
								}
							}
							?>
                        </select>
                        <p class="description"><?php esc_html_e( 'The order to which the license keys will be assigned.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- PRODUCT -->
                <tr scope="row">
                    <th scope="row">
                        <label for="edit__product"><?php esc_html_e( 'Product', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <select name="product_id" id="edit__product" class="regular-text">
							<?php
							if ( $license->getProductId() ) {
								/** @var WC_Order $order */
								$product = wc_get_product( $license->getProductId() );
								if ( $product ) {
									echo sprintf(
										'<option value="%d" selected="selected">(#%d) %s</option>',
										$product->get_id(),
										$product->get_id(),
										$product->get_formatted_name()
									);
								}
							}
							?>
                        </select>
                        <p class="description"><?php esc_html_e( 'The product to which the license keys will be assigned.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- CUSTOMER -->
                <tr scope="row">
                    <th scope="row">
                        <label for="single__user"><?php esc_html_e( 'Customer', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <select name="user_id" id="single__user" class="regular-text">
							<?php
							if ( $license->getUserId() ) {
								/** @var WP_User $user */
								$user = get_userdata( $license->getUserId() );
								if ( $user ) {
									echo sprintf(
										'<option value="%d" selected="selected">%s (#%d - %s)</option>',
										$user->ID,
										$user->user_nicename,
										$user->ID,
										$user->user_email
									);
								}
							}
							?>
                        </select>
                        <p class="description"><?php esc_html_e( 'The user to which the license keys will be assigned.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                </tbody>
            </table>

            <p class="submit">
                <button name="submit" id="edit__submit" class="button button-primary" value="1" type="submit">
	                <?php esc_html_e( 'Update', 'digital-license-manager' ); ?>
                </button>
            </p>
        </form>
    </div>
</div>
