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

/* @var \IdeoLogix\DigitalLicenseManager\Database\Models\License[] $records */


use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;

$show_actions = apply_filters( 'dlm_block_license_table_actions', false, $records );

?>

<table>
    <thead>
    <tr>
        <th><?php esc_html_e( 'License', 'digital-license-manager' ); ?></th>
		<?php do_action( 'dlm_block_license_table_after_license_column_head' ); ?>
        <th><?php esc_html_e( 'Status', 'digital-license-manager' ); ?></th>
        <th><?php esc_html_e( 'Expires At', 'digital-license-manager' ); ?></th>
		<?php if ( $show_actions ): ?>
            <th><?php esc_html_e( 'Actions', 'digital-license-manager' ); ?></th>
		<?php endif; ?>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( $records as $record ): ?>
        <tr>
            <td>
				<?php
				try {
					echo apply_filters( 'dlm_license_key_markup', $record->getDecryptedLicenseKey(), $record );
				} catch ( \Exception $e ) {
					echo esc_html_e( 'Unknown', 'digital-license-manager' );
				}
				?>
            </td>
			<?php do_action( 'dlm_block_license_table_after_license_column_body', $records ); ?>
            <td>
				<?php
				$status = apply_filters( 'dlm_block_license_table_status', null, $records );
				if ( null === $status ) {
					$status = \IdeoLogix\DigitalLicenseManager\Enums\LicensePrivateStatus::statusToHtml( $record->getStatus(), ['style' => 'inline'] );
				}
				echo $status;
				?>
            </td>
            <td>
				<?php echo DateFormatter::toHtml( $record->getExpiresAt(), [ 'expires' => true ] ); ?>
            </td>
			<?php if ( $show_actions ): ?>
                <td><?php do_action( 'dlm_block_license_table_actions_content', $record ); ?></td>
			<?php endif; ?>
        </tr>
	<?php endforeach; ?>
    </tbody>
</table>