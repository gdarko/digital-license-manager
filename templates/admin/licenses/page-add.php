<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Add license', 'digital-license-manager'); ?></h1>
<hr class="wp-header-end">

<div class="postbox">
    <div class="inside">
        <form method="post" action="<?php echo esc_html(admin_url('admin-post.php'));?>">
            <input type="hidden" name="action" value="dlm_add_license_key">
		    <?php wp_nonce_field('dlm_add_license_key'); ?>

            <table class="form-table">
                <tbody>
                <!-- LICENSE KEY -->
                <tr scope="row">
                    <th scope="row"><label for="single__license_key"><?php esc_html_e('License key', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="license_key" id="single__license_key" class="regular-text" type="text">
                        <p class="description"><?php esc_html_e('The license key will be encrypted before it is stored inside the database.', 'digital-license-manager');?></p>
                    </td>
                </tr>

                <!-- EXPIRES AT -->
                <tr scope="row">
                    <th scope="row"><label for="single__expires_at"><?php esc_html_e('Expires at', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="expires_at" id="single__expires_at" class="regular-text" type="text">
                        <p class="description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
                            <?php esc_html_e('The exact date at midnight UTC that this license key expires on. Leave blank if the license key does not expire.', 'digital-license-manager');?>
                        </p>
                    </td>
                </tr>

                <!-- TIMES ACTIVATED MAX -->
                <tr scope="row">
                    <th scope="row"><label for="single__activations_limit"><?php esc_html_e('Max activations', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="activations_limit" id="single__activations_limit" class="regular-text" type="number">
                        <p class="description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
                            <?php esc_html_e( 'Define how many times the license can be marked as "activated". Leave blank for unlimited activations.', 'digital-license-manager' ); ?>
                        </p>
                    </td>
                </tr>

                <!-- STATUS -->
                <tr scope="row">
                    <th scope="row"><label for="edit__status"><?php esc_html_e('Status', 'digital-license-manager');?></label></th>
                    <td>
                        <select id="edit__status" name="status" class="regular-text">
						    <?php foreach($statusOptions as $option): ?>
                                <option value="<?php echo esc_html($option['value']); ?>"><?php echo esc_html($option['name']); ?></option>
						    <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <!-- PRODUCT -->
                <tr scope="row">
                    <th scope="row"><label for="single__product"><?php esc_html_e('Product', 'digital-license-manager');?></label></th>
                    <td>
                        <select name="product_id" id="single__product" class="regular-text"></select>
                        <p class="description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
			                <?php esc_html_e('The product to which the license keys will be assigned. Useful if selling from stock.', 'digital-license-manager');?>
                        </p>
                    </td>
                </tr>


                <!-- ORDER -->
                <tr scope="row">
                    <th scope="row"><label for="single__order"><?php esc_html_e('Order', 'digital-license-manager');?></label></th>
                    <td>
                        <select name="order_id" id="single__order" class="regular-text"></select>
                        <p class="description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
                            <?php esc_html_e('The order to which the license keys will be assigned.', 'digital-license-manager');?>
                        </p>
                    </td>
                </tr>

                <!-- CUSTOMER -->
                <tr scope="row">
                    <th scope="row"><label for="single__user"><?php esc_html_e('Customer', 'digital-license-manager');?></label></th>
                    <td>
                        <select name="user_id" id="single__user" class="regular-text"></select>
                        <p class="description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
                            <?php esc_html_e('The user to which the license keys will be assigned.', 'digital-license-manager');?>
                        </p>
                    </td>
                </tr>

                </tbody>
            </table>

            <p class="submit">
                <input name="submit" id="single__submit" class="button button-primary" value="<?php esc_html_e('Create' ,'digital-license-manager');?>" type="submit">
            </p>
        </form>
    </div>
</div>
