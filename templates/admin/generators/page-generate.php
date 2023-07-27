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

use IdeoLogix\DigitalLicenseManager\Database\Models\Generator;

defined( 'ABSPATH' ) || exit;

/**
 * @var Generator[] $generatorsDropdown
 * @var array $statusOptions
 */

?>

<h1 class="wp-heading-inline"><?php esc_html_e( 'Generate Licenses', 'digital-license-manager' ); ?></h1>
<hr class="wp-header-end">

<div class="postbox">

    <div class="inside">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="dlm_generate_license_keys">
			<?php wp_nonce_field( 'dlm_generate_license_keys' ); ?>

            <table class="form-table">
                <tbody>
                <!-- GENERATOR -->
                <tr scope="row">
                    <th scope="row">
                        <label for="generate__generator"><?php esc_html_e( 'Generator', 'digital-license-manager' ); ?></label>
                        <span class="text-danger">*</span>
                    </th>
                    <td>
                        <select id="generate__generator" name="generator_id" class="regular-text">
							<?php foreach ( $generatorsDropdown as $generator ): ?>
                                <option value="<?php esc_attr_e( $generator->getId() ); ?>"><?php esc_attr_e( $generator->getName() ); ?></option>
							<?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'The selected generator\'s rules will be used to generate the license keys.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- AMOUNT -->
                <tr scope="row">
                    <th scope="row"><label for="generate__amount"><?php esc_html_e( 'Amount', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <input name="amount" id="generate__amount" class="regular-text" type="number">
                        <p class="description"><?php esc_html_e( 'Define how many license keys will be generated.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- AMOUNT -->
                <tr scope="row">
                    <th scope="row">
                        <label for="valid__for"><?php esc_html_e( 'Valid for', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <input name="valid_for" id="valid__for" class="regular-text" type="number">
                        <p class="description"><?php esc_html_e( 'Define in days how much time the license will be valid after purchase from stock. (Applies to orders from stock)', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- STATUS -->
                <tr scope="row">
                    <th scope="row"><label for="edit__status"><?php esc_html_e( 'Status', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <select id="edit__status" name="status" class="regular-text">
			                <?php foreach ( $statusOptions as $option ): ?>
                                <option value="<?php echo esc_html( $option['value'] ); ?>"><?php echo esc_html( $option['name'] ); ?></option>
			                <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Define the initial license status. Set "Active" to make this license available for stock purchases.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- PRODUCT -->
                <tr scope="row">
                    <th scope="row"><label for="generate__product"><?php esc_html_e( 'Product', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <select name="product_id" id="generate__product" class="regular-text"></select>
                        <p class="description"><?php esc_html_e( 'The product to which the license keys will be assigned.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                <!-- ORDER -->
                <tr scope="row">
                    <th scope="row"><label for="generate__order"><?php esc_html_e( 'Order', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <select name="order_id" id="generate__order" class="regular-text"></select>
                        <p class="description"><?php esc_html_e( 'The order to which the license keys will be assigned.', 'digital-license-manager' ); ?></p>
                    </td>
                </tr>

                </tbody>
            </table>

			<?php submit_button( __( 'Generate', 'digital-license-manager' ) ); ?>

        </form>
    </div>
</div>
