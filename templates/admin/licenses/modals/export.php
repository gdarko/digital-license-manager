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

$modal_id = 'dlm-license-export';

/**
 * Allowed columns
 */
$columns = \IdeoLogix\DigitalLicenseManager\Controllers\Licenses::exportColumns();
?>

<div class="modal micromodal-slide" id="<?php echo esc_attr($modal_id); ?>" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <form method="POST" action="<?php echo admin_url( 'admin-post.php' ); ?>" id="dlm-license-export-form">
            <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($modal_id); ?>-title">
                <header class="modal__header">
                    <h2 class="modal__title" id="<?php echo esc_attr($modal_id); ?>-title">
						<?php _e( 'Export Licenses', 'digital-license-manager' ); ?>
                    </h2>
                    <button class="modal__close" type="button" aria-label="Close modal" data-micromodal-close></button>
                </header>
                <main class="modal__content" id="<?php echo esc_attr($modal_id); ?>-content">
                    <div class="dlm-form-row">
                        <label><?php _e( 'Columns' ); ?></label>
						<?php foreach ( $columns as $column ): ?>
                            <p class="dlm-checkbox-row">
                                <label>
                                    <input type="checkbox" name="dlm_export_columns[]" checked value="<?php echo esc_attr($column['slug']); ?>"> <?php echo esc_html($column['name']); ?>
                                </label>
                            </p>
						<?php endforeach; ?>
                    </div>
                </main>
                <footer class="modal__footer">
					<?php wp_nonce_field( 'dlm_export_licenses' ); ?>
                    <input type="hidden" name="dlm_export_licenses">
                    <input type="hidden" name="action" value="dlm_licenses_export">
                    <button type="submit" class="button button-primary"><?php _e( 'Export', 'digital-license-manager' ); ?></button>
                    <button class="button button-secondary" type="button" data-micromodal-close aria-label="Close this dialog window"><?php _e( 'Close', 'digital-license-manager' ); ?></button>
                </footer>
            </div>
        </form>
    </div>
</div>
