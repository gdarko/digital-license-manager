<?php
defined( 'ABSPATH' ) || exit;
/* @var \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool $tool */
/* @var \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractToolMigrator[] $plugins */
?>

<h3><?php _e( 'Database Migration', 'digital-license-manager' ); ?></h3>
<p><?php _e( 'This is one-click migration tool that makes it possible to migrate from other plugins easily. Please take database backups before starting this operation.', 'digital-license-manager' ); ?></p>
<form class="dlm-tool-form">
    <div class="dlm-tool-form-row">
        <label for="identifier"><?php _e( 'Select plugin', 'digital-license-manager' ); ?></label>
        <select id="identifier" name="identifier">
			<?php foreach ( $plugins as $plugin ): ?>
                <option value="<?php echo $plugin->getId(); ?>"><?php echo $plugin->getName(); ?></option>
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
        <input type="hidden" name="tool" value="<?php echo $tool->getId(); ?>">
        <button type="submit" class="button button-small button-primary"><?php _e( 'Migrate', 'digital-license-manager' ); ?></button>
    </div>
</form>
