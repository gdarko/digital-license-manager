<?php defined('ABSPATH') || exit; ?>

<h1 class="wp-heading-inline"><?php esc_html_e('Licenses', 'digital-license-manager'); ?></h1>
<a class="page-title-action" href="<?php echo esc_url($addLicenseUrl); ?>">
    <span><?php esc_html_e('Add new', 'digital-license-manager');?></span>
</a>
<a class="page-title-action" href="<?php echo esc_url($importLicenseUrl); ?>">
    <span><?php esc_html_e('Import', 'digital-license-manager');?></span>
</a>
<hr class="wp-header-end">

<form method="post" id="dlm-license-table">
    <?php
        $licenses->prepare_items();
        $licenses->views();
        $licenses->search_box(__( 'Search licenses', 'digital-license-manager' ), 'license_key');
        $licenses->display();
    ?>
</form>
