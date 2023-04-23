<?php
defined( 'ABSPATH' ) || exit;
/* @var \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool $tool */
/* @var \IdeoLogix\DigitalLicenseManager\Abstracts\AbstractToolMigrator[] $plugins */
?>

<h3><?php _e( 'Past Orders License Generator', 'digital-license-manager' ); ?></h3>
<p><?php _e( 'This tool generates licenses for all past orders that doesn\'t have license assigned. Useful if you already have established shop and want to assign licenses to your existing orders.', 'digital-license-manager' ); ?></p>
<form class="dlm-tool-form" method="POST" action="">
    <div class="dlm-tool-form-row">
        <label for="generator"><?php _e( 'Generator', 'digital-license-manager' ); ?> <span class="required">*</span></label>
        <select id="generator" name="generator" required>
        </select>
    </div>
    <div class="dlm-tool-form-row">
        <label>
            <input type="checkbox" name="use_product_licensing_configuration" value="1">
            <small><?php _e( 'Use product settings where possible, e.g some products have their own licensing configuration settings.', 'digital-license-manager' ); ?></small>
        </label>
    </div>
    <div class="dlm-tool-form-row dlm-tool-form-row-progress" style="display: none;">
        <div class="dlm-tool-progress-bar">
            <p class="dlm-tool-progress-bar-inner">&nbsp;</p>
        </div>
        <div class="dlm-tool-progress-info"><?php _e( 'Initializing...', 'digital-license-manager' ); ?></div>
    </div>
    <div class="dlm-tool-form-row">
        <input type="hidden" name="identifier" value="past_orders"/>
        <input type="hidden" name="tool" value="<?php echo $tool->getId(); ?>">
        <button type="submit" class="button button-small button-primary"><?php _e( 'Process', 'digital-license-manager' ); ?></button>
    </div>
</form>

<script type="application/javascript">
    document.addEventListener("DOMContentLoaded", function (event) {
        var selectGenerator = document.getElementById('generator');
        if (selectGenerator) {
            new window.DLM.Select(selectGenerator, {
                remote: {
                    url: ajaxurl,
                    action: 'dlm_dropdown_search',
                    type: 'generator',
                    nonce: '<?php echo wp_create_nonce( 'dlm_dropdown_search' ); ?>',
                },
                placeholder: '<?php echo __( 'Search by generator', 'digital-license-manager' ); ?>',
            });
        }
    });
</script>