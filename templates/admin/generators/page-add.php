<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2023  Darko Gjorgjijoski. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

defined('ABSPATH') || exit;
?>

<h1 class="wp-heading-inline"><?php esc_html_e('Add new generator', 'digital-license-manager'); ?></h1>
<hr class="wp-header-end">

<div class="postbox">
    <div class="inside">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')) ;?>">
            <input type="hidden" name="action" value="dlm_create_generators">
		    <?php wp_nonce_field('dlm_create_generators'); ?>

            <table class="form-table">
                <tbody>
                <!-- NAME -->
                <tr scope="row">
                    <th scope="row">
                        <label for="name"><?php esc_html_e('Name', 'digital-license-manager');?>
                            <span class="text-danger">*</span></label>
                    </th>
                    <td>
                        <input name="name" id="name" class="regular-text" type="text">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Required.', 'digital-license-manager');?></strong>
                            <span><?php esc_html_e('A short name to describe the generator.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- CHARSET -->
                <tr scope="row">
                    <th scope="row">
                        <label for="charset"><?php esc_html_e('Character map', 'digital-license-manager');?></label>
                        <span class="text-danger">*</span>
                    </th>
                    <td>
                        <input name="charset" id="charset" class="regular-text" type="text">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Required.', 'digital-license-manager');?></strong>
                            <span><?php _e('i.e. for "12-AB-34-CD" the character map is <kbd>ABCD1234</kbd>.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- NUMBER OF CHUNKS -->
                <tr scope="row">
                    <th scope="row">
                        <label for="chunks"><?php esc_html_e('Number of chunks', 'digital-license-manager');?></label>
                        <span class="text-danger">*</span>
                    </th>
                    <td>
                        <input name="chunks" id="chunks" class="regular-text" type="text">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Required.', 'digital-license-manager');?></strong>
                            <span><?php _e('i.e. for "12-AB-34-CD" the number of chunks is <kbd>4</kbd>.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- CHUNK LENGTH -->
                <tr scope="row">
                    <th scope="row">
                        <label for="chunk_length"><?php esc_html_e('Chunk length', 'digital-license-manager');?></label>
                        <span class="text-danger">*</span>
                    </th>
                    <td>
                        <input name="chunk_length" id="chunk_length" class="regular-text" type="text">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Required.', 'digital-license-manager');?></strong>
                            <span><?php _e('i.e. for "12-AB-34-CD" the chunk length is <kbd>2</kbd>.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- SEPARATOR -->
                <tr scope="row">
                    <th scope="row"><label for="separator"><?php esc_html_e('Separator', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="separator" id="separator" class="regular-text" type="text">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
                            <span><?php _e('i.e. for "12-AB-34-CD" the separator is <kbd>-</kbd>.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- PREFIX -->
                <tr scope="row">
                    <th scope="row"><label for="prefix"><?php esc_html_e('Prefix', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="prefix" id="prefix" class="regular-text" type="text">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
                            <span><?php _e('Adds a word at the start (separator <strong>not</strong> included), i.e. <kbd><strong>PRE-</strong>12-AB-34-CD</kbd>.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- SUFFIX -->
                <tr scope="row">
                    <th scope="row"><label for="suffix"><?php esc_html_e('Suffix', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="suffix" id="suffix" class="regular-text" type="text">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
                            <span><?php _e('Adds a word at the end (separator <strong>not</strong> included), i.e. <kbd>12-AB-34-CD<strong>-SUF</strong></kbd>.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>

                <!-- TIMES ACTIVATED MAX -->
                <tr scope="row">
                    <th scope="row"><label><?php esc_html_e('Max activations', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="activations_limit" id="activations_limit" class="regular-text" type="number">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
			                <?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'digital-license-manager');?>
                        </p>
                    </td>
                </tr>

                <!-- EXPIRES IN -->
                <tr scope="row">
                    <th scope="row"><label for="expires_in"><?php esc_html_e('Expires in', 'digital-license-manager');?></label></th>
                    <td>
                        <input name="expires_in" id="expires_in" class="regular-text" type="text">
                        <p class="description" id="tagline-description">
                            <strong><?php esc_html_e('Optional.', 'digital-license-manager');?></strong>
                            <span><?php esc_html_e('The number of days for which the license key is valid after purchase. Leave blank if it doesn\'t expire.', 'digital-license-manager');?></span>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <input name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Create' ,'digital-license-manager');?>" type="submit">
            </p>
        </form>
    </div>
</div>
