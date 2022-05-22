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
        <label><input type="checkbox" name="preserve_ids" value="1"> <?php _e( 'Preserve old IDs. Important: By using this option your existing Digital License Manager database will be wiped to free it up for the original IDs. Check this box if you agree.', 'digital-license-manager' ); ?></label>
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


<style>

    .dlm-tool-form-row {
        width: 100%;
        margin-bottom: 10px;
    }

    .dlm-tool-form-row label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .dlm-tool-progress-info, .dlm-tool-progress-bar, .dlm-tool-form-row #identifier {
        max-width: 360px;
        width: 100%;
    }

    .dlm-tool-progress-bar {
        position: relative;
        height: 20px;
        border: 1px solid #cccccc;
    }

    .dlm-tool-progress-bar-inner {
        position: absolute;
        left: 0;
        top: 0;
        background-color: #73d95b;
        height: 100%;
        font-size: 0px;
        transition: width 600ms linear;
    }

    .dlm-tool-progress-info {
        text-align: center;
        font-size: 12px;
    }
</style>
