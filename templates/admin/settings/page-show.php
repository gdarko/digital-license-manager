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

use IdeoLogix\DigitalLicenseManager\Database\Models\ApiKey;

defined( 'ABSPATH' ) || exit;

/** @var ApiKey $keyData */
/** @var string $consumerKey */

?>

<h2><?php esc_html_e( 'Key details', 'digital-license-manager' ); ?></h2>
<hr class="wp-header-end">

<div class="dlm-settings-edit">
	<?php if ( $keyData ): ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'dlm-api-key-update' ); ?>
            <table class="form-table">
                <tbody>
                <tr scope="row">
                    <th scope="row">
                        <label for="consumer_key"><?php esc_html_e( 'Consumer key', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <input id="consumer_key" class="regular-text" name="consumer_key" type="text"
                               value="<?php echo esc_attr( $consumerKey ); ?>" readonly="readonly">
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label for="consumer_secret"><?php esc_html_e( 'Consumer secret', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <input id="consumer_secret" class="regular-text" name="consumer_secret" type="text"
                               value="<?php echo esc_attr( $keyData->getConsumerSecret() ); ?>" readonly="readonly">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
	<?php else: ?>
        <div><?php esc_html_e( 'Nothing to see here...', 'digital-license-manager' ); ?></div>
	<?php endif; ?>
</div>
