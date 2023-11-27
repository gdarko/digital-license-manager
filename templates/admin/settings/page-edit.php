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
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Setup;

defined( 'ABSPATH' ) || exit;

$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );

/** @var ApiKey $keyData */

?>

<h2><?php esc_html_e( 'Key details', 'digital-license-manager' ); ?></h2>
<hr class="wp-header-end">

<div class="dlm-settings-edit">
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="id" value="<?php esc_html_e( $keyId ); ?>">
		<?php wp_nonce_field( 'dlm-api-key-update' ); ?>
        <input type="hidden" name="action" value="<?php echo 'dlm_api_key_update'; ?>">
        <input type="hidden" name="dlm_action" value="<?php esc_attr_e( $action ); ?>">

        <table class="form-table">
            <tbody>
            <tr scope="row">
                <th scope="row">
                    <label for="description"><?php esc_html_e( 'Description', 'digital-license-manager' ); ?></label>
                    <span class="text-danger">*</span>
                </th>
                <td>
                    <input id="description" class="regular-text" name="description" type="text"
                           value="<?php echo esc_attr( $keyData->getDescription() ); ?>">
                    <p class="description">
                        <strong><?php esc_html_e( 'Required.', 'digital-license-manager' ); ?></strong>
                        <span><?php esc_html_e( 'Friendly name for identifying this key.', 'digital-license-manager' ); ?></span>
                    </p>
                </td>
            </tr>
            <tr scope="row">
                <th scope="row">
                    <label for="user"><?php esc_html_e( 'User', 'digital-license-manager' ); ?></label>
                    <span class="text-danger">*</span>
                </th>
                <td>
                    <select id="user" class="regular-text" name="user">
						<?php
						foreach ( $users as $user ):
							$selected = ( $userId == $user->ID ) ? 'selected="selected"' : '';

							echo sprintf(
								'<option value="%s" %s>%s (#%d - %s)</option>',
								$user->ID,
								$selected,
								$user->user_login,
								$user->ID,
								$user->user_email
							);
						endforeach;
						?>
                    </select>
                    <p class="description">
                        <strong><?php esc_html_e( 'Required.', 'digital-license-manager' ); ?></strong>
                        <span><?php esc_html_e( 'Owner of these keys.', 'digital-license-manager' ); ?></span>
                    </p>
                </td>
            </tr>
            <tr scope="row">
                <th scope="row">
                    <label for="permissions"><?php esc_html_e( 'Permissions', 'digital-license-manager' ); ?></label>
                    <span class="text-danger">*</span>
                </th>
                <td>
                    <select id="permissions" class="regular-text" name="permissions">
						<?php foreach ( $permissions as $permissionId => $permissionName ) : ?>
                            <option value="<?php echo esc_attr( $permissionId ); ?>"
								<?php selected( $keyData->getPermissions(), $permissionId, true ); ?>
                            >
                                <span><?php echo esc_html( $permissionName ); ?></span>
                            </option>
						<?php endforeach; ?>
                    </select>
                    <p class="description">
                        <strong><?php esc_html_e( 'Required.', 'digital-license-manager' ); ?></strong>
                        <span><?php esc_html_e( 'Select the access type of these keys.', 'digital-license-manager' ); ?></span>
                    </p>
                </td>
            </tr>
            <tr scope="row">
                <th scope="row">
                    <label for="permissions"><?php esc_html_e( 'Endpoints', 'digital-license-manager' ); ?></label>
                    <span class="text-danger">*</span>
                </th>
                <td>
					<?php

					$classList = array(
						'GET'    => 'text-success',
						'PUT'    => 'text-info',
						'POST'   => 'text-primary',
						'DELETE' => 'text-danger',
					);

					$field = 'endpoints';
					$html  = '<fieldset>';

					$value = $keyData ? $keyData->getEndpoints() : array();

					foreach ( \IdeoLogix\DigitalLicenseManager\RestAPI\Setup::getEndpoints() as $route ) {
						$checked = false;

						if ( is_array( $value ) && array_key_exists( $route['id'], $value ) && (int) $value[ $route['id'] ] === 1 ) {
							$checked = true;
						}

						$html .= sprintf( '<label for="%s-%s">', $field, $route['id'] );
						$html .= sprintf(
							'<input id="%s-%s" type="checkbox" name="%s[%s]" value="1" %s>',
							$field,
							$route['id'],
							$field,
							$route['id'],
							checked( true, $checked, false )
						);
						$html .= sprintf( '<code><b class="%s">%s</b> - %s</code>', $classList[ $route['method'] ], $route['method'], $route['name'] );
						if ( isset( $route['deprecated'] ) && true === $route['deprecated'] ) {
							$html .= sprintf(
								'<code class="text-info"><strong>%s</strong></code>',
								strtoupper( __( 'Deprecated', 'digital-license-manager' ) )
							);
						}
						$html .= '</label>';
						$html .= '<br>';
					}

					$html .= sprintf(
						'<p class="description" style="margin-top: 1em;"><strong>%s</strong> %s</p>',
						__( 'Required.', 'digital-license-manager' ),
						__( 'Select the endpoints that this key has access to.<br/> The complete <strong>API documentation</strong> can be found <a href="https://docs.codeverve.com/digital-license-manager/rest-api/authentication/" target="_blank" rel="noopener">here</a>.', 'digital-license-manager' )
					);
					$html .= '</fieldset>';
					echo $html;
					?>

                </td>
            </tr>
			<?php if ( $action === 'edit' ): ?>
                <tr scope="row">
                    <th scope="row">
                        <label><?php esc_html_e( 'Consumer key ending in', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
                        <code>&hellip;<?php echo esc_html( $keyData->getTruncatedKey() ); ?></code>
                    </td>
                </tr>
                <tr scope="row">
                    <th scope="row">
                        <label><?php esc_html_e( 'Last access', 'digital-license-manager' ); ?></label>
                    </th>
                    <td>
						<?php
						if ( ! empty( $keyData->getLastAccess() ) ) {
							echo sprintf(
								esc_html__( '%1$s at %2$s', 'digital-license-manager' ),
								date_i18n( $date_format, strtotime( $keyData->getLastAccess() ) ),
								date_i18n( $time_format, strtotime( $keyData->getLastAccess() ) )
							);
						} else {
							esc_html_e( 'Unknown', 'digital-license-manager' );
						}
						?>
                    </td>
                </tr>
			<?php endif; ?>
            </tbody>
        </table>

		<?php if ( 0 === (int) $keyId ): ?><?php submit_button( __( 'Generate API key', 'digital-license-manager' ), 'primary', 'update_api_key' ); ?><?php else: ?>
            <p class="submit">
				<?php submit_button( __( 'Save changes', 'digital-license-manager' ), 'primary', 'update_api_key', false ); ?>
                <a class="dlm-confirm-dialog" style="color: #a00; text-decoration: none; margin-left: 10px;"
                   href="<?php echo esc_url(
					   wp_nonce_url(
						   add_query_arg(
							   array(
								   'action' => 'delete',
								   'key'    => $keyId
							   ),
							   sprintf(
								   admin_url( 'admin.php?page=%s&tab=rest_api' ),
								   PageSlug::SETTINGS
							   )
						   ),
						   'delete'
					   )
				   ); ?>">
                    <span><?php esc_html_e( 'Delete', 'digital-license-manager' ); ?></span>
                </a>
            </p>
		<?php endif; ?>
    </form>
</div>
