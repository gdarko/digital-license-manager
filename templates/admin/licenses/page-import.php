<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Import Licenses', 'digital-license-manager'); ?></h1>
<hr class="wp-header-end">

<div class="postbox">
    <div class="inside">
        <form method="post" action="<?php echo esc_html(admin_url('admin-post.php')) ;?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="dlm_import_license_keys">
		    <?php wp_nonce_field('dlm_import_license_keys'); ?>

            <table class="form-table">
                <tbody>

                <!-- SOURCE -->
                <tr class="row">
                    <th class="row"><?php esc_html_e('Source', 'digital-license-manager'); ?></th>
                    <td>
                        <label style="display: block; margin-bottom: 1em;">
                            <input type="radio" id="bulk__type_file" class="bulk__type regular-text" name="source" value="file" checked="checked">
                            <span><?php _e('File upload', 'digital-license-manager'); ?></span>
                        </label>
                        <label style="display: block;">
                            <input type="radio" id="bulk__type_clipboard" class="bulk__type regular-text" name="source" value="clipboard">
                            <span><?php _e('Clipboard', 'digital-license-manager'); ?></span>
                        </label>
                        <p class="description" style="margin-top: 1em;"><?php _e('You can either upload a file containing the license keys, or copy-paste them into a text field.', 'digital-license-manager'); ?></p>
                    </td>
                </tr>

                <!-- FILE -->
                <tr scope="row" id="bulk__source_file" class="bulk__source_row">
                    <th scope="row"><label for="bulk__file"><?php esc_html_e('File', 'digital-license-manager'); ?> <kbd>CSV</kbd> <kbd>TXT</kbd></label></th>
                    <td>
                        <input name="file" id="bulk__file" class="regular-text" type="file" accept=".csv,.txt">
                        <p class="description">
                            <b class="text-danger"><?php esc_html_e('Important', 'digital-license-manager'); ?>:</b>
                            <span><?php esc_html_e('One line per license key.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- Clipboard -->
                <tr scope="row" id="bulk__source_clipboard" class="bulk__source_row hidden">
                    <th scope="row"><label for="bulk__clipboard"><?php esc_html_e('License keys', 'digital-license-manager'); ?></label></th>
                    <td>
                        <textarea name="clipboard" id="bulk__clipboard" cols="49" rows="10" ></textarea>
                        <p class="description">
                            <b class="text-danger"><?php esc_html_e('Important', 'digital-license-manager'); ?>:</b>
                            <span><?php esc_html_e('One line per license key.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- VALID FOR -->
                <tr scope="row">
                    <th scope="row"><label for="bulk__valid_for"><?php esc_html_e('Valid for (days)', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="valid_for" id="bulk__valid_for" class="regular-text" type="text">
                        <p class="description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire.', 'digital-license-manager');?></p>
                    </td>
                </tr>

                <!-- TIMES ACTIVATED MAX -->
                <tr scope="row">
                    <th scope="row"><label for="bulk__activations_limit"><?php esc_html_e('Max activations', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="activations_limit" id="bulk__activations_limit" class="regular-text" type="number">
                        <p class="description"><?php esc_html_e( 'Define how many times the license can be marked as "activated". Leave blank for unlimited activations.', 'digital-license-manager' ); ?></p>
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

                <!-- ORDER -->
                <tr scope="row">
                    <th scope="row"><label for="bulk__order"><?php esc_html_e('Order', 'digital-license-manager');?></label></th>
                    <td>
                        <select name="order_id" id="bulk__order" class="regular-text"></select>
                        <p class="description"><?php esc_html_e('The order to which the license keys will be assigned.', 'digital-license-manager');?></p>
                    </td>
                </tr>

                <!-- PRODUCT -->
                <tr scope="row">
                    <th scope="row"><label for="bulk__product"><?php esc_html_e('Product', 'digital-license-manager');?></label></th>
                    <td>
                        <select name="product_id" id="bulk__product" class="regular-text"></select>
                        <p class="description"><?php esc_html_e('The product to which the license keys will be assigned.', 'digital-license-manager');?></p>
                    </td>
                </tr>

                <!-- CUSTOMER -->
                <tr scope="row">
                    <th scope="row"><label for="single__user"><?php esc_html_e('Customer', 'digital-license-manager');?></label></th>
                    <td>
                        <select name="user_id" id="single__user" class="regular-text"></select>
                        <p class="description"><?php esc_html_e('The user to which the license keys will be assigned.', 'digital-license-manager');?></p>
                    </td>
                </tr>

                </tbody>
            </table>

            <p class="submit">
                <input name="submit" id="bulk__submit" class="button button-primary" value="<?php esc_html_e('Import' ,'digital-license-manager');?>" type="submit">
            </p>
        </form>
    </div>
</div>
