<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-present  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
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

/* @var bool $emailRequired */

$licenseKey = apply_filters( 'dlm_block_licenses_table_key', null );
?>


<div class="dlm-block-licenses-check">

    <div class="dlm-block-licenses-check-results"></div>

    <form id="dlm-licenses-check">
		<?php if ( isset( $emailRequired ) && $emailRequired && ! is_user_logged_in() ): ?>
            <div class="dlm-form-row">
                <label for="email"><?php esc_html_e( 'Owner Email', 'digital-license-manager' ); ?></label>
                <input type="text"
                       id="email"
                       name="email"
                       class="dlm-form-control"/>
            </div>
            <input type="hidden" name="echeck" id="echeck" value="1"/>
		<?php endif; ?>
        <div class="dlm-form-row">
            <label for="licenseKey"><?php esc_html_e( 'License Key', 'digital-license-manager' ); ?></label>
            <input type="text"
                   id="licenseKey"
                   name="licenseKey"
                   value="<?php echo esc_attr( $licenseKey ); ?>"
                   class="dlm-form-control"/>
        </div>
        <button type="submit"><?php esc_html_e( 'Submit', 'digital-license-manager' ); ?></button>
    </form>
</div>