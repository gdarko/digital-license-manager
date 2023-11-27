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

defined( 'ABSPATH' ) || exit;
/* @var \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool $tool */
/* @var \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractToolMigrator[] $plugins */
?>

<h3><?php _e( 'Database Migration', 'digital-license-manager' ); ?></h3>
<p><?php _e( 'This is one-click migration tool that makes it possible to migrate from other plugins easily. Please take database backups before starting this operation.', 'digital-license-manager' ); ?></p>
<form class="dlm-tool-form dlm-tool-form--database-migration" method="POST" action="">
    <div class="dlm-tool-form-row">
        <label for="identifier"><?php _e( 'Select plugin', 'digital-license-manager' ); ?></label>
        <select id="identifier" name="identifier">
            <option value="none">---</option>
			<?php foreach ( $plugins as $plugin ): ?>
                <option value="<?php echo esc_attr( $plugin->getId() ); ?>"><?php echo esc_attr( $plugin->getName() ); ?></option>
			<?php endforeach; ?>
        </select>
    </div>
    <div class="dlm-tool-form-row">
        <label>
            <input type="checkbox" name="preserve_ids" value="1">
            <small style="color:red;"><?php _e( 'Preserve old IDs. If checked, your existing Digital License Manager database will be wiped to remove/free used IDs. Use this ONLY if you are absolutely sure what you are doing and if your app depend on the existing license/generator IDs.', 'digital-license-manager' ); ?></small>
        </label>
    </div>
    <div class="dlm-tool-form-row dlm-tool-form-row-progress" style="display: none;">
        <div class="dlm-tool-progress-bar">
            <p class="dlm-tool-progress-bar-inner">&nbsp;</p>
        </div>
        <div class="dlm-tool-progress-info"><?php _e( 'Initializing...', 'digital-license-manager' ); ?></div>
    </div>
    <div class="dlm-tool-form-row">
        <input type="hidden" name="id" value="<?php echo esc_attr( $tool->getId() ); ?>">
        <input type="hidden" name="tool" value="<?php echo esc_attr( $tool->getSlug() ); ?>">
        <button type="submit" class="button button-small button-primary"><?php _e( 'Migrate', 'digital-license-manager' ); ?></button>
    </div>
    <div class="dlm-tool-form-row dlm-tool-form-status" style="display:none;"></div>
</form>
